<?php

namespace CSIROCMS\Command;

use CSIROCMS\Application\CommandHelpers;
use CSIROCMS\Application\Profile;
use CSIROCMS\Config\Factory\ConfigFactory;
use Exception;
use Monolog\Logger;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ValidateDestinationCommand extends Command
{
	const ARG_COLLECTION_NAME = 'collection_name';
	const ARG_MAPPING_NAME = 'mapping_name';
	const PROFILE_NAME = 'csiro-nrca';
	private static $refineries = [
		'collectionHierarchyBuilder' => ['table' => 'ca_collections', 'prefix' => 'collection'],
		'collectionSplitter' => ['table' => 'ca_collections', 'prefix' => 'collection'],
		'entityHierarchyBuilder' => ['table' => 'ca_entities', 'prefix' => 'entity'],
		'entityJoiner' => ['table' => 'ca_entities', 'prefix' => 'entity'],
		'entitySplitter' => ['table' => 'ca_entities', 'prefix' => 'entity'],
		'listItemHierarchyBuilder' => ['table' => 'ca_list_items', 'prefix' => 'listItem'],
		'listItemIndentedHierarchyBuilder' => ['table' => 'ca_list_items', 'prefix' => 'listItem'],
		'listItemSplitter' => ['table' => 'ca_list_items', 'prefix' => 'listItem'],
		'loanSplitter' => ['table' => 'ca_loans', 'prefix' => 'loan'],
		'movementSplitter' => ['table' => 'ca_movements', 'prefix' => 'movement'],
		'objectHierarchyBuilder' => ['table' => 'ca_objects', 'prefix' => 'object'],
		'objectLotSplitter' => ['table' => 'ca_object_lots', 'prefix' => 'objectLot'],
		'objectSplitter' => ['table' => 'ca_objects', 'prefix' => 'object'],
		'occurrenceHierarchyBuilder' => ['table' => 'ca_occurrences', 'prefix' => 'occurrence'],
		'occurrenceSplitter' => ['table' => 'ca_occurrences', 'prefix' => 'occurrence'],
		'placeHierarchyBuilder' => ['table' => 'ca_places', 'prefix' => 'place'],
		'placeSplitter' => ['table' => 'ca_places', 'prefix' => 'place'],
		'storageLocationHierarchyBuilder' => ['table' => 'ca_storage_locations', 'prefix' => 'storageLocation'],
		'storageLocationSplitter' => ['table' => 'ca_storage_locations', 'prefix' => 'storageLocation'],
	];

	/**
	 * @var array Errors discovered during a validation run.
	 */
	private $errors = [];

	/**
	 * @var Profile
	 */
	protected $profile;

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

	public function __construct(ConfigFactory $configFactory, Logger $logger, Finder $finder)
	{
		$this->configFactory = $configFactory;
		$this->logger = $logger;
		$this->finder = $finder;
		parent::__construct();
	}

	private static function parents($refinery, $settings)
	{
		$typeKey = sprintf('%s_parents', $refinery);
		return isset($settings[$typeKey]) ? $settings[$typeKey] : [];
	}

	private static function refineryAttributes($refinery, $settings)
	{
		$typeKey = sprintf('%s_attributes', $refinery);
		return isset($settings[$typeKey]) ? $settings[$typeKey] : [];
	}

	private static function refineryInterstitial($refinery, $settings)
	{
		$typeKey = sprintf('%s_interstitial', $refinery);
		return isset($settings[$typeKey]) ? $settings[$typeKey] : [];
	}

	private static function refineryType($refinery, $settings)
	{

		Assert::assertArrayHasKey($refinery, self::$refineries, sprintf('Missing refinery definition: %s.', $refinery));
		$typeKey = sprintf('%s_%sType', $refinery, self::$refineries[$refinery]['prefix']);
		$defaultTypeKey = $typeKey . 'Default';
		if (isset($settings[$typeKey])) {
			$ret = $settings[$typeKey];
		} else if (isset($settings[$defaultTypeKey])) {
			$ret = $settings[$defaultTypeKey];
		} else {
			throw new Exception(sprintf('Missing refinery type definition: %s. You need to set one of `%s` or `%s` ', $refinery, $typeKey, $defaultTypeKey));
		}
		return $ret;
	}

	private static function refineryRelationshipType($refinery, $settings, $destination)
	{
		if (preg_match('/^ca_\w+\.[\w\.]+$/', $destination)) {
			// This is not a relationship type, it's an attribute / intrinsic field that joins to a related table
			// Most often this is for List attributes but can also be an instance of AuthorityAttributeValue or another field
			// like source_id, type_id, parent_id, lot_id, and item_status_id
			$ret = null;
		} else {
			$typeKey = sprintf('%s_relationshipType', $refinery);
			$defaultTypeKey = $typeKey . 'Default';
			if (isset($settings[$typeKey])) {
				$ret = $settings[$typeKey];
			} else if (isset($settings[$defaultTypeKey])) {
				$ret = $settings[$defaultTypeKey];
			} else {
				throw new Exception(sprintf('Missing refinery relationship type definition: `%s`. You need to set one of `%s` or `%s`.', $refinery, $typeKey, $defaultTypeKey));
			}
		}
		return $ret;
	}

	private static function refineryTable($refinery)
	{
		Assert::assertArrayHasKey($refinery, self::$refineries, sprintf('Missing refinery definition: %s.', $refinery));
		return self::$refineries[$refinery]['table'];

	}

	public function configure()
	{
		$this->setName('validate:destination')
			->setDescription('Validate loaded mappings against CollectiveAccess installation profile.')
			->addArgument(self::ARG_COLLECTION_NAME, InputArgument::OPTIONAL, 'The name of the collection you are validating.')
			->addArgument(self::ARG_MAPPING_NAME, InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The name of the mapping you are validating.');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$caHome = getenv('COLLECTIVEACCESS_HOME');
		if (!$caHome) {
			throw new Exception('You need to have the COLLECTIVEACCESS_HOME variable defined in your environment');
		}
		$this->profile = new Profile(self::PROFILE_NAME, $caHome . '/install/profiles/xml/');
		$collection = $input->getArgument(self::ARG_COLLECTION_NAME);
		$mappings = $input->getArgument(self::ARG_MAPPING_NAME);
		$mappings = array_map(function ($mapping) use ($collection) {
			return preg_quote($collection . $mapping);
		}, $mappings);
		$this->validateMappings($this->finder->in(MappingExportCommand::MAPPING_DIR)->name('*.json'), $mappings, $collection);
		$numErrors = count($this->errors);
		if ($numErrors) {
			throw new Exception(sprintf("%s validation error%s occurred:\n✘ %s", $numErrors, $numErrors > 1 ? 's' : '', join("\n✘ ", $this->errors)));
		}
	}

	/**
	 * @param Exception $e
	 */
	private function queueError($e)
	{
		$this->logger->error($e->getMessage());
		$this->logger->debug($e->getTraceAsString());
		$this->errors[] = $e->getMessage();
	}

	/**
	 * @param $files
	 * @param $mappings
	 * @param $collection
	 */
	private function validateMappings($files, $mappings, $collection)
	{
		/** @var SplFileInfo $file */
		foreach ($files as $file) {
			try {
				$mapping = json_decode($file->getContents(), JSON_OBJECT_AS_ARRAY);
				$mappingCode = $mapping['importer_code'];
				if (!isset($mappingCode)) {
					throw new Exception(sprintf('No importer_code defined from mapping in file %s.', $file->getFilename()));
				}
				$table = preg_replace('/^(ca\w+).*$/', '$1', $file->getFilename());
				if (
					($mappings && !in_array($mappingCode, $mappings)) ||
					($collection && !preg_match("/^$collection.*/", $mappingCode))
				) {
					continue;
				}
				if (isset($mapping['importer']['settings']['type'])) {
					$type = $mapping['importer']['settings']['type'];
					CommandHelpers::assert($this->profile->typeExistsForTable($type, $table));
				} else {
					throw new Exception(sprintf('The mapping %s requires a type settings.', $mappingCode));
				}
				$this->validateGroups($mapping['groups'], $type, $mappingCode, $table);
			} catch (Exception $e) {
				$this->queueError($e);
			}
		}
	}

	/**
	 * @param $groups
	 * @param $type
	 * @param $mappingCode
	 * @param $table
	 */
	private function validateGroups($groups, $type, $mappingCode, $table)
	{
		foreach ($groups as $groupCode => $group) {
			foreach ($group['items'] as $i => $item) {
				try {
					$destination = $item['destination'];
					$this->logger->debug(sprintf('Checking for existence of destination `%s` in group `%s`.', $destination, $groupCode));
					$this->profile->assertDestinationExists($destination, $type, sprintf('Destination `%s` does not exist for group `%s` in mapping `%s`', $destination, $groupCode, $mappingCode), null);
					$this->validateRefineries($item['settings'], $destination, $table, $type, $groupCode, $mappingCode);
				} catch (Exception $e) {
					$this->queueError($e);
				}
			}
		}
	}

	/**
	 * @param $attributes
	 * @param $type
	 * @param $groupCode
	 * @param $mappingCode
	 * @param $table
	 * @param $refinery
	 * @param $parent
	 */
	private function validateAttributes($attributes, $type, $groupCode, $mappingCode, $table, $refinery = null, $parent = null)
	{
		$refinery = isset($refinery) ? sprintf('in refinery `%s`', $refinery) : '';
		$parent = isset($parent) ? sprintf('in parent `%s`', $parent) : '';
		foreach ($attributes as $attributeDestination => $attributeValue) {
			try {
				$this->profile->assertDestinationExists($attributeDestination, $type, sprintf('Destination `%s` does not exist for type `%s` %s%sin group `%s` in mapping `%s` ', $attributeDestination, $type, $refinery, $parent, $groupCode, $mappingCode), $table);
			} catch (Exception $e) {
				$this->queueError($e);
			}
		}
	}

	/**
	 * @param $settings
	 * @param $destination
	 * @param $table
	 * @param $type
	 * @param $groupCode
	 * @param $mappingCode
	 */
	private function validateRefineries($settings, $destination, $table, $type, $groupCode, $mappingCode)
	{
		// Filter because if there are no refineries then $settings['refineries'] = [ null ]
		foreach (array_filter($settings['refineries']) as $refinery) {
			try {
				$refineryTable = self::refineryTable($refinery);
				if (!preg_match('/\w+HierarchyBuilder/', $refinery)) {
					$refineryType = self::refineryType($refinery, $settings);
					$this->profile->typeExistsForTable($refineryType, $refineryTable);
					$refineryRelationshipType = self::refineryRelationshipType($refinery, $settings, $destination);
					if ($refineryRelationshipType) {
						$this->profile->assertRelationshipTypeExists($refineryRelationshipType, $table, $type, $refineryTable, $refineryType, sprintf('Refinery `%s` group `%s` mapping `%s`', $refinery, $groupCode, $mappingCode));
					}
				} else {
					$refineryType = 'HierarchyBuilder';
				}
				$this->validateAttributes(self::refineryAttributes($refinery, $settings), $refineryType, $groupCode, $mappingCode, $refineryTable, $refinery);
//				$interstitialTable = $this->interstitalTable
//				$this->validateAttributes(self::refineryInterstitial($refinery, $settings), $refineryType, $groupCode, $mappingCode, )
				$this->validateParents(self::parents($refinery, $settings), $refineryTable, $groupCode, $mappingCode, $refinery);
			} catch (Exception $e) {
				$this->queueError($e);
			}
		}
	}

	/**
	 * @param $parents
	 * @param $refineryTable
	 * @param $groupCode
	 * @param $mappingCode
	 * @param $refinery
	 */
	private function validateParents($parents, $refineryTable, $groupCode, $mappingCode, $refinery)
	{
		foreach ($parents as $i => $parent) {
			try {
				Assert::assertArrayHasKey('type', $parent, 'No type defined for parent %s in refinery %s');
				$parentType = $parent['type'];
				if (preg_match('/\^\w+/', $parentType)) {
					$this->logger->warn(sprintf('Cannot perform full parent hierarchy checks for parent `%s` with type `%s`', $i, $parentType));
					// TODO implement once we can read the data.
				} else {
					$this->profile->typeExistsForTable($parentType, $refineryTable);
					$parentAttributes = isset($parent['attributes']) ? $parent['attributes'] : [];
					$this->validateAttributes($parentAttributes, $parentType, $groupCode, $mappingCode, $refineryTable, $refinery, $i);
				}
			} catch (Exception $e) {
				$this->queueError($e);
			}
		}
	}
}
