<?php

namespace CSIROCMS\Application;


use DOMDocument;
use DOMElement;
use DOMXpath;
use Exception;
use PHPUnit\Framework\Assert;

class Profile
{
	/** @var DOMDocument */
	protected $profile;
	/** @var DOMXpath */
	protected $xpath;

	public function __construct($name, $path, $profileSchema = null)
	{
		if (!$this->profile) {
			$filename = $path . DIRECTORY_SEPARATOR . $name . '.xml';
			$this->profile = new DOMDocument();
			$this->profile->load($filename);
			$this->xpath = new DOMXPath($this->profile);
			$this->xpath->registerNamespace('php', 'http://php.net/xpath');
			$this->xpath->registerPhpFunctions('preg_match');
			$this->profile->schemaValidate($profileSchema ?: $path . DIRECTORY_SEPARATOR . 'profile.xsd');
		}
	}

	/**
	 * @param $table
	 * @return string
	 */
	public static function typeListCode($table)
	{
		$table_map = [
			'ca_entities' => 'entity',
		];
		if (isset($table_map[$table])) {
			$table = $table_map[$table];
		} else {
			$table = self::tableFromMapping($table);
		}
		$listCode = $table . '_types';
		return $listCode;
	}

	/**
	 * @param $field
	 * @return bool
	 */
	public static function intrinsicExists($field)
	{
		$intrinsics = ['basic' => [
			'idno', 'idno_stub', 'source_info', 'status', 'access', 'extent', 'extent_units', 'source_info', 'view_count', 'rating_status', 'tagging_status', 'commenting_status', 'is_template', 'rank',
			'accession_sdatetime', 'accession_edatetime', 'deaccession_sdatetime', 'deaccession_edatetime', 'is_deaccessioned', 'deaccession_notes',
			// list items
			'is_enabled',
			// interstitials
			'sdatetime', 'edatetime',
		],
			'label' => [
				// type
				'preferred_labels', 'nonpreferred_labels',
				// general
				'name',
				// list items
				'name_singular', 'name_plural',
				// entities
				'displayname', 'forename', 'other_forenames', 'middlename', 'surname', 'prefix', 'suffix',
			], 'id' => array_map(function ($id) {
				return $id . '_id';
			}, [
				'hierarchy', 'hierarchy_object', 'parent', 'source', 'type', 'locale', 'list', 'home_location',
				'object', 'entity', 'collection', 'set', 'occurrence', 'place', 'item', 'occurrence', 'lot', 'representation', 'label', 'relation', 'item_status', 'deaccession_type', 'current_loc', 'circulation_status',
			])];
		foreach ($intrinsics as $type => $intrinsicGroup) {
			if (array_search($field, $intrinsicGroup) !== false) {
				return true;
			}
		}
		return false;
	}

	private static function destinationFromMapping($destination)
	{
		return explode('.', preg_replace('/^ca_\w+\.?/', '', $destination));
	}

	public static function tableFromMapping($table)
	{
		$table = preg_replace('/^ca_(.*)s$/', '$1', $table);
		return $table ?: null;
	}

	/**
	 * @param $type
	 * @param string $table The table for which the mapping is
	 * @param null $context Context for the error message.
	 * @return boolean
	 */
	public function typeExistsForTable($type, $table, $context = null)
	{
		$context = $context ? " Context: $context." : null;
		$listCode = self::typeListCode($table);
		$typeList = $this->xpath->query("/profile/lists/list[@code='$listCode']");
		Assert::assertCount(1, $typeList, "The type list for $table ($listCode) needs to exist.$context");
		Assert::assertCount(1, $this->xpath->query("/profile/lists/list[@code='$listCode']/items//item[@idno='$type']"), "The type '$type' must exist in the list for $table ($listCode).$context");
		return true;
	}

