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

class SqlCommand extends Command
{
	const ARG_CONNECTION = 'connection';
	const ARG_FILE = 'file';
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

	protected function configure()
	{
		$this
			->setName('sql:execute')
			->setDescription('Execute an sql file on a connection.')
			->addArgument(
				self::ARG_CONNECTION,
				InputArgument::REQUIRED,
				'Name of connection to use.')
			->addArgument(
				self::ARG_FILE,
				InputArgument::REQUIRED,
				'Path to file(s) to execute.');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$connections = $this->configFactory->getConfig()['connections'];
		$connection = $input->getArgument(self::ARG_CONNECTION);
		if (!isset($connections[$connection])){
			throw new \Exception(sprintf('Connection `%s` does not exist. Valid connections are: %s.', $connection, join(',', array_keys($connections))));
		}
		$dbConfig = $connections[$connection];
		$db = new Db($dbConfig['driver'], $dbConfig['database'], $dbConfig['host'], $dbConfig['username'], $dbConfig['password']);
		$pdo = $db->getPDO();
		$fileName = $input->getArgument(self::ARG_FILE);
		if (!file_exists($fileName)){
			throw new Exception(sprintf('Input file could not be found at %s.', $fileName));
		}
		$sql = file_get_contents(getcwd() . DIRECTORY_SEPARATOR . $fileName);
		$pdo->setAttribute($pdo::ATTR_AUTOCOMMIT, false);
		$pdo->beginTransaction();
		$this->logger->debug($pdo->getAttribute($pdo::ATTR_ERRMODE));
		//Only pick up colons denoting the end of a command.
		$statements = preg_split('/;\r?\n/', $sql);
		foreach ($statements as $statement){
			if(trim($statement)){
				$this->logger->debug(sprintf('Executing sql: %s', $statement));
				$pdo->prepare($statement)->execute();
				$stm = $pdo->query('SHOW WARNINGS');
				if ($stm->rowCount()){
					$this->logger->warn(sprintf('Obtained the following warnings %s: ', json_encode($stm->fetchAll())));
				}
			}
		}
		$pdo->commit();
		$this->logger->info(sprintf('Executed `%s` on connection `%s`.', $fileName, $connection));
	}
}
