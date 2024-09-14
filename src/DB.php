<?php
declare(strict_types=1);

namespace Piotr\DbVacations;

use Piotr\DbVacations\DatabaseException;

class DB
{
  private string $host;
  private string $user;
  private string $name;
  private string $pass;
  private \PDO $conn;
  public function __construct(string $host, string $user, string $name, string $pass)
  {
    $this->host = $host;
    $this->user = $user;
    $this->name = $name;
    $this->pass = $pass;
  }

  public function connect(): \PDO
  {
    $dns = "mysql:host=$this->host;dbname=$this->name";
    try {
      $this->conn = new \PDO($dns, $this->user, $this->pass);
      $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      return $this->conn;
    }
    catch (\PDOException $e) {
      throw new DatabaseException("Incorrect credentials", 500);
    }
  }

  public function close(\PDO $conn): void
  {
    $conn = null;
  }

}