	public function assertDestinationExists($destination, $type, $message, $table = null)
	{
		$table = $table ?: preg_replace('/^(ca_\w+).*$/', "$1", $destination);
		$destinations = self::destinationFromMapping($destination);
		$parentDestination = null;
		if ($destination === $table && $destination) {
			// Destination is a simple table name like `ca_places`. Should have a refinery.
			$this->assertTableExists($table, sprintf('Table `%s` is not a valid CA table.', $table));
			$exists = true;
		} else {
			$exists = false;
			$parentDestination = null;
			foreach ($destinations as $i => $individualDestination) {
				if ($i === 0 && count($destinations) > 1) {
					$parentDestination = $individualDestination;
				}
				if (!self::intrinsicExists($individualDestination)) {
					$exists = $this->assertElementExists($individualDestination, $table, $type, $i === 0 ? null : $parentDestination, $message);
				} else {
					$exists = true;
				}
			}
		}
		Assert::assertTrue($exists, $message);
	}

	public function assertElementExists($elementCode, $table, $type, $parentElementCode = null, $context = null)
	{

		$result = $this->xpath->query(
		/** @lang XPath */
			"/profile/elementSets//metadataElement[@code='$elementCode']"
		);
		Assert::assertCount(1, $result,sprintf('Metadata element missing. %s', $context));
		if ($parentElementCode) {
			$result = $this->xpath->query(
			/** @lang XPath */
				"/profile/elementSets/metadataElement[@code='$parentElementCode']//metadataElement[@code='$elementCode']");
			Assert::assertCount(1, $result,
				sprintf('Child Element Missing. Parent element `%s`. %s', $elementCode, $parentElementCode, $context)
			);

		}
		/** @var DOMElement $element */
		$element = $result->item(0);
		if ($element->getAttribute('datatype') === 'List'){

			$list_code = $element->getAttribute('list');
			$element_code = $element->getAttribute('code');
			Assert::assertNotNull($list_code, "The list attribute needs to be set for the {$element->getNodePath()} element");
			Assert::assertEquals(1, $this->xpath->query("/profile/lists/list[@code='$list_code']")->length, "List attributes require a matching list. The list '$list_code' does not exist and is required for the $element_code element." .
				"\nHere's an xml element:\n" .
				<<<"LIST_XML"
<list code="$list_code" hierarchical="0" system="1" vocabulary="0">
  <labels>
    <label locale="en_AU">
      <name>$list_code</name>
    </label>
  </labels>
</list>
LIST_XML
			);
		}
		if (!$parentElementCode){
			$path = $element->getNodePath();

			$path = /** @lang XPath */
				"$path/typeRestrictions/restriction[table='$table']";
			$result = $this->xpath->query($path);
			CommandHelpers::assert(($result)->length > 0, sprintf('At least one restriction is required for element %s in table %s.', $elementCode, $table));
			$typeRestrictionFound = false;
			/** @var DOMElement $restriction */
			foreach ($result as $restriction) {
				$restrictionTypeElement = $restriction->getElementsByTagName('type');
				if ($restrictionTypeElement->length) {
					$restrictionTypeValue = $restrictionTypeElement->item(0)->nodeValue;
					$types = [$restrictionTypeValue];
					$includeSubtypesElement = $restriction->getElementsByTagName('includeSubtypes');
					if ($includeSubtypesElement && (bool)$includeSubtypesElement->item(0)->nodeValue) {
						$types = array_merge($types, $this->subtypes($restrictionTypeValue, $table));
					}
					$typeRestrictionFound |= in_array($type, $types);
				} else {
					$typeRestrictionFound = true;
				}
			}
			Assert::assertTrue((bool)$typeRestrictionFound, sprintf('No restriction found for element `%s` in records of type `%s` in table `%s`', $elementCode, $type, $table));
		}

		return true;
	}

	private function assertTableExists($table, $message = null)
	{
		$tables = [
			'ca_entities',
			'ca_list_items',
			'ca_lists',
			'ca_loans',
			'ca_movements',
			'ca_object_lots',
			'ca_object_representations',
			'ca_objects',
			'ca_occurrences',
			'ca_places',
		];
		Assert::assertContains($table, $tables, $message);
	}

