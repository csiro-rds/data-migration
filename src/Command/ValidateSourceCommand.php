<?php

namespace CSIROCMS\Command;

use CSIROCMS\Application\Db;
use CSIROCMS\Config\Factory\ConfigFactory;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ValidateSourceCommand extends Command
{
	const ARG_COLLECTION_NAME = 'collection_name';
	const ARG_MAPPING_NAME = 'mapping_name';
	const MAX_LINE_WIDTH = 120;
	/**
	 * @var ConfigFactory
	 */
	private $configFactory;
	/**
	 * @var Logger
	 */
	private $logger;
	/**
	 * @var Finder
	 */
	private $finder;

	private $errors = [];
	private $currentMapping;

	public function __construct(ConfigFactory $configFactory, Logger $logger)
	{
		$this->configFactory = $configFactory;
		$this->logger = $logger;
		parent::__construct();
	}

	public function configure()
	{
		$this->setName('validate:source')
			->setDescription('Validate loaded mappings against configured source view/table.')
			->addArgument(self::ARG_COLLECTION_NAME, InputArgument::OPTIONAL, 'The name of the collection you are validating.')
			->addArgument(self::ARG_MAPPING_NAME, InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The name of the mapping you are validating.');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 * @throws \Exception
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$collectionNames = [];

		$importMappingConfig = $this->configFactory->getConfig('importMappingSettings');
		if (!$input->getArgument(self::ARG_COLLECTION_NAME)) {
			$collectionNames = array_keys($importMappingConfig['parameters']);
		} else if (!array_key_exists($input->getArgument(self::ARG_COLLECTION_NAME), $importMappingConfig['parameters'])) {
			throw new Exception("Invalid collection name");
		} else {
			array_push($collectionNames, $input->getArgument(self::ARG_COLLECTION_NAME));
		}
		foreach ($collectionNames as $collectionName) {
			$internalMappings = $importMappingConfig['parameters'][$collectionName];
			$mappings = [];
			$mappingNameArgs = null;

			foreach ($internalMappings['mappings'] as $internalMapping) {
				$mappings[$internalMapping['name']] = $internalMapping['table_name'];
			}
			if ($input->getArgument(self::ARG_MAPPING_NAME)) {
				$mappingNameArgs = $input->getArgument(self::ARG_MAPPING_NAME);
			} else {
				$mappingNameArgs = array_keys($mappings);
			}

			$connections = $this->configFactory->getConfig()['connections'];
			$connectionName = $internalMappings['connection'];
			if (!isset($connections[$connectionName])) {
				throw new \Exception(sprintf('Connection `%s` does not exist. Valid connections are: %s.', $connectionName, join(',', array_keys($connections))));
			}
			$dbConfig = $connections[$connectionName];
			$db = new Db($dbConfig['driver'], $dbConfig['database'], $dbConfig['host'], $dbConfig['username'], $dbConfig['password']);
			$pdo = $db->getPDO();

			foreach ($mappingNameArgs as $mappingNameArg) {
				$output->writeln("\n<fg=white;options=bold>Mapping: </><fg=blue;options=bold>$collectionName$mappingNameArg</>");
				$this->currentMapping = $collectionName.$mappingNameArg;
				$sourceFields = [];
				$rows = $pdo->query("DESCRIBE `$mappings[$mappingNameArg]`");
				if ($rows) {
					$infoFieldsArray = [];
					$infoFields = '';
					while ($row = $rows->fetch()) {
						array_push($sourceFields, $row['Field']);
						if (strlen($infoFields) + strlen($row['Field']) < self::MAX_LINE_WIDTH - 16) {
							$infoFields .= $row['Field'] . ' ';
						} else {
							array_push($infoFieldsArray, $infoFields);
							$infoFields = $row['Field'] . ' ';
						}
					}
					$output->writeln("\t<fg=white;options=bold>View fields found:</>");
					$output->writeln("\t\t<fg=blue;options=bold>" . implode("\n\t\t", $infoFieldsArray) . '</>');
				} else {
					$output->writeln(sprintf("\t\t<fg=yellow;options=bold>Obtained the following warnings: %s</>", json_encode($rows->fetchAll())));
				}
				$mappingJson = $this->getMappingFields($collectionName, $mappingNameArg, $output);
				if ($mappingJson == null) {
					$output->writeln(sprintf("\t\t<fg=red;options=bold>No mapping JSON file found.</>"));
				}
				$mappingObject = (array)json_decode($mappingJson);
				$output->writeln("\t\t<fg=white;options=bold>Validating fields</>");
				$this->recursiveMatcher($mappingObject, $sourceFields, $output);
			}
		}
		if($this->errors){
			throw new \Exception('Validation errors occurred for the following mappings: ' . json_encode($this->errors));
		}
	}

	/**
	 * @param $parent
	 * @param $sourceFields
	 * @param $output
	 * @throws \Exception
	 */
	private function recursiveMatcher($parent, $sourceFields, $output)
	{
		foreach ($parent as $property) {

			$type = gettype($property);
			if ($type === 'object' || $type === 'array') {
				$arrayProperty = $type === 'object' ? get_object_vars($property) : $property;
				if (array_key_exists('source', $arrayProperty)) {
					$this->validateAgainstSource($arrayProperty['source'], $sourceFields, $output);
					unset($arrayProperty['source']);
				}

				$this->recursiveMatcher($arrayProperty, $sourceFields, $output);
			} else if ($type === 'string') {
				$pregMatches = null;
				$count = preg_match_all('/\^\w+/', $property, $pregMatches);
				if ($count){
					if ($pregMatches && !!$pregMatches[0]) {
						foreach ($pregMatches as $pregMatch) {
							if (is_array($pregMatch)) {
								foreach ($pregMatch as $match){
									$this->validateAgainstSource($match, $sourceFields, $output);
								}
							} else {
								$this->validateAgainstSource($pregMatch, $sourceFields, $output);
							}
						}
					}
				}
			} else if ($type === 'integer' || $type === 'NULL' || $type === 'boolean') {
				// noop
			} else {
				throw new \Exception('Unknown type: ' . $type);
			}
		}
	}

	/**
	 * @param $value
	 * @param $sourceFields
	 * @param $output
	 * @throws \Exception
	 */
	private function validateAgainstSource($value, $sourceFields, $output)
	{
		$value = preg_replace('/^\^/', '', $value);
		foreach ($sourceFields as $sourceField) {
			$type = gettype($value);
			if ($type === 'string') {
				if ($value === $sourceField || preg_match('/_CONSTANT_/', $value)) {
					$output->writeln(sprintf("\t\t\t<fg=green;options=bold>Passed: %s</>", $value));
					return;
				}
			} else {
				throw new \Exception(sprintf('Value cannot be an object or an array. Type: %s, value: %s', $type, json_encode($value)));
			}
		}
		$error = sprintf('Source Validation Failed: %s', $value);
		$output->writeln(sprintf("\t\t\t<fg=red;options=bold>Failed: %s</>", $error));
		$this->addError($error);
	}

	private function getMappingFields($collectionName, $mappingNameArg, $output)
	{
		$mappingJson = null;
		$cwd = getcwd();
		$ds = DIRECTORY_SEPARATOR;
		$files = Finder::create()->in("$cwd{$ds}steps{$ds}mappings{$ds}json")->name("*$collectionName$mappingNameArg.json");
		if ($files->count() == 0) {
			throw new Exception("Could not find match for $mappingNameArg.json");
		}
		if ($files->count() > 1) {
			$output->writeln("\t\t<fg=yellow;options=bold>The Mapping JSON matched on more than one file. ($collectionName$mappingNameArg)</>");
		}
		$output->writeln("\t<fg=white;options=bold>Validating mapping JSON</>");
		foreach ($files as $file) {
			if ($mappingJson != null) {

			} else {
				$output->writeln("\t\t<fg=green;options=bold>Mapping JSON found: $mappingNameArg</>");
			}
			$mappingJson = $file->getContents($file);
		}
		return $mappingJson;
	}

	private function addError($error)
	{
		$this->logger->error($error);
		$this->errors[$this->currentMapping][] = $error;
	}
}
