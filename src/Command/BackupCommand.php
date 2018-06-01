<?php

namespace CSIROCMS\Command;

use CSIROCMS\Config\Factory\ConfigFactory;
use Monolog\Logger;
use Spatie\DbDumper\Databases\MySql;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCommand extends Command
{
	const ARG_CONNECTION = 'connection';
	const ARG_FILE_PREFIX = 'file-prefix';
	const OPT_INCLUDE_BUILD_ID = 'include-build-id';
	const OPT_ZIP = 'zip';
	const DATA_DIRECTORY = 'data';
	/**
	 * @var ConfigFactory
	 */
	private $configFactory;
	/**
	 * @var Logger
	 */
	private $logger;

	public function __construct(ConfigFactory $configFactory, Logger $logger)
	{
		$this->configFactory = $configFactory;
		$this->logger = $logger;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('sql:backup')
			->setDescription('Backup database to file.')
			->addArgument(
				self::ARG_CONNECTION,
				InputArgument::OPTIONAL,
				'Name of connection to backup.',
				'collectiveaccess')
			->addArgument(
				self::ARG_FILE_PREFIX,
				InputArgument::OPTIONAL,
				'File name prefix for backup file. Defaults to the specified connection name.')
			->addOption(
				self::OPT_INCLUDE_BUILD_ID,
				'b',
				InputOption::VALUE_NONE,
				'Include build id in backup filename.')
			->addOption(
				self::OPT_ZIP,
				'z',
				InputOption::VALUE_NONE,
				'Compress the archive in a zip file.');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 * @throws \Exception
	 * @throws \Spatie\DbDumper\Exceptions\CannotStartDump
	 * @throws \Spatie\DbDumper\Exceptions\DumpFailed
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$connections = $this->configFactory->getConfig()['connections'];
		$connection = $input->getArgument(self::ARG_CONNECTION);
		if (!isset($connections[$connection])) {
			throw new \Exception(sprintf('Connection `%s` does not exist. Valid connections are: %s.', $connection, join(',', array_keys($connections))));
		}
		if (!file_exists(self::DATA_DIRECTORY)) {
			mkdir(self::DATA_DIRECTORY);
		}
		$buildId = '';
		if ($input->getOption(self::OPT_INCLUDE_BUILD_ID) && file_exists('version.properties')){
			$properties = parse_ini_file('version.properties', FALSE, INI_SCANNER_RAW);
			$buildId = '.' . $properties['build.number'];
		}
		$baseFileName = sprintf('%s%s.%s.sql', $input->getArgument(self::ARG_FILE_PREFIX) ?: $connection, $buildId, date('YmdHms'));
		$filename = self::DATA_DIRECTORY . DIRECTORY_SEPARATOR . $baseFileName;
		MySql::create()
			->setDbName($connections[$connection]['database'])
			->setUserName($connections[$connection]['username'])
			->setPassword($connections[$connection]['password'])
			->setHost($connections[$connection]['host'])
			->dumpToFile($filename);
		if ($input->getOption(self::OPT_ZIP)){
			$zip = new \ZipArchive();
			$zipFileName = $filename . '.zip';
			$zip->open($zipFileName, \ZipArchive::CREATE);
			$zip->addFile($filename, $baseFileName);
			$zip->close();
			unlink($filename);
			$filename = $zipFileName;
		}


		$this->logger->info(sprintf('Backed up connection `%s` to file: `%s`.', $connection, $filename));
	}
}
