<?php

namespace CSIROCMS\Command;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class RunDependentTasksCommand extends Command
{
	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var Stopwatch
	 */
	private $stopwatch;

	private $commands;
	private $disambiguate;

	public function __construct($name, $description, array $commands, Logger $logger, Stopwatch $stopwatch)
	{
		$this->logger = $logger;
		$this->stopwatch = $stopwatch;
		$this->setName($name);
		$this->setDescription($description);
		foreach ($commands as $i => $commandConfig){
			if(!is_array($commandConfig)){
				$commandConfig = ['name' => $commandConfig];
			} else {
				$commandName = array_keys($commandConfig)[0];
				$commandConfig = $commandConfig[$commandName];
				$commandConfig['name'] = $commandName;
			}
			foreach (['arguments', 'options'] as $setting){
				if(!isset($commandConfig[$setting])){
					$commandConfig[$setting] = [];
				}
				if(!is_array($commandConfig[$setting])){
					$commandConfig[$setting] = [$commandConfig[$setting]];
				}
			}
			$commandNames[] = $commandName = $commandConfig['name'];
			$commands[$i] = $commandConfig;
		}
		$this->commands = $commands;
		$description = sprintf('%s. Commands are: %s.', $this->getDescription(), join(', ', $commandNames));
		$this->setDescription($description);
		parent::__construct();
	}

	/**
	 *
	 */
	public function configure()
	{
		foreach ($this->commands as $i => $commandConfig){
			foreach (['arguments', 'options'] as $inputType){
				foreach ($commandConfig[$inputType] as $name => $default){
					$this->uniqueOption($i, $commandConfig['name'], $inputType, $name, $default);
				}
			}
		}
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$this->stopwatch->start('execute');
		$progress = new ProgressBar($output, count($this->commands));
		$progress->start();
		$commands = [];
		foreach ($this->commands as $i => $commandConfig){
			$commandName = $commandConfig['name'];
			$commands[] = $commandName;
			/** @var Command $command */
			$command = $this->getApplication()->find($commandName);
			$arguments = array(
				'command' => $commandName
			);
			$lookups = isset($this->disambiguate[$i]) ? $this->disambiguate[$i] : [];
			foreach ($input->getOptions() as $name => $value){
				if (isset($lookups[$name])){
					$conf = $lookups[$name];
					if ($commandName === $conf['command']){
						if ($conf['type'] === 'arguments'){
							$arguments[$conf['original']] = $value;
						} else { // $conf['type'] === 'options'
							$arguments['--' . $conf['original']] = $value;
						}
					}
				}
			}
			$runCommandInput = new ArrayInput($arguments);
			$this->logger->debug(sprintf('Running command :%s', $commandName));
			$command->run($runCommandInput, $output);
			$progress->advance();
			$this->stopwatch->lap('execute');
		}
		$event = $this->stopwatch->stop('execute');
		$rows = [[$this->getName(), $event->getDuration(),  $event->getStartTime(), $event->getEndTime(), $event->getMemory()/1024]];
		foreach ($event->getPeriods() as $i => $period){
			if (isset($commands[$i])){
				$rows[] = ['  ' . $commands[$i], $period->getDuration(), $period->getStartTime(), $period->getEndTime(), $period->getMemory()/1024];
			}
		}
		$table = new Table($output);
		$decorator = $table->getStyle();
		$decorator->setCrossingChar('|');
		$header = ['Command', 'Duration (ms)', 'Start (ms)', 'End (ms)', 'Memory (KB)'];
		$table->setHeaders($header);
		$progress->finish();
		$progress->clear();
		$table->addRows($rows)->render();
		$this->logger->debug('Job Durations: ', array_map(function($row) use ($header) {
			return array_combine($header, $row);
		}, $rows));
	}

	private function uniqueOption($i, $command, $type, $name, $default, $original = null)
	{
		$matches = [];
		if (preg_match('/(\D+)(\d*)/', $name, $matches) && !$original){
			$original = $matches[1];
		}
		if ($this->getDefinition()->hasOption($matches[0])){
			$this->uniqueOption($i, $command, $type, $matches[1] . (((int)$matches[2]) + 1), $default, $original);
		} else {
			$this->disambiguate[$i][$name] = ['type' => $type, 'original' => $original ?: $name, 'command' => $command];
			$this->addOption($name, null, InputOption::VALUE_OPTIONAL, null, $default);
		}
	}
}
