<?php

namespace CSIROCMS\Command;

use CSIROCMS\Application\GoogleApi;
use CSIROCMS\Config\Factory\ConfigFactory;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class MappingDownloadCommand extends Command
{
	const ARG_COLLECTION_NAME = 'collection name';
	const ARG_MAPPING_NAME = 'mapping-name';

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
		$this->setName('mapping:download')
			->setDescription('Download mapping file from Google Drive')
			->addArgument(
				self::ARG_COLLECTION_NAME,
				InputArgument::REQUIRED,
				'Name of the the mapping collection to download from.')
			->addArgument(
				self::ARG_MAPPING_NAME,
				InputArgument::OPTIONAL,
				'Name of mapping to download. Defaults to all.');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$collectionName = $input->getArgument(self::ARG_COLLECTION_NAME);
		$googleApi = new GoogleApi($collectionName);
		$googleApi->configure();
		$service = $googleApi->getService();

		$listQuery = "name='" . $collectionName . "' and mimeType='application/vnd.google-apps.folder'";
		$mappingFolder = $googleApi->listFiles($service, $listQuery);
		$mappingName = $input->getArgument(self::ARG_MAPPING_NAME);
		if (count($mappingFolder->getFiles()) > 1) {
			throw new Exception('Too many matches for mapping parent folder: ' . $collectionName);
		}
		$spreadsheetFileQuery = "parents='" . $mappingFolder->getFiles()[0]->getId()
			. "' and mimeType = 'application/vnd.google-apps.spreadsheet'";
		$mappingSpreadsheets = $googleApi->listFiles($service, $spreadsheetFileQuery);

		if (count($mappingSpreadsheets->getFiles()) == 0) {
			print "No files found.\n";
		} else {
			print "Files:\n";
			foreach ($mappingSpreadsheets->getFiles() as $file) {
				$fileId = $file->getId();
				$fileName = $file->getName();
				if ($mappingName && $fileName !== $mappingName) {
					continue;
				}
				printf("Downloading %s (%s)\n", $fileName, $fileId);
				$response = $service->files->export($fileId,
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					array('alt' => 'media'));
				$content = $response->getBody()->getContents();
				$fileDir = sprintf('steps/mappings/%s', $collectionName);
				if (!file_exists($fileDir)) {
					mkdir($fileDir);
				}
				file_put_contents(sprintf('%s/%s.xlsx', $fileDir, $fileName), $content);
			}
		}
	}
}
