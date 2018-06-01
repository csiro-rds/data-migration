<?php

namespace CSIROCMS\Command;

use CSIROCMS\Config\Factory\ConfigFactory;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MappingExportCommand extends Command
{
	const COMMAND = 'mapping:export';
	const ARG_NAME = 'mapping_name';
	const MAPPING_DIR = 'steps/mappings/json';
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
		$this->setName(self::COMMAND)
			->setDescription('Export loaded mappings into JSON format.')
			->addArgument(self::ARG_NAME, InputArgument::OPTIONAL + InputArgument::IS_ARRAY, 'Name(s) of the mappings you want to export.')
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 * @throws \ApplicationException
	 * @throws \Exception
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		require_once getenv('COLLECTIVEACCESS_HOME') . DIRECTORY_SEPARATOR . 'setup.php';
		require_once __CA_MODELS_DIR__ . DIRECTORY_SEPARATOR . 'ca_data_importers.php';
		$mapping = new \ca_data_importers();

		if (!is_dir(self::MAPPING_DIR)) {
			mkdir(self::MAPPING_DIR);
		}
		$dm = \Datamodel::load();
		$wanted = $input->getArgument(self::ARG_NAME);
		$found = [];

		foreach (\ca_data_importers::find(['deleted' => false], ['returnAs' => 'ids']) as $id) {
			$mapping->load($id, false);
			$groups = $mapping->getGroups();
			$items = $mapping->getItems();
			$resultGroups = [];
			$importer_code = $mapping->get('importer_code');
			$this->logger->debug("Loading import mapping with id: '$id' and importer code: '$importer_code'");
			if ($wanted){
				$this->logger->debug("Checking '$importer_code' against wanted values:" . json_encode($wanted));
				if (array_search($importer_code, $wanted) !== false){
					$this->logger->debug("found import mapping: '$importer_code'");
					$found[] = $importer_code;
				} else {
					$this->logger->debug("Mapping: '$importer_code' does not match, skipping to next mapping.");
					// skip to next mapping
					continue;
				}
			}
			foreach ($items as $i => $item) {
				/** @var \ca_data_importer_groups $group */
				$group = $groups[$item['group_id']];
				$groupCode = $group['group_code'];
				$groupSettings = $group->SETTINGS;
				unset($item['item_id'], $item['importer_id'], $item['group_id']);
				if (!isset($resultGroups[$groupCode]['settings'])) {
					$resultGroups[$groupCode]['settings'][] = $groupSettings;
				}
				$resultGroups[$groupCode]['items'][] = $item;
			}
			$table = $dm->getTableName($mapping->get('table_num'));
			$fileName = self::MAPPING_DIR . DIRECTORY_SEPARATOR .
				$table . '.' .
				$mapping->get('importer_code') . '.json';
			$fieldValues = $mapping->getFieldValuesArray();
			// Remove importer_id which changes on subsequent mapping loads
			unset($fieldValues['importer_id']);
			// Remove  `worksheet` from exported mappings because it depends on the hash of the latest loaded worksheet.
			// removing this reduces unnecessary noise in the exported mappings
			unset($fieldValues['worksheet']);
			$this->logger->debug(sprintf('Exporting import mapping to: %s.', $fileName));
			file_put_contents($fileName,
				json_encode(
					[
						'importer' => $fieldValues,
						'importer_code' => $importer_code,
						'importer_name' => $mapping->getPreferredLabels(null, false, ['forDisplay' => true]),
						'table' => $table,
						'settings' => $mapping->SETTINGS,
						'groups' => $resultGroups
					],
					JSON_PRETTY_PRINT)
			);
			$this->logger->debug("Finished export to '$fileName'");
		}
		$diff = array_diff($wanted, $found);
		if ($diff){
			$this->logger->error('Mismatch between wanted and found values', ['wanted'=>$wanted, 'found' => $found]);
			throw new \Exception(sprintf('Mapping%s `%s` not found and cannot be exported.', count($diff) === 1 ? '' : 's', join(', ', $diff)));
		}

	}
}
