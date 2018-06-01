<?php

namespace CSIROCMS\Command;


use CSIROCMS\Config\Factory\ConfigFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfiguredPentahoJobCommand extends Command
{
	const BASE_COMMAND = 'pentaho:job';
	/**
	 * @var string
	 */
	private $description;
	/**
	 * @var string
	 */
	private $file;
	/**
	 * @var array
	 */
	private $pentahoParameters;
	/**
	 * @var ConfigFactory
	 */
	private $configFactory;
	/**
	 * @var array
	 */
	private $commandParameters;

	public function __construct($name, $description, $file, $commandParameters = array(), $pentahoParameters = array(), ConfigFactory $configFactory)
	{
		$this->description = $description;
		$this->file = $file;
		$this->pentahoParameters = $pentahoParameters;
		$this->configFactory = $configFactory;
		$this->commandParameters = $commandParameters;
		parent::__construct($name);
	}

	public function configure()
	{
		/* Required because description is a private property of the parent class.*/
		$this->setDescription($this->description);
		foreach (array_merge($this->commandParameters, $this->pentahoParameters) as $parameter => $default){
			$this->addOption($parameter, null, InputOption::VALUE_REQUIRED, null, $default);
		}
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$command = $this->getApplication()->find(self::BASE_COMMAND);
		$arguments = array(
			'command' => self::BASE_COMMAND,
			PentahoJobCommand::ARG_FILE => $this->file
		);
		if ($this->pentahoParameters){
			if (!is_array($this->pentahoParameters)){
				throw new \Exception('Parameters should be passed as key value arrays.');
			}
			/* Override the default pentaho options with values that have been passed in by the CLI.*/
			foreach ($input->getOptions() as $parameter => $override){
				if(isset($this->pentahoParameters[$parameter])){
					if (strpos($override, 'config.') === 0){
						$override = $this->_extractConfig($override);
					}
					$this->pentahoParameters[$parameter] = $override;
				}
			}
			$arguments['--' . PentahoJobCommand::OP_PARAMS] = json_encode($this->pentahoParameters);
		}
		foreach ($this->commandParameters as $name => $value){
			$arguments['--' . $name] = $input->getOption($name);
		}

		$commandInput = new ArrayInput($arguments);

		return $command->run($commandInput, $output);
	}

	private function _extractConfig($default)
	{
		$parts = explode('.', $default);
		array_shift($parts);
		$stack = $this->configFactory->getConfig();
		foreach ($parts as $part){
			if (!isset($stack[$part])) {
				throw new \Exception(sprintf('Missing configuration key `%s` in stack `%s`.', $part, $default));
			}
			if (is_array($stack[$part])){
				$stack = $stack[$part];
			} else {
				return $stack[$part];
			}
		}
		return null;
	}
}
