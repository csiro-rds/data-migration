<?php

namespace CSIROCMS\Command;


use Monolog\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class CaUtilsCommand extends Command
{
	protected $command;
	protected $caParameters;
	protected $commandParameters;

	/**
	 * @var Logger
	 */
	private $logger;

	public function __construct($name, $description, $command, $caParameters = array(), $commandParameters = array(), Logger $logger)
	{
		$this->logger = $logger;
		$this->command = $command;
		$this->caParameters = $caParameters ?: array();
		$this->commandParameters = $commandParameters?: array();
		$this->setName($name);
		$this->setDescription($description);
		parent::__construct();
	}

	protected function configure()
	{
		foreach (array_merge($this->caParameters, $this->commandParameters) as $parameter => $default){
			$this->addOption($parameter, null, InputOption::VALUE_REQUIRED, null, $default);
		}
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$caPath = getenv('COLLECTIVEACCESS_HOME');

		if(!$caPath){
			throw new Exception("Could not find your CollectiveAccess caUtils file! Please set the"
				. " COLLECTIVEACCESS_HOME environment variable to the location of your CollectiveAccess installation.");
		}

		$builder = new ProcessBuilder([
			$caPath . '/support/bin/caUtils',
			$this->command
		]);
		foreach($this->caParameters as $option => $default){
			$value = str_replace('COLLECTIVEACCESS_HOME', $caPath, $input->getOption($option));
			$builder->add(sprintf('--%s=%s', $option,$value));
		}

		$process = $builder->getProcess();
		foreach($this->commandParameters as $parameter => $default){
			$method = 'set' . ucfirst($parameter);
			if(method_exists($process, $method)){
				$value = $input->getOption($parameter);
				$process->$method($value);
			}
		}

		$process->start();

		$this->logger->addDebug(sprintf('Running process "%s"', $process->getCommandLine()));
		$failed = false;
		foreach ($process as $type => $data) {
			$failed |= preg_match('/error occurred/s', $data);
			if ($process::OUT === $type && !$failed) {
				$output->writeln($data);
			} else { // $process::ERR === $type
				$this->logger->addError($data);

			}
		}

		// executes after the command finishes
		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}
		if ($failed){
			throw new Exception('caUtils command threw an error.');
		}
		$this->logger->info(sprintf('Executed `%s`.', $process->getCommandLine()));
	}
}
