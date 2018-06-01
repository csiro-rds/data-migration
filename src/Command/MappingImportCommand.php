<?php

namespace CSIROCMS\Command;

use CSIROCMS\Config\Factory\ConfigFactory;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CSIROCMS\Application\CommandHelpers;

class MappingImportCommand extends Command
{
    const ARG_COLLECTION_NAME = 'collection name';
    const ARG_MAPPING_NAME = 'mapping name';
    const ARG_OTHER_PARAMS = 'other parameters';

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
        $this->setName('mapping:import')
            ->setDescription('import mapping files into Collective Access')
            ->addArgument(
                self::ARG_COLLECTION_NAME,
                InputArgument::REQUIRED,
                'Name of the target mapping collection.')
            ->addArgument(
                self::ARG_MAPPING_NAME,
                InputArgument::REQUIRED,
                'Name of the mapping to import.')
            ->addArgument(
                self::ARG_OTHER_PARAMS,
                InputArgument::OPTIONAL,
                'Passthrough for other parameters.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $collectionName = $input->getArgument(self::ARG_COLLECTION_NAME);
        $mappingName = $input->getArgument(self::ARG_MAPPING_NAME);
        $otherParams = '' . $input->getArgument(self::ARG_OTHER_PARAMS);
        $dataMigrationBaseDir = realpath(__DIR__ . '/../..');
        $caPath = getenv('COLLECTIVEACCESS_HOME');

        if(!$caPath){
            throw new Exception("Could not find your CollectiveAccess caUtils file! Please set the"
                . " COLLECTIVEACCESS_HOME environment variable to the location of your CollectiveAccess installation.");
        }

        try {
            $loadMappingExecString = sprintf("php %s/support/bin/caUtils load-import-mapping --file '%s/steps/mappings/%s/%s.xlsx' %s",
                $caPath, $dataMigrationBaseDir, $collectionName, $mappingName, $otherParams);
            CommandHelpers::liveExecuteCommand($loadMappingExecString);
        } catch(Exception $e) {
            $this->logger->critical(sprintf('Could not run caUtils command. Error:', $e->getMessage()));
        }
        $export = $this->getApplication()->get(MappingExportCommand::COMMAND);
        $export->run(new ArrayInput([MappingExportCommand::ARG_NAME => [$collectionName . $mappingName]]), $output);
    }
}
