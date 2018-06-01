<?php

namespace CSIROCMS\Command;

use Monolog\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class PentahoJobCommand extends Command
{
	const ARG_FILE = 'file';
	const OP_PARAMS = 'params';
	const OP_TIMEOUT = 'timeout';
	/**
	 * @var Logger
	 */
	private $logger;
	/**
	 * @var string
	 */
	private $basePath;

	public function __construct($basePath, Logger $logger)
	{

		parent::__construct();
		$this->logger = $logger;
		$this->basePath = $basePath;
	}

	protected function configure()
	{
		$this
			->setName('pentaho:job')
			->setDescription('Execute a pentaho job.')
			->addArgument(
				self::ARG_FILE,
				InputArgument::REQUIRED,
				'Path to file to execute. Pentaho job files typically end in *.kjb')
			->addOption(self::OP_PARAMS, 'p', InputOption::VALUE_REQUIRED, 'List of parameters to pass on to the job. Parameters should be passed as JSON.')
			->addOption(self::OP_TIMEOUT, 't', InputOption::VALUE_REQUIRED, 'Specify the timeout for the pentaho script.');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 * @throws \Exception
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$fileName = $input->getArgument(self::ARG_FILE);
		$fileName = getcwd() . DIRECTORY_SEPARATOR . $fileName;
		if (!file_exists($fileName)){
			throw new \Exception(sprintf('Job file `%s` not found.', $fileName));
		}
		$this->logger->debug(sprintf('Executing job: %s', $fileName));
		$builder = new ProcessBuilder([
			$this->basePath . '/vendor/pentaho/data-integration/kitchen.sh',
			'-file',
			$fileName
		]);
		/** Increase memory allocated to pentaho. Default is 2048m. */
		$builder->setEnv('PENTAHO_DI_JAVA_OPTIONS', '-Xms2048m -Xmx4096m -XX:MaxPermSize=256m');
		$paramsString = $input->getOption(self::OP_PARAMS);
		if ($paramsString) {
			$params = json_decode($paramsString);
			if (JSON_ERROR_NONE !== json_last_error()) {
				throw new Exception(sprintf('Invalid JSON in parameter string %s', $paramsString));
			}
			foreach ($params as $name => $param){
				$argument = sprintf("-param:%s=%s",$name, $param);
				$builder->add($argument);
			}
		}
		$verbosityLevels = [
			$output::VERBOSITY_DEBUG => 'Debug',
			$output::VERBOSITY_VERY_VERBOSE => 'Detailed',
			$output::VERBOSITY_VERBOSE => 'Basic',
			$output::VERBOSITY_NORMAL => 'Minimal',
			$output::VERBOSITY_QUIET => 'Nothing',
		];
		$builder->add(sprintf('-level=%s', $verbosityLevels[$output->getVerbosity()]));
		$process = $builder->getProcess();
		$timeOut = $input->getOption('timeout');
		if ($timeOut !== null){
			$process->setTimeout($timeOut);
		}

		$process->start();

		$this->logger->addDebug(sprintf('Running process "%s"', $process->getCommandLine()));

		foreach ($process as $type => $data) {
			if ($process::OUT === $type) {
				$output->writeln($data);
			} else { // $process::ERR === $type
				$this->logger->addError($data);
			}
		}

		// executes after the command finishes
		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}
		$this->logger->info(sprintf('Executed `%s`.', $fileName));
	}
}
