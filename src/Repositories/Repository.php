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
		$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

		// @todo use https://github.com/vlucas/phpdotenv so we don't have any hard coded credentials here
		$this->env = $_ENV['APP_ENV'] ?? '';
		$this->hostname = $_ENV['DB_HOST'] ?? 'localhost';
		$this->username = $_ENV['DB_USER'] ?? 'root';
		$this->databaseName = $this->env === 'test' ? 'posts_web_app_test' : 'posts_web_app';
		$this->databasePassword = $_ENV['DB_PASSWORD'] ?? '';
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
