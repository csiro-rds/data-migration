<?php

namespace CSIROCMS\Command;
use CSIROCMS\Config\Factory\ConfigFactory;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportBulkRunCommand extends Command
{
    const IMPORT_RUN_COMMAND = 'import:run';
    const ARG_COLLECTION_NAME = 'collection name';
    const ARG_MAPPING_NAME = 'mapping name';
    const ARG_SOURCE_DB_NAME = 'db source name';
    const ARG_SOURCE_TABLE_NAME = 'source table name';
    const ARG_OTHER_PARAMS = 'other parameters';
    const ARG_WHERE_CLAUSE = 'where clause';
    const OPT_USE_TEST_VALUES = 'use-test-values';
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
        $this->setName('import:bulk_run')
            ->setDescription('Run an import task for multiple mappings.')
            ->addArgument(
                self::ARG_COLLECTION_NAME,
                InputArgument::REQUIRED,
                'Name of the target mapping collection.')
            ->addArgument(
                self::ARG_WHERE_CLAUSE,
                InputArgument::OPTIONAL,
                'Where clause to filter the result set.')
            ->addOption(
                self::OPT_USE_TEST_VALUES,
                't',
                InputOption::VALUE_NONE,
                'Attempt to import test values instead.')
            ->addArgument(
                self::ARG_OTHER_PARAMS,
                InputArgument::OPTIONAL,
                'Passthrough for other parameters.')
			->addOption(
				self::OPT_IGNORE_ERRORS,
				'i',
				InputOption::VALUE_NONE,
				'Ignore any errors when running the import. Allows for the migration to complete.');;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $importMappingConfig = $this->configFactory->getConfig('importMappingSettings');
        $dbConfig = $this->configFactory->getConfig('config');
        $caPath = getenv('COLLECTIVEACCESS_HOME');
        $collectionName = $input->getArgument(self::ARG_COLLECTION_NAME);
        $config = $importMappingConfig['parameters'][$collectionName];
        $mappings = $config['mappings'];
        $connection = $config['connection'];
        $host = $dbConfig['connections'][$connection]['host'];
        $driver =$dbConfig['connections'][$connection]['driver'];
        $username = $dbConfig['connections'][$connection]['username'];
        $password = $dbConfig['connections'][$connection]['password'];
        $sourceDbHost = "$driver://$username:$password@$host";
        $sourceDbName = $dbConfig['connections'][$connection]['database'];

        if(!$caPath){
            throw new Exception("Could not find your CollectiveAccess caUtils file! Please set the"
                . " COLLECTIVEACCESS_HOME environment variable to the location of your CollectiveAccess installation.");
        }
        foreach ($mappings as $key=>$mapping) {
            $mappingName = $mapping['name'];
            $sourceTableName = $input->getOption(self::OPT_USE_TEST_VALUES) ?
				$mappingName . '_test_' . $mapping['table_name'] :
				$mapping['table_name'];

            try {
                $this->logger->info(sprintf('Starting Import for %s', $mappingName));
                $command = $this->getApplication()->find(self::IMPORT_RUN_COMMAND);
                $arguments = array(
                    'command' => self::IMPORT_RUN_COMMAND,
                    ImportRunCommand::ARG_COLLECTION_NAME => $collectionName,
                    ImportRunCommand::ARG_MAPPING_NAME => $mappingName,
                    ImportRunCommand::ARG_SOURCE_DB_HOST => $sourceDbHost,
                    ImportRunCommand::ARG_SOURCE_DB_NAME => $sourceDbName,
                    ImportRunCommand::ARG_SOURCE_TABLE_NAME => $sourceTableName,
					'--'.ImportRunCommand::OPT_IGNORE_ERRORS => $input->getOption(self::OPT_IGNORE_ERRORS)
                );
                if(isset($mapping['where_clause'])) {
                    $arguments += [ImportRunCommand::ARG_WHERE_CLAUSE => $mapping['where_clause']];
                }
                if(isset($config['other_import_params'])) {
                    $arguments += [ImportRunCommand::ARG_OTHER_PARAMS => $config['other_import_params']];
                }
				$commandInput = new ArrayInput($arguments);
				$command->run($commandInput, $output);
            } catch(Exception $e) {
                $this->logger->critical(sprintf('There was an error with the bulk import. Error:', $e->getMessage()));
            }
        }
    }
}
