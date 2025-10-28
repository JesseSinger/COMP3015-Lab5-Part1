<?php

namespace src\Repositories;

use PDO;
use PDOException;

class Repository
{
	protected PDO $pdo;
	private string $hostname;
	private string $username;
	private string $databaseName;
	private string $databasePassword;
	private string $charset;
	private string $env;

	public function __construct()
	{
		// @todo use https://github.com/vlucas/phpdotenv so we don't have any hard coded credentials here
		$this->env = 'test';
		$this->hostname = 'localhost';
		$this->username = 'root';
		$this->databaseName = $this->env === 'test' ? 'posts_web_app_test' : 'posts_web_app';
		$this->databasePassword = '';
		$this->charset = 'utf8mb4';

		$dsn = "mysql:host=$this->hostname;dbname=$this->databaseName;charset=$this->charset";
		// For options info, see: https://www.php.net/manual/en/pdo.setattribute.php
		$options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
		try {
			$this->pdo = new PDO($dsn, $this->username, $this->databasePassword, $options);
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage(), (int)$e->getCode());
		}
	}

	public function db()
	{
		return $this->pdo;
	}
}