	private function subtypes($type, $table)
	{
		$listCode = self::typeListCode($table);
		$this->typeExistsForTable($type, $table, sprintf('Evaluating subtypes of type `%s` in table `%s`', $type, $table));
		$subtypes = [];
		/** @var DOMElement $subtype */
		foreach ($this->xpath->query("/profile/lists/list[@code='$listCode']//item[@idno='$type']//item") as $subtype) {
			$subtypes[] = $subtype->getAttribute('idno');
		}
		return $subtypes;
	}

	public function assertRelationshipTypeExists($relationshipType, $leftTable, $leftType, $rightTable, $rightType, $context = null)
	{
		if ($context){
			$context = sprintf(' %s', $context);
		}
		$relationshipTables = [
			'ca_bundle_displays_x_user_groups' => ['ca_bundle_displays', 'ca_user_groups'],
			'ca_bundle_displays_x_users' => ['ca_bundle_displays', 'ca_users'],
			'ca_collections_x_collections' => ['ca_collections', 'ca_collections'],
			'ca_collections_x_storage_locations' => ['ca_collections', 'ca_storage_locations'],
			'ca_entities_x_collections' => ['ca_entities', 'ca_collections'],
			'ca_entities_x_entities' => ['ca_entities', 'ca_entities'],
			'ca_entities_x_occurrences' => ['ca_entities', 'ca_occurrences'],
			'ca_entities_x_places' => ['ca_entities', 'ca_places'],
			'ca_entities_x_storage_locations' => ['ca_entities', 'ca_storage_locations'],
			'ca_entities_x_vocabulary_terms' => ['ca_entities', 'ca_list_items'],
			'ca_list_items_x_list_items' => ['ca_list_items', 'ca_list_items'],
			'ca_loans_x_collections' => ['ca_loans', 'ca_collections'],
			'ca_loans_x_entities' => ['ca_loans', 'ca_entities'],
			'ca_loans_x_loans' => ['ca_loans', 'ca_loans'],
			'ca_loans_x_movements' => ['ca_loans', 'ca_movements'],
			'ca_loans_x_object_lots' => ['ca_loans', 'ca_object_lots'],
			'ca_loans_x_object_representations' => ['ca_loans', 'ca_object_representations'],
			'ca_loans_x_objects' => ['ca_loans', 'ca_objects'],
			'ca_loans_x_occurrences' => ['ca_loans', 'ca_occurrences'],
			'ca_loans_x_places' => ['ca_loans', 'ca_places'],
			'ca_loans_x_storage_locations' => ['ca_loans', 'ca_storage_locations'],
			'ca_loans_x_vocabulary_terms' => ['ca_loans', 'ca_list_items'],
			'ca_movements_x_collections' => ['ca_movements', 'ca_collections'],
			'ca_movements_x_entities' => ['ca_movements', 'ca_entities'],
			'ca_movements_x_movements' => ['ca_movements', 'ca_movements'],
			'ca_movements_x_object_lots' => ['ca_movements', 'ca_object_lots'],
			'ca_movements_x_object_representations' => ['ca_movements', 'ca_object_representations'],
			'ca_movements_x_objects' => ['ca_movements', 'ca_objects'],
			'ca_movements_x_occurrences' => ['ca_movements', 'ca_occurrences'],
			'ca_movements_x_places' => ['ca_movements', 'ca_places'],
			'ca_movements_x_storage_locations' => ['ca_movements', 'ca_storage_locations'],
			'ca_movements_x_vocabulary_terms' => ['ca_movements', 'ca_list_items'],
			'ca_object_lots_x_collections' => ['ca_object_lots', 'ca_collections'],
			'ca_object_lots_x_entities' => ['ca_object_lots', 'ca_entities'],
			'ca_object_lots_x_object_lots' => ['ca_object_lots', 'ca_object_lots'],
			'ca_object_lots_x_object_representations' => ['ca_object_lots', 'ca_object_representations'],
			'ca_object_lots_x_occurrences' => ['ca_object_lots', 'ca_occurrences'],
			'ca_object_lots_x_places' => ['ca_object_lots', 'ca_places'],
			'ca_object_lots_x_storage_locations' => ['ca_object_lots', 'ca_storage_locations'],
			'ca_object_lots_x_vocabulary_terms' => ['ca_object_lots', 'ca_list_items'],
			'ca_object_representations_x_collections' => ['ca_object_representations', 'ca_collections'],
			'ca_object_representations_x_entities' => ['ca_object_representations', 'ca_entities'],
			'ca_object_representations_x_object_representations' => ['ca_object_representations', 'ca_object_representations'],
			'ca_object_representations_x_occurrences' => ['ca_object_representations', 'ca_occurrences'],
			'ca_object_representations_x_places' => ['ca_object_representations', 'ca_places'],
			'ca_object_representations_x_storage_locations' => ['ca_object_representations', 'ca_storage_locations'],
			'ca_object_representations_x_vocabulary_terms' => ['ca_object_representations', 'ca_list_items'],
			'ca_objects_x_collections' => ['ca_objects', 'ca_collections'],
			'ca_objects_x_entities' => ['ca_objects', 'ca_entities'],
			'ca_objects_x_object_representations' => ['ca_objects', 'ca_object_representations'],
			'ca_objects_x_objects' => ['ca_objects', 'ca_objects'],
			'ca_objects_x_occurrences' => ['ca_objects', 'ca_occurrences'],
			'ca_objects_x_places' => ['ca_objects', 'ca_places'],
			'ca_objects_x_storage_locations' => ['ca_objects', 'ca_storage_locations'],
			'ca_objects_x_vocabulary_terms' => ['ca_objects', 'ca_list_items'],
			'ca_occurrences_x_collections' => ['ca_occurrences', 'ca_collections'],
			'ca_occurrences_x_occurrences' => ['ca_occurrences', 'ca_occurrences'],
			'ca_occurrences_x_storage_locations' => ['ca_occurrences', 'ca_storage_locations'],
			'ca_occurrences_x_vocabulary_terms' => ['ca_occurrences', 'ca_list_items'],
			'ca_places_x_collections' => ['ca_places', 'ca_collections'],
			'ca_places_x_occurrences' => ['ca_places', 'ca_occurrences'],
			'ca_places_x_places' => ['ca_places', 'ca_places'],
			'ca_places_x_storage_locations' => ['ca_places', 'ca_storage_locations'],
			'ca_places_x_vocabulary_terms' => ['ca_places', 'ca_list_items'],
			'ca_representation_annotations_x_entities' => ['ca_representation_annotations', 'ca_entities'],
			'ca_representation_annotations_x_objects' => ['ca_representation_annotations', 'ca_objects'],
			'ca_representation_annotations_x_occurrences' => ['ca_representation_annotations', 'ca_occurrences'],
			'ca_representation_annotations_x_places' => ['ca_representation_annotations', 'ca_places'],
			'ca_representation_annotations_x_vocabulary_terms' => ['ca_representation_annotations', 'ca_list_items'],
			'ca_storage_locations_x_storage_locations' => ['ca_storage_locations', 'ca_storage_locations'],
			'ca_storage_locations_x_vocabulary_terms' => ['ca_storage_locations', 'ca_list_items'],
			'ca_tour_stops_x_collections' => ['ca_tour_stops', 'ca_collections'],
			'ca_tour_stops_x_entities' => ['ca_tour_stops', 'ca_entities'],
			'ca_tour_stops_x_objects' => ['ca_tour_stops', 'ca_objects'],
			'ca_tour_stops_x_occurrences' => ['ca_tour_stops', 'ca_occurrences'],
			'ca_tour_stops_x_places' => ['ca_tour_stops', 'ca_places'],
			'ca_tour_stops_x_tour_stops' => ['ca_tour_stops', 'ca_tour_stops'],
			'ca_tour_stops_x_vocabulary_terms' => ['ca_tour_stops', 'ca_list_items'],
			'ca_user_representation_annotations_x_entities' => ['ca_user_representation_annotations', 'ca_entities'],
			'ca_user_representation_annotations_x_objects' => ['ca_user_representation_annotations', 'ca_objects'],
			'ca_user_representation_annotations_x_occurrences' => ['ca_user_representation_annotations', 'ca_occurrences'],
			'ca_user_representation_annotations_x_places' => ['ca_user_representation_annotations', 'ca_places'],
			'ca_user_representation_annotations_x_vocabulary_terms' => ['ca_user_representation_annotations', 'ca_list_items'],
		];
		$relationshipTable = array_filter($relationshipTables, function($b) use ($leftTable, $rightTable) {
			$a = [$leftTable, $rightTable];
			return array_diff($a, $b) === array_diff($b, $a);
		});
		// logic errors
		if (!$relationshipTable){
			throw new Exception(sprintf('Relationship table not defined for %s join %s.%s', $leftTable, $rightTable, $context));
		} elseif (count($relationshipTable) > 1){
			throw new Exception(sprintf('Ambiguous relationship found. Possible relationships are: %s.%s', json_encode($relationshipTable), $context));
		}
		$relationshipTableName = current(array_keys($relationshipTable));
		// Standardise the order of tables / types
		$leftTable = $relationshipTable[$relationshipTableName][0];
		$rightTable = $relationshipTable[$relationshipTableName][1];
		$query = $this->xpath->query("/profile/relationshipTypes/relationshipTable[@name='$relationshipTableName']");
		Assert::assertCount(1, $query, sprintf('Relationship table definition `%s` should exist in profile.%s', $relationshipTableName, $context));
		/** @var DOMElement $relationshipElement */
		$relationshipElement = $query->item(0);
		$path = $relationshipElement->getNodePath();
		$type = $this->xpath->query("$path/types/type[@code='$relationshipType']");
		Assert::assertCount(1, $type, sprintf('Relationship type `%s` required for relationship table `%s`.%s', $relationshipType, $relationshipTableName, $context));
		/** @var DOMElement $typeElement */
		$typeElement = $type->item(0);
		$restriction = [];
		foreach(['typeRestrictionLeft', 'typeRestrictionRight', 'includeSubtypesLeft', 'includeSubtypesRight'] as $attribute){
			$restriction[$attribute] = $typeElement->getAttribute($attribute);
		}
		$leftTypes = [$restriction['typeRestrictionLeft']];
		if ($restriction['includeSubtypesLeft']){
			$leftTypes = array_merge($leftTypes, $this->subtypes($restriction['typeRestrictionLeft'], $leftTable));
		}
		$leftTypes = array_filter($leftTypes);
		$rightTypes = [$restriction['typeRestrictionRight']];
		if ($restriction['includeSubtypesRight']) {
			$rightTypes = array_merge($rightTypes, $this->subtypes($restriction['typeRestrictionRight'], $rightTable));
		}
		$rightTypes = array_filter($rightTypes);
		if ($leftTable === $rightTable){
			// combine relationship types for both left and right
//			$leftTypes += $rightTypes;
//			$rightTypes = $leftTypes;
			$combinedTypes = $leftTypes + $rightTypes;

		}
		if ($leftTypes && !in_array($leftType, $rightTypes)){
			Assert::assertContains($leftType, $leftTypes, sprintf('Could not find record type `%s` for table `%s` on relationship table `%s` with relationship type `%s`. Types: `%s`', $leftType, $leftTable, $relationshipTableName, $relationshipType, json_encode($leftTypes)));
		}
		if ($rightTypes && !in_array($rightTypes, $leftTypes)){
			Assert::assertContains($rightType, $rightTypes, sprintf('Could not find record type `%s` for table `%s` on relationship table `%s` with relationship type `%s`. Types: `%s`', $rightType, $rightTable, $relationshipTableName, $relationshipType, json_encode($rightTypes)));
		}
	}
}
