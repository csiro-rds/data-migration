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

class ValidateSampleCommand extends Command
{
	const IMPORT_RUN_COMMAND = 'import:run';
	const MAPPING_IMPORT_COMMAND = 'mapping:import';
	const ARG_COLLECTION_NAME = 'collection_name';
	const ARG_MAPPING_NAME = 'mapping_name';
	const OPT_NO_IMPORT = 'no-import';
	const OPT_NO_REGENERATE = 'no-regenerate';
	const OPT_DATA_DUMP = 'data-dump';
	const OPT_VALIDATIONS = 'validations';
	const OPT_FIELDS = 'fields';

	/**
	 * @var ConfigFactory
	 */
	private $configFactory;
	/**
	 * @var Logger
	 */
	private $logger;

	private $validations = ['values', 'searchRules', 'searchValues', 'searchClauses'];

	public function __construct(ConfigFactory $configFactory, Logger $logger)
	{
		$this->configFactory = $configFactory;
		$this->logger = $logger;
		parent::__construct();
	}

	public function configure()
	{
		$this->setName('validate:sample')
			->setDescription('Validate sample data against import mapping.')
			->addArgument(self::ARG_COLLECTION_NAME, InputArgument::OPTIONAL, 'The name of the collection you are validating.')
			->addArgument(self::ARG_MAPPING_NAME, InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The name of the mapping you are validating.')
			->addOption(self::OPT_NO_IMPORT, 'I', InputOption::VALUE_NONE, 'Don\'t run the import only produce the sample data.')
			->addOption(self::OPT_NO_REGENERATE, 'G', InputOption::VALUE_NONE, 'Don\'t regenerate sample data table.')
			->addOption(self::OPT_DATA_DUMP, 'd', InputOption::VALUE_NONE, 'Data dump sample records to file.')
			->addOption(self::OPT_VALIDATIONS, null , InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, sprintf('Limit number of validations. Possible values are: %s', implode($this->validations, ', ')))
			->addOption(self::OPT_FIELDS, null , InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Limit sample data to source fields.');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
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
		$validations = $input->getOption(self::OPT_VALIDATIONS);
		if ($validations){
			array_map(function ($validation){
				if (!in_array($validation, $this->validations)){
					throw new InvalidOptionException(sprintf('Value %s for option %s is invalid. Possible values are: %s.', $validation, self::OPT_VALIDATIONS, join($this->validations,', ')));
				}
			}, $validations);
		}
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
				// Load table schema
				$mappings[$internalMapping['name']]['fields'] = $this->loadTableSchema($pdo, $internalMapping['table_name']);
				// Add sample config settings
				$mappings[$internalMapping['name']]['fields'] = $this->getMappingSampleConfigs($collectionName,
					$mappings[$internalMapping['name']]['fields'], $internalMapping['name'], $output);

				//get values/queries from db
				$sampleFields = $mappings[$internalMapping['name']]['fields'];
				$filterFields = $input->getOption(self::OPT_FIELDS);
				if ($filterFields){
					$filterFields = array_flip($filterFields);
                    $diff = array_diff_key($filterFields, $sampleFields);
                    if ($diff) {
                        $possibleFields = array_keys($sampleFields);
                        throw new InvalidOptionException(sprintf('Field name(s) %s for option %s is invalid. Possible values are: %s.', join(', ', $diff), self::OPT_VALIDATIONS, join($possibleFields,', ')));
                    }
                    $sampleFields = array_intersect_key($sampleFields, $filterFields);
				}
				$sampleTableName = $this->sampleTableName($collectionName, $internalMapping['name']);
				if (sizeof($mappings[$internalMapping['name']]) == 0) {
					$output->writeln(sprintf("\n\t<fg=yellow;options=bold>There are no fields in the %s %s json file.\n</>", $collectionName, $internalMapping['name']));
				} elseif ($input->getOption(self::OPT_NO_REGENERATE)){
					$output->writeln('Skipping generation of sample data.');
				} else {
					// Get view statements
					$mappings[$internalMapping['name']]['queries'] = $this->getFieldSampleQueries($sampleFields,
						$internalMapping['table_name'], $pdo, $validations);
					$viewStatements = $this->buildSampleTable($collectionName, $mappings[$internalMapping["name"]], $internalMapping["name"], $output, $validations && in_array('values', $validations));
					foreach ($viewStatements as $viewStatement) {
						$this->logger->debug(sprintf('Executing statement: `%s`', $viewStatement ));
						$rowsAffected = $pdo->exec($viewStatement);
						$output->writeln("\tRows affected: $rowsAffected");
					}
					// Dedup the records in the sample table
					$numOriginalRows = $pdo->query("SELECT COUNT(*) FROM `$sampleTableName`")->fetchColumn();
					$pdo->exec("CREATE TEMPORARY TABLE `dedup_records` AS SELECT DISTINCT * FROM `$sampleTableName`");
					$pdo->exec("TRUNCATE `$sampleTableName`");
					$numNewRows = $pdo->exec("INSERT INTO `$sampleTableName` SELECT * FROM `dedup_records`");
					$pdo->exec("DROP TEMPORARY TABLE `dedup_records`");
					$output->writeln("\tDeduplicated $sampleTableName. Original count: <fg=white;options=bold>$numOriginalRows</>. New count: <fg=white;options=bold>$numNewRows</>.");
					if($input->getOption(self::OPT_DATA_DUMP)){
						$result = $pdo->query("SELECT * FROM $sampleTableName")->fetchAll(PDO::FETCH_ASSOC);
						$dataDirectory = dirname(dirname(__DIR__)) . "/data/$collectionName";
						$fileName = "$dataDirectory/$internalMapping[name].json";
						$output->writeln("\tDumping data to sample file: <fg=yellow;options=bold>$fileName</>.");
						if (!file_exists($dataDirectory)){
							mkdir($dataDirectory);
						}
						file_put_contents($fileName, json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
						$fileName = "$dataDirectory/$internalMapping[name].csv";
						$output->writeln("\tDumping data to sample file: <fg=yellow;options=bold>$fileName</>.");
						$handle = fopen($fileName, 'w');
						fputcsv($handle, array_keys($result[0]));
						foreach($result as $header => $row){
							fputcsv($handle, $row);
						}
						fclose($handle);
					}

				}
				$this->runMappingImport($collectionName, $internalMapping['name'], $output);
				if(!$input->getOption(self::OPT_NO_IMPORT)){
					// Run import with test values
					$this->runImporter($collectionName, $sampleTableName, $internalMapping['name'], $dbConfig, $output);
				}
			}
		}
	}

	/**
	 * @param PDO $pdo
	 * @param $tableName
	 * @return array
	 */
	private function loadTableSchema($pdo, $tableName) {
		$statement = $pdo->query("DESCRIBE $tableName;");
		$mappingFields = [];
		while($row = $statement->fetch()) {
			$mappingFields[$row['Field']]['name'] = $row['Field'];
			$mappingFields[$row['Field']]['type'] = $row['Type'];
			$mappingFields[$row['Field']]['default'] = $row['Default'];
			$mappingFields[$row['Field']]['nullable'] = $row['Null'] == 'YES' ? true : false;
		}
		return $mappingFields;
	}

	/**
	 * @param $collectionName
	 * @param $mapping
	 * @param $mappingName
	 * @param OutputInterface $output
	 * @return array|bool
	 */
	private function buildSampleTable($collectionName, $mapping, $mappingName, $output, $includeValues = true)
	{
		$sampleTableName = $this->sampleTableName($collectionName, $mappingName);
		$insertColumnsArray = [];
		$createFieldsArray = [];
		$longestField = 0;
		$createTableStatement = "DROP TABLE IF EXISTS $sampleTableName;\n" .
			"\tCREATE TABLE $sampleTableName (\n\t";
		if(sizeof($mapping['fields']) == 0) {
			$output->writeln("\n\t<fg=red;options=bold>No fields found for table $sampleTableName</>");
			return false;
		}

		// Find longest values array
		foreach($mapping['fields'] as $fieldName=>$field) {
			if(array_key_exists('values', $field) && $longestField <= sizeof($field['values'])) {
				$longestField = sizeof($field['values']);
			}
		};

		//initialise array
		$valueStatementArrays = array_fill(0, $longestField, []);

		//loop again to create inserts
		foreach($mapping['fields'] as $fieldName=>$field) {
			$nullable = $field['nullable'] ? 'NULL' : 'NOT NULL';
			array_push($createFieldsArray, "\t`$fieldName` $field[type] $nullable");
			array_push($insertColumnsArray, " `$fieldName`");
			if ($includeValues){
				for ($index = 0; $index < $longestField; $index++) {
					$newFieldValue = null;
					if (isset($field['values'][$index])) {
						$newFieldValue = strpos($field['type'], 'int') !== false ?
							$field['values'][$index] :
							"'" . $field['values'][$index] . "'";
					} else if (!$field['nullable']) {
						if (array_key_exists('values', $field) && sizeof($field['values']) > 0) {
							$newFieldValue = $field['values'][sizeof($field['values']) - 1];
							$newFieldValue = strpos($field['type'], 'int') !== false ?
								$newFieldValue :
								$newFieldValue = "'$newFieldValue'";
						} else {
							throw new Exception("Cannot have a non nullable field with no values. Please enter at least one value.");
						}
					} else if($field['nullable']) {
						$newFieldValue = 'NULL';
					}
					// Push the field if found, else null if nullable, else add the last valid field to the value statement
					array_push($valueStatementArrays[$index], $newFieldValue);
				}
			}
		};

		// Add value statements to array
		$valueStatements = array_map(function ($valueStatementArray) {
			return implode(",\n\t", $valueStatementArray);
		}, $valueStatementArrays);
		$createFields = implode(",\n\t", $createFieldsArray);
		$insertColumns = implode(",\n\t", $insertColumnsArray);
		$createTableStatementArray = [];
		// Add drop/create table statement
		array_push($createTableStatementArray, $createTableStatement .= "$createFields\n\t);");
		// Add insert statements statements
		$constructedInsertStatements = array_map(function($valueStatement) use ($sampleTableName, $insertColumns) {
			return "\n\tINSERT INTO $sampleTableName (\n\t$insertColumns\n) VALUES (\n\t$valueStatement);";
		}, $valueStatements);
		$viewStatements = array_merge($createTableStatementArray, $constructedInsertStatements);

		// Update insert queries with table names
		foreach($mapping['queries'] as $query) {
			array_push($viewStatements, "\tINSERT INTO $sampleTableName ($insertColumns) $query");
		}
		$output->writeln("<fg=white;options=bold>\nStarting Import for $mappingName</>");
		$output->writeln(sprintf("\t<fg=white;options=bold>\nSQL query:\n</><fg=blue;options=bold>\t%s</>", $createTableStatement));

		return $viewStatements;
	}

	/**
	 * @param $sampleFields
	 * @param $tableName
	 * @param PDO $pdo
	 * @param array $validations
	 * @return array
	 */
	private function getFieldSampleQueries($sampleFields, $tableName, $pdo, $validations) {
		$fields = $sampleFields;
		$queries = [];
		foreach($fields as $field) {
			$fieldName = $field['name'];
			$validations = $validations?: $this->validations;
			if (in_array('searchRules', $validations)){
				foreach ($field['searchRules'] as $searchRule) {
					// Case statement for further search patterns
					switch ($searchRule) {
						case 'firstMedianLast':
							$rows = $pdo->query("SELECT Count(*) as numRecords FROM $tableName WHERE `$fieldName` IS NOT NULL")->fetchAll();
							$count = $rows[0]['numRecords'];
							$medianFrom = floor($count/2);
							$lastFrom = $count - 5 >=0 ? $count - 5 : 0;
							array_push($queries, "SELECT DISTINCT * FROM ((SELECT * FROM $tableName WHERE `$fieldName` IS NOT NULL ORDER BY `$fieldName` LIMIT 0, 5)" .
								"UNION (SELECT * FROM $tableName WHERE `$fieldName` IS NOT NULL ORDER BY `$fieldName` LIMIT $medianFrom, 5)" .
								"UNION (SELECT * FROM $tableName WHERE `$fieldName` IS NOT NULL ORDER BY `$field[name]` LIMIT $lastFrom, 5)) AS d;");
							break;
						case 'distinctValues':
							$query = $pdo->query("SELECT `$fieldName` as fieldValue, COUNT(*) as numRecords FROM `$tableName` WHERE `$fieldName` IS NOT NULL GROUP BY `$fieldName` ORDER BY 2 DESC, 1 ASC ");
							$subqueries = [];
							$fieldType = $query->getColumnMeta(0)['pdo_type'];

							foreach ($query->fetchAll() AS $row){
								$subqueries[] = "SELECT * FROM $tableName WHERE `$fieldName` = " . $pdo->quote($row['fieldValue'], $fieldType) . " ORDER BY 1 LIMIT 5";
							}
							if ($subqueries){
								$subqueries = sprintf('(%s)',implode(")\nUNION\n(", $subqueries));
								$queries[] = "SELECT DISTINCT * FROM ($subqueries) AS d";
							}
							break;
						default:
							throw new Exception("Invalid command: $searchRule");
					}
				}
			}
			if (in_array('searchValues', $validations) && $field['searchValues']) {
				$searchValues = implode(", ", $field['searchValues']);
				array_push($queries, "SELECT * FROM $tableName WHERE $fieldName IN ($searchValues);");
			}
			if (in_array('searchClauses', $validations) && $field['searchClauses']) {
				foreach($field['searchClauses'] as $searchClause) {
					array_push($queries, "SELECT * FROM $tableName WHERE $searchClause");
				}
			}
		}
		return $queries;
	}

	/**
	 * @param $collectionName
	 * @param $schemaFields
	 * @param $mappingName
	 * @param OutputInterface $output
	 * @return mixed
	 */
	private function getMappingSampleConfigs($collectionName, $schemaFields, $mappingName, $output)
	{
		$cwd = getcwd();
		$samplesConfigDir = "$cwd/steps/mappings/$collectionName/samples";
		$samplesConfig = "$samplesConfigDir/$mappingName.json";
		$fileHandle = null;
		if(!file_exists($samplesConfigDir)) {
			mkdir($samplesConfigDir);
		}
		if (!file_exists($samplesConfig) || filesize($samplesConfig) === 0) {
			$fileHandle = fopen($samplesConfig, "w+");
			array_walk($schemaFields, function ($schemaField) {
				$schemaField['searchRules'] = [];
				$schemaField['values'] = [];
			});
			fwrite($fileHandle, json_encode($schemaFields, JSON_PRETTY_PRINT));
		} else {
			$fileHandle = fopen($samplesConfig, "r");
			$json = fread($fileHandle, filesize($samplesConfig));
			fclose($fileHandle);
			$mappingConfigFields = json_decode($json);
			$keys = array_keys($schemaFields);
			foreach($keys as $key) {
				if(!array_key_exists($key, $schemaFields)) {
					$output->writeln("<fg=red;options=bold>Database field `$key` does not exist.");
				}
				if(!array_key_exists($key, $mappingConfigFields)) {
					$output->writeln("<fg=red;options=bold>Sample field `$key` not found; " .
						"</><fg=blue;options=bold>A default entry will be created in the file.</>");
					$mappingConfigFields->{$key} = (object)[
						'values'=>[],
						'searchRules'=>[],
						'searchValues' => [],
						'searchClauses' => []
					];
				} else {
					if(!property_exists($mappingConfigFields->{$key}, 'values')) {
						$mappingConfigFields->{$key}->values = [];
					}
					if(!property_exists($mappingConfigFields->{$key}, 'searchRules')) {
						$mappingConfigFields->{$key}->searchRules = [];
					}
					if(!property_exists($mappingConfigFields->{$key}, 'searchValues')) {
						$mappingConfigFields->{$key}->searchValues = [];
					}
					if(!property_exists($mappingConfigFields->{$key}, 'searchClauses')) {
						$mappingConfigFields->{$key}->searchClauses = [];
					}
				}

				$schemaFields[$key] = array_merge($schemaFields[$key], (array)$mappingConfigFields->{$key});
			};
			$fileHandle = fopen($samplesConfig, "w+");
			fwrite($fileHandle, json_encode($schemaFields, JSON_PRETTY_PRINT));
		}
		fclose($fileHandle);
		return $schemaFields;
	}

	/**
	 * @param $collectionName
	 * @param $mappingName
	 * @param OutputInterface $output
	 */
	private function runMappingImport($collectionName, $mappingName, $output) {
		$arguments = array(
			'command' => self::MAPPING_IMPORT_COMMAND,
			ImportRunCommand::ARG_COLLECTION_NAME => $collectionName,
			ImportRunCommand::ARG_MAPPING_NAME => $mappingName
		);
		$output->writeln("\n<fg=white;options=bold>Running Mapping Import...</>");
		try {
			$command = $this->getApplication()->find(self::MAPPING_IMPORT_COMMAND);
			$commandInput = new ArrayInput($arguments);
			$command->run($commandInput, $output);
		} catch (Exception $e) {
			$this->logger->critical(sprintf('There was an error with the mapping import. Error:', $e->getMessage()));
		}
	}

	/**
	 * @param $collectionName
	 * @param $sourceTableName
	 * @param $mappingName
	 * @param $dbConfig
	 * @param OutputInterface $output
	 */
	private function runImporter($collectionName, $sourceTableName, $mappingName, $dbConfig, $output){
		$host = $dbConfig['host'];
		$driver = $dbConfig['driver'];
		$username = $dbConfig['username'];
		$password = $dbConfig['password'];
		$sourceDbHost = "$driver://$username:$password@$host";
		$sourceDbName = $dbConfig['database'];

		$arguments = array(
			'command' => self::IMPORT_RUN_COMMAND,
			ImportRunCommand::ARG_COLLECTION_NAME => $collectionName,
			ImportRunCommand::ARG_MAPPING_NAME => $mappingName,
			ImportRunCommand::ARG_SOURCE_DB_HOST => $sourceDbHost,
			ImportRunCommand::ARG_SOURCE_DB_NAME => $sourceDbName,
			ImportRunCommand::ARG_SOURCE_TABLE_NAME => $sourceTableName,
			ImportRunCommand::ARG_OTHER_PARAMS => '-d DEBUG -l logs/ --no-search-indexing',
			'--' . ImportRunCommand::OPT_IGNORE_ERRORS => true
		);
		$output->writeln("\n<fg=white;options=bold>Running Importer...</>");
		try {
			$command = $this->getApplication()->find(self::IMPORT_RUN_COMMAND);
			$commandInput = new ArrayInput($arguments);
			$command->run($commandInput, $output);
		} catch (Exception $e) {
			$this->logger->critical(sprintf('There was an error with the data import. Error:', $e->getMessage()));
		}
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
}
