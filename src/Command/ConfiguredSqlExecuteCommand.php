<?php

namespace CSIROCMS\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfiguredSqlExecuteCommand extends Command
{
	const BASE_COMMAND = 'sql:execute';
	/**
	 * @var string
	 */
	private $description;
	/**
	 * @var string
	 */
	private $connection;
	/**
	 * @var string
	 */
	private $file;

	public function __construct($name, $description, $connection, $file)
	{
		$this->description = $description;
		$this->connection = $connection;
		$this->file = $file;
		parent::__construct($name);
	}

	public function configure()
	{
		/* Required because description is a private property of the parent class.*/
		$this->setDescription($this->description);
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$command = $this->getApplication()->find(self::BASE_COMMAND);
		$arguments = array(
			'command' => self::BASE_COMMAND,
			SqlCommand::ARG_CONNECTION => $this->connection,
			SqlCommand::ARG_FILE => $this->file
		);
		$commandInput = new ArrayInput($arguments);
		return $command->run($commandInput, $output);
	}
}
