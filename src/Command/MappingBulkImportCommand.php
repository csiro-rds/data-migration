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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class MappingBulkImportCommand extends Command
{
    const MAPPING_IMPORT_COMMAND = 'mapping:import';
    const ARG_COLLECTION_NAME = 'collection name';

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
        $this->setName('mapping:bulk_import')
            ->setDescription('Run an import task for multiple mappings.')
            ->addArgument(
                self::ARG_COLLECTION_NAME,
                InputArgument::REQUIRED,
                'Name of the target mapping collection.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $importMappingConfig = $this->configFactory->getConfig('importMappingSettings');
        $caPath = getenv('COLLECTIVEACCESS_HOME');
        $collectionName = $input->getArgument(self::ARG_COLLECTION_NAME);
        $config = $importMappingConfig['parameters'][$collectionName];
        $mappings = $config['mappings'];

        if(!$caPath){
            throw new Exception("Could not find your CollectiveAccess caUtils file! Please set the"
                . " COLLECTIVEACCESS_HOME environment variable to the location of your CollectiveAccess installation.");
        }
        foreach ($mappings as $key=>$mapping) {
            $mappingName = $mapping['name'];
            try {
                $this->logger->info(sprintf('Starting import of mapping files for %s', $mappingName));
                $command = $this->getApplication()->find(self::MAPPING_IMPORT_COMMAND);
                $arguments = array(
                    'command' => self::MAPPING_IMPORT_COMMAND,
                    ImportRunCommand::ARG_COLLECTION_NAME => $collectionName,
                    ImportRunCommand::ARG_MAPPING_NAME => $mappingName,
                );
                if(isset($config['other_mapping_parameters'])) {
                    $arguments += [ImportRunCommand::ARG_OTHER_PARAMS => $config['other_mapping_parameters']];
                }
                $commandInput = new ArrayInput($arguments);
                $command->run($commandInput, $output);
            } catch(Exception $e) {
                $this->logger->critical(sprintf('There was an error with the bulk mapping file import. Error:', $e->getMessage()));
            }
        }
    }
}
