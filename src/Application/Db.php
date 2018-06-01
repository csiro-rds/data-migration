<?php

namespace CSIROCMS\Application;


class Db
{
	private $dsn;
	private $username;
	private $password;

	public function __construct($driver = null, $db = null, $host = null, $username = null, $password = null)
	{
		if($driver && $db && $host){
			$this->dsn = self::generateDsn($driver, $db, $host);
		}
		$this->username = $username;
		$this->password = $password;
	}

	public function setDsn($dsn)
	{
		$this->dsn = $dsn;
	}

	public function setPassword($password)
	{
		$this->password = $password;
	}

	public function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * @param int $errorMode Error mode for the connection.
	 * @return \PDO
	 */
	public function getPDO($errorMode = \PDO::ERRMODE_EXCEPTION)
	{
		$pdo = new \PDO($this->dsn, $this->username, $this->password);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, $errorMode);
		return $pdo;
	}

	public static function generateDsn($driver,$db,$host)
	{
		return sprintf('%s:dbname=%s;host=%s', $driver, $db, $host);
	}
}
