<?php

namespace CSIROCMS\Command;

use CSIROCMS\Application\Db;
use CSIROCMS\Config\Factory\ConfigFactory;
use Monolog\Logger;
use PDO;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class ValidateMigrationCommand extends Command
{
	const IMPORT_RUN_COMMAND = 'import:run';
	const MAPPING_IMPORT_COMMAND = 'mapping:import';
	const ARG_COLLECTION_NAME = 'collection_name';
	const ARG_MAPPING_NAME = 'mapping_name';
	const OPT_SAMPLE_VALIDATE = 'sample-validate';

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
		$this->setName('validate:migration')
			->setDescription('Validate migrated data against the interim data.')
			->addArgument(self::ARG_COLLECTION_NAME, InputArgument::OPTIONAL, 'The name of the collection you are validating.')
			->addArgument(self::ARG_MAPPING_NAME, InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The name of the mapping you are validating.')
			->addOption(self::OPT_SAMPLE_VALIDATE, 'S', InputOption::VALUE_NONE, 'Data dump sample records to file.');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		if ($input->getOption(self::OPT_SAMPLE_VALIDATE)) {
			$this->logger->info("** Running validations against sample dataset **");
		}

		$providenceDbConfig = $this->getDbConfig('collectiveaccess');
		$providencePdo = $this->getPdo($providenceDbConfig);

		$collectionNames = [];
		//Used only when a specifc collection is
		$mappingArgs = [];
		$importMappingConfig = $this->configFactory->getConfig('importMappingSettings');
		if (!$input->getArgument(self::ARG_COLLECTION_NAME)) {
			$collectionNames = array_keys($importMappingConfig['parameters']);
		} else if (!array_key_exists($input->getArgument(self::ARG_COLLECTION_NAME), $importMappingConfig['parameters'])) {
			throw new Exception("Invalid collection name");
		} else {
			array_push($collectionNames, $input->getArgument(self::ARG_COLLECTION_NAME));
			if($input->getArguments(self::ARG_MAPPING_NAME)) {
				$mappingArgs = array_flip($input->getArguments(self::ARG_MAPPING_NAME)['mapping_name']);
			}
		}

		$rows = Array();

		foreach ($collectionNames as $collectionName) {
			$importMappingConfig = $this->configFactory->getConfig('importMappingSettings');
			$importMappings = $importMappingConfig['parameters'][$collectionName]['mappings'];
			$mappings = [];
			// If there's a single collection specified, filter the importMappings array by the mapping arguments
			if($mappingArgs) {
				$importMappings = array_filter($importMappings, function($importMapping) use ($mappingArgs) {
					return array_key_exists($importMapping['name'], $mappingArgs);
				});
			}
			//foreach mapping file
			foreach ($importMappings as $mappingIndex => $internalMapping) {
				$mappings[$internalMapping['name']]['tableName'] = $internalMapping['table_name'];
				$dbConfig = $this->getDbConfig($importMappingConfig['parameters'][$collectionName]['connection']);
				$pdo = $this->getPdo($dbConfig);

				if ($input->getOption(self::OPT_SAMPLE_VALIDATE)) {
					$tableName = $this->sampleTableName($collectionName, $internalMapping['name']);
				} else {
					$tableName = $internalMapping['table_name'];
				}

				$this->logger->info("Checking " . $collectionName . " " . $internalMapping['name'] . "...");
				$statement = $pdo->query("SELECT COUNT(*) FROM " . $tableName . ";");
				$interimRowCount = $statement->fetch()[0];

				$query = $this->getTargetValidationQuery($collectionName, $internalMapping['name']);

				if ($query) {
					$statement = $providencePdo->query($query);
					$targetRowCount = number_format($statement->fetch()[0]);
					$difference = number_format($interimRowCount - $targetRowCount);
				} else {
					$targetRowCount = "Unknown";
					$difference = "Unknown";
				}

				array_push($rows, [$collectionName, $internalMapping['name'], number_format($interimRowCount), $targetRowCount, $difference]);

			}

		}
		
		$table = new Table($output);
		$decorator = $table->getStyle();
		$decorator->setCrossingChar('|');
		$header = ['Collection', 'Mapping', 'Interim Count', 'Migrated Count', 'Difference'];
		$table->setHeaders($header);
		$table->addRows($rows)->render();
		$this->logger->debug('Job Durations: ', array_map(function($row) use ($header) {
			return array_combine($header, $row);
		}, $rows));		

	}

	private function getPdo($dbConfig) {
		$db = new Db($dbConfig['driver'], $dbConfig['database'], $dbConfig['host'], $dbConfig['username'], $dbConfig['password']);
		$pdo = $db->getPDO();
		return $pdo;
	}
	private function getDbConfig($connectionName) {
		$connections = $this->configFactory->getConfig()['connections'];
		if (!isset($connections[$connectionName])){
			throw new \Exception(sprintf('Connection `%s` does not exist. Valid connections are: %s.', $connectionName, join(',', array_keys($connections))));
		}
		return $connections[$connectionName];
	}

	/**
	 * @param $collectionName
	 * @param $mappingName
	 * @return string
	 */
	private function sampleTableName($collectionName, $mappingName)
	{
		return '_test_' . $collectionName . '_' . $mappingName;
	}


	/**
	 * @param $collectionName
	 * @param $mappingName
	 * @return string
	 */
	private function getTargetValidationQuery($collectionName, $mappingName)
	{
		$cwd = getcwd();
		$validationSqlFile = "$cwd/steps/sql/mysql/validate/$collectionName$mappingName.sql";

		if (file_exists($validationSqlFile)) {
			$handle = fopen($validationSqlFile, 'r');
			$sql = fread($handle,filesize($validationSqlFile));
			fclose($handle);
			return $sql;
		} else {
			$this->logger->warning("Validation file not found for $collectionName $mappingName. Should be $validationSqlFile");
			return null;
		}
	}

}
