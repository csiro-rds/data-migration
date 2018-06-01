<?php

namespace CSIROCMS\Command;
use CSIROCMS\Application\GoogleApi;
use CSIROCMS\Config\Factory\ConfigFactory;
use CSIROCMS\Config\Loader\YamlFileLoader;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ImportBulkAllCommand extends Command
{
    const MAPPING_DOWNLOAD_COMMAND = 'mapping:download';
    const MAPPING_BULK_IMPORT_COMMAND = 'mapping:bulk_import';
    const IMPORT_BULK_RUN_COMMAND = 'import:bulk_run';
    const ARG_COLLECTION_NAME = 'collection name';
    const OPT_RUN_OFFLINE = 'run-offline';
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

    public function configure() {
        $this->setName('import:bulk_all')
            ->setDescription('Download, import and run all mappings for a collection.')
            ->addArgument(
                self::ARG_COLLECTION_NAME,
                InputArgument::REQUIRED,
                'Name of the target mapping collection.')
            ->addOption(
                self::OPT_RUN_OFFLINE,
                'o',
                InputOption::VALUE_NONE,
                'Run this command without downloading from Google Drive')
            ->addOption(
                self::OPT_IGNORE_ERRORS,
                'i',
                InputOption::VALUE_NONE,
                'Ignore any errors when running the import. Allows for the migration to complete.');;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $caPath = getenv('COLLECTIVEACCESS_HOME');
        $collectionName = $input->getArgument(self::ARG_COLLECTION_NAME);

        if(!$caPath){
            throw new Exception("Could not find your CollectiveAccess caUtils file! Please set the"
                . " COLLECTIVEACCESS_HOME environment variable to the location of your CollectiveAccess installation.");
        }

        $this->logger->info(sprintf('Starting mapping/import steps for all mapping files in '
            . ' collection %s', $collectionName));

        if(!$input->getOption(self::OPT_RUN_OFFLINE)) {
            $downloadCommand = $this->getApplication()->find(self::MAPPING_DOWNLOAD_COMMAND);
            $downloadArguments = array(
                'command' => self::MAPPING_DOWNLOAD_COMMAND,
                MappingDownloadCommand::ARG_COLLECTION_NAME => $collectionName
            );
            $downloadCommandInput = new ArrayInput($downloadArguments);
            $downloadCommand->run($downloadCommandInput, $output);
        }
        $mappingBulkImportCommand = $this->getApplication()->find(self::MAPPING_BULK_IMPORT_COMMAND);
        $bulkImportArguments = array(
            'command' => self::MAPPING_BULK_IMPORT_COMMAND,
            MappingImportCommand::ARG_COLLECTION_NAME => $collectionName
        );
        $importCommandInput = new ArrayInput($bulkImportArguments);
        $mappingBulkImportCommand->run($importCommandInput, $output);

        $importBulkRunCommand = $this->getApplication()->find(self::IMPORT_BULK_RUN_COMMAND);
        $bulkRunArguments = array(
            'command' => self::IMPORT_BULK_RUN_COMMAND,
            ImportRunCommand::ARG_COLLECTION_NAME => $collectionName,
			'--' . ImportBulkRunCommand::OPT_IGNORE_ERRORS => $input->getOption(ImportBulkAllCommand::OPT_IGNORE_ERRORS),
        );
        $runCommandInput = new ArrayInput($bulkRunArguments);
        $importBulkRunCommand->run($runCommandInput, $output);
    }
}
