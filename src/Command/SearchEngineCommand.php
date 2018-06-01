<?php

namespace CSIROCMS\Command;

use CSIROCMS\Config\Factory\ConfigFactory;
use Monolog\Logger;
use Spatie\DbDumper\Databases\MySql;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchEngineCommand extends Command
{
	const ARG_ENGINE = 'search-engine';
	private $logger;

	private $engines = ['SqlSearch', 'ElasticSearch'];

	public function __construct(Logger $logger)
	{
		$this->logger = $logger;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('search:engine')
			->setDescription('Switch search engine.')
			->addArgument(
				self::ARG_ENGINE,
				InputArgument::REQUIRED,
				sprintf('Search engine to switch to. Valid engines are: %s', join(', ', $this->engines)));
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		require_once getenv('COLLECTIVEACCESS_HOME') . DIRECTORY_SEPARATOR . 'setup.php';
		$appConf = __CA_LOCAL_CONFIG_DIRECTORY__ . '/app.conf';
		$conf = file_get_contents($appConf);
		$engine = $input->getArgument(self::ARG_ENGINE);
		if (!in_array($engine, $this->engines)) {
			throw new InvalidArgumentException(sprintf('Invalid search engine. Valid engines are: %s', join(', ', $this->engines)));
		}
		$conf = preg_replace('/(search_engine_plugin = )(Elastic|Sql)Search/', '$1' . $engine, $conf);
		file_put_contents($appConf, $conf);

		$this->logger->info(sprintf('Switched search engine to `%s`.', $engine));
	}
}
