<?php

namespace CSIROCMS\Command;
use CSIROCMS\Application\CommandHelpers;
use CSIROCMS\Application\GoogleApi;
use CSIROCMS\Config\Factory\ConfigFactory;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportRunCommand extends Command
{
	const ARG_COLLECTION_NAME = 'collection name';
	const ARG_MAPPING_NAME = 'mapping name';
	const ARG_SOURCE_DB_HOST = 'source host';
	const ARG_SOURCE_DB_NAME = 'db source name';
	const ARG_SOURCE_TABLE_NAME = 'source table name';
	const ARG_OTHER_PARAMS = 'other parameters';
	const ARG_WHERE_CLAUSE = 'where clause';
	const OPT_IGNORE_ERRORS = 'ignore-errors';

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

	public function configure()
	{
		$this->setName('import:run')
			->setDescription('run the import using a mapping file, into CA.')
			->addArgument(
				self::ARG_COLLECTION_NAME,
				InputArgument::REQUIRED,
				'Name of the target mapping collection.')
			->addArgument(
				self::ARG_MAPPING_NAME,
				InputArgument::REQUIRED,
				'Name of the mapping to import.')
			->addArgument(
				self::ARG_SOURCE_DB_HOST,
				InputArgument::REQUIRED,
				'Host of the source database.')
			->addArgument(
				self::ARG_SOURCE_DB_NAME,
				InputArgument::REQUIRED,
				'Name of the source database to import from.')
			->addArgument(
				self::ARG_SOURCE_TABLE_NAME,
				InputArgument::REQUIRED,
				'Name of the table within the source database to import from.')
			->addArgument(
				self::ARG_WHERE_CLAUSE,
				InputArgument::OPTIONAL,
				'Where clause to filter the result set.')
			->addArgument(
				self::ARG_OTHER_PARAMS,
				InputArgument::OPTIONAL,
				'Passthrough for other parameters.')
			->addOption(
				self::OPT_IGNORE_ERRORS,
				'i',
				InputOption::VALUE_NONE,
				'Ignore any errors when running the import. Allows for the migration to complete.');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$caPath = getenv('COLLECTIVEACCESS_HOME');
		$collectionName = $input->getArgument(self::ARG_COLLECTION_NAME);
		$mappingName = $input->getArgument(self::ARG_MAPPING_NAME);
		$sourceDbHost = $input->getArgument(self::ARG_SOURCE_DB_HOST);
		$sourceDbName = $input->getArgument(self::ARG_SOURCE_DB_NAME);
		$sourceTableName = $input->getArgument(self::ARG_SOURCE_TABLE_NAME);
		$whereClause = $input->getArgument(self::ARG_WHERE_CLAUSE);
		$otherParams = $input->getArgument(self::ARG_OTHER_PARAMS);
		$ignoreErrors = $input->getOption(self::OPT_IGNORE_ERRORS);

		if(!$caPath){
			throw new Exception("Could not find your CollectiveAccess caUtils file! Please set the"
				. " COLLECTIVEACCESS_HOME environment variable to the location of your CollectiveAccess installation.");
		}

		try {
			$runMappingExecString = sprintf("php %s/support/bin/caUtils import-data -m %s%s -f mysql -s '%s/%s?table=%s %s' %s %s",
				$caPath, $collectionName, $mappingName, $sourceDbHost, $sourceDbName, $sourceTableName, $whereClause, $otherParams, $ignoreErrors ? '--ignore-errors': '');
			$this->logger->debug(sprintf('Running command: %s', $runMappingExecString));
			CommandHelpers::liveExecuteCommand($runMappingExecString);
		} catch(Exception $e) {
			$this->logger->critical(sprintf('Could not run caUtils command. Error:', $e->getMessage()));
		}
	}

}
