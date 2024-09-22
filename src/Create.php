<?php
declare(strict_types=1);

namespace Piotr\DbVacations;
use Faker\Factory;
use Piotr\DbVacations\DatabaseException;

class Create
{
  private $faker;
  private \PDO $conn;
  public function __construct(DB $db)
  {
    try {
      $this->conn = $db->connect();
    } catch (DatabaseException $e) {
      echo $e->getMessage() . "\n";
      exit();
    }

    $this->faker = Factory::create('pl_PL');
  }

  public function dropDatabase(string $dbName)
  {
    try {
      $sql = "DROP DATABASE IF EXISTS " . $dbName;
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();
    } catch (\PDOException $e) {
      throw new DatabaseException("Database error", 500);
    }
  }

  public function createDatabase(string $dbName)
  {
    try {
      $sql = "CREATE DATABASE IF NOT EXISTS " . $dbName . "; USE " . $dbName;
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();
    } catch (\PDOException $e) {
      throw new DatabaseException("Database error", 500);
    }
  }

  public function createTables()
  {
    try {
      $sql = "
        CREATE TABLE `Events` (
          `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
          `userId` int NOT NULL,
          `groupId` int NOT NULL,
          `reasonId` int NOT NULL,
          `dateFrom` date NOT NULL,
          `dateTo` date NOT NULL,
          `days` int NOT NULL,
          `status` enum('approved','pending','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
          `notice` text COLLATE utf8mb4_unicode_ci NOT NULL,
          `wantCancel` enum('no','yes') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
          `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        CREATE TABLE `Groups` (
          `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
          `userId` int NOT NULL,
          `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
          `address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
          `postalCode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
          `city` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
          `nip` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
          `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        CREATE TABLE `Reasons` (
          `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
          `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
          `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        CREATE TABLE `Tokens` (
          `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
          `userId` int NOT NULL,
          `token` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
          `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `validAt` datetime NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        CREATE TABLE `UserData` (
          `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
          `userId` int NOT NULL,
          `firstName` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
          `lastName` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
          `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
          `postalCode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
          `city` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
          `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
          `email` text COLLATE utf8mb4_unicode_ci NOT NULL,
          `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        CREATE TABLE `Users` (
          `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
          `groupId` int DEFAULT NULL,
          `login` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
          `pass` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
          `isActive` tinyint(1) NOT NULL DEFAULT '0',
          `isAdmin` tinyint(1) NOT NULL DEFAULT '0',
          `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      //Indexes

      $sql = "
        ALTER TABLE `Events`
        ADD KEY `userId` (`userId`),
        ADD KEY `groupId` (`groupId`),
        ADD KEY `reasonId` (`reasonId`)
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        ALTER TABLE `Groups`
        ADD UNIQUE KEY `user_id` (`userId`)
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        ALTER TABLE `Tokens`
        ADD KEY `users_tokens` (`userId`)
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        ALTER TABLE `UserData`
        ADD UNIQUE KEY `user_id` (`userId`) USING BTREE;
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        ALTER TABLE `Users`
        ADD KEY `group_id` (`groupId`) USING BTREE;
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      //Constraints

      $sql = "
        ALTER TABLE `Events`
        ADD CONSTRAINT `Events_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`),
        ADD CONSTRAINT `Events_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `Groups` (`id`),
        ADD CONSTRAINT `Events_ibfk_3` FOREIGN KEY (`reasonId`) REFERENCES `Reasons` (`id`)
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        ALTER TABLE `Groups`
        ADD CONSTRAINT `Groups_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`)
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        ALTER TABLE `Tokens`
        ADD CONSTRAINT `users_tokens` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "
        ALTER TABLE `UserData`
        ADD CONSTRAINT `UserData_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`)
      ";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

    } catch (\PDOException $e) {
      throw new DatabaseException("Database error", 500);
    }
  }

  public function reasons(): void
  {
    $reasons = [
      'Bez powodu',
      'Choroba',
      'Urlop wypoczynkowy',
      'Nagłe zdarzenie',
      'Nie interesuj się',
    ];
    $sql = "INSERT INTO Reasons (name) VALUES (:name)";
    $stmt = $this->conn->prepare($sql);
    try {
      foreach ($reasons as $reason) {
        $stmt->bindValue(':name', $reason, \PDO::PARAM_STR);
        $stmt->execute();
      }
    } catch (\PDOException $e) {
      throw new DatabaseException("Database error", 500);
    }
  }

  public function admins(int $count): void
  {
    for ($i = 0; $i < $count; $i++) {
      try {
        $this->conn->beginTransaction();
        $user = $this->getUser();
        if ($i === 0) {
          echo "\n" . 'Admin\'s credentials:' . "\n";
          echo 'login: ' . $user['login'] . ' - pass: ' . $user['login'] . "\n\n";
        }
        $sql = "
          INSERT INTO Users (login, pass, isActive, isAdmin)
          VALUES (:login, :pass, :isActive, :isAdmin)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':login', $user['login'], \PDO::PARAM_STR);
        $stmt->bindValue(':pass', $user['pass'], \PDO::PARAM_STR);
        $stmt->bindValue(':isActive', $user['isActive'], \PDO::PARAM_BOOL);
        $stmt->bindValue(':isAdmin', $user['isAdmin'], \PDO::PARAM_BOOL);
        $stmt->execute();
        $userId = $this->conn->lastInsertId();

        $userData = $this->getUserData();
        $sql = "
          INSERT INTO UserData (userId, firstName, lastName, address, postalCode, city, phone, email) 
          VALUES (:userId, :firstName, :lastName, :address, :postalCode, :city, :phone, :email)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':firstName', $userData['firstName'], \PDO::PARAM_STR);
        $stmt->bindValue(':lastName', $userData['lastName'], \PDO::PARAM_STR);
        $stmt->bindValue(':address', $userData['address'], \PDO::PARAM_STR);
        $stmt->bindValue(':postalCode', $userData['postalCode'], \PDO::PARAM_STR);
        $stmt->bindValue(':city', $userData['city'], \PDO::PARAM_STR);
        $stmt->bindValue(':phone', $userData['phone'], \PDO::PARAM_STR);
        $stmt->bindValue(':email', $userData['email'], \PDO::PARAM_STR);
        $stmt->execute();

        $group = $this->getGroup();
        $sql = "
            INSERT INTO `Groups` (userId, name, address, postalCode, city, nip) 
            VALUES (:userId, :name, :address, :postalCode, :city, :nip)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':name', $group['name'], \PDO::PARAM_STR);
        $stmt->bindValue(':address', $group['address'], \PDO::PARAM_STR);
        $stmt->bindValue(':postalCode', $group['postalCode'], \PDO::PARAM_STR);
        $stmt->bindValue(':city', $group['city'], \PDO::PARAM_STR);
        $stmt->bindValue(':nip', $group['nip'], \PDO::PARAM_STR);
        $stmt->execute();
        $groupId = $this->conn->lastInsertId();
        $sql = "
          UPDATE Users SET groupId = :groupId WHERE id = :id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':groupId', $groupId, \PDO::PARAM_INT);
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $this->conn->commit();
      } catch (\PDOException $e) {
        $this->conn->rollBack();
        throw new DatabaseException("Database error", 500);
      }
    }
  }

  public function users(int $from = 0, int $to = 10)
  {
    $sql = "SELECT id FROM `Groups`";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    echo "\n" . 'User\'s credentials:' . "\n";
    foreach ($rows ?? [] as $key => $row) {
      $c = $this->faker->numberBetween($from, $to);
      for ($i = 0; $i < $c; $i++) {
        try {
          $this->conn->beginTransaction();
          $user = $this->getUser(true, false);
          if ($key === 0) {
            echo 'login: ' . $user['login'] . ' - pass: ' . $user['login'] . "\n";
          }

          $sql = "
            INSERT INTO Users (groupId, login, pass, isActive, isAdmin)
            VALUES (:groupId, :login, :pass, :isActive, :isAdmin)
          ";
          $stmt = $this->conn->prepare($sql);
          $stmt->bindValue(':groupId', $row['id'], \PDO::PARAM_INT);
          $stmt->bindValue(':login', $user['login'], \PDO::PARAM_STR);
          $stmt->bindValue(':pass', $user['pass'], \PDO::PARAM_STR);
          $stmt->bindValue(':isActive', $user['isActive'], \PDO::PARAM_BOOL);
          $stmt->bindValue(':isAdmin', $user['isAdmin'], \PDO::PARAM_BOOL);
          $stmt->execute();
          $userId = $this->conn->lastInsertId();

          $userData = $this->getUserData();
          $sql = "
            INSERT INTO UserData (userId, firstName, lastName, address, postalCode, city, phone, email) 
            VALUES (:userId, :firstName, :lastName, :address, :postalCode, :city, :phone, :email)
          ";
          $stmt = $this->conn->prepare($sql);
          $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
          $stmt->bindValue(':firstName', $userData['firstName'], \PDO::PARAM_STR);
          $stmt->bindValue(':lastName', $userData['lastName'], \PDO::PARAM_STR);
          $stmt->bindValue(':address', $userData['address'], \PDO::PARAM_STR);
          $stmt->bindValue(':postalCode', $userData['postalCode'], \PDO::PARAM_STR);
          $stmt->bindValue(':city', $userData['city'], \PDO::PARAM_STR);
          $stmt->bindValue(':phone', $userData['phone'], \PDO::PARAM_STR);
          $stmt->bindValue(':email', $userData['email'], \PDO::PARAM_STR);
          $stmt->execute();
          $this->conn->commit();
        } catch (\PDOException $e) {
          $this->conn->rollBack();
          throw new DatabaseException("Database error", 500);
        }
      }
    }
  }

  public function events(int $from = 0, int $to = 10)
  {
    $reasonsId = [];
    $sql = "SELECT id FROM Reasons";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($rows ?? [] as $row) {
      $reasonsId[] = $row['id'];
    }

    $sql = "SELECT id, groupId FROM Users WHERE isActive = true AND isAdmin = false";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($rows ?? [] as $row) {
      $c = $this->faker->numberBetween($from, $to);
      for ($i = 0; $i < $c; $i++) {
        try {
          $event = $this->getEvent();
          $sql = "
            INSERT INTO Events (userId, groupId, reasonId, dateFrom, dateTo, days, status, notice, wantCancel) 
            Values (:userId, :groupId, :reasonId, :dateFrom, :dateTo, :days, :status, :notice, :wantCancel)
          ";
          $stmt = $this->conn->prepare($sql);
          $stmt->bindValue(':userId', $row['id'], \PDO::PARAM_INT);
          $stmt->bindValue(':groupId', $row['groupId'], \PDO::PARAM_INT);
          $stmt->bindValue(':reasonId', $this->faker->randomElement($reasonsId), \PDO::PARAM_INT);
          $stmt->bindValue(':dateFrom', $event['dateFrom'], \PDO::PARAM_STR);
          $stmt->bindValue(':dateTo', $event['dateTo'], \PDO::PARAM_STR);
          $stmt->bindValue(':days', $event['days'], \PDO::PARAM_INT);
          $stmt->bindValue(':status', $event['status'], \PDO::PARAM_STR);
          $stmt->bindValue(':notice', $event['notice'], \PDO::PARAM_STR);
          $stmt->bindValue(':wantCancel', $event['wantCancel'], \PDO::PARAM_STR);
          $stmt->execute();
        } catch (\PDOException $e) {
          throw new DatabaseException("Database error", 500);
        }
      }
    }
  }

  private function getUser(bool $isActive = true, $isAdmin = true): array
  {
    $login = '';
    while (strlen($login) < 5) {
      $login = $this->faker->word() . $this->faker->numberBetween(100, 999);
    }
    $pass = md5($login);
    return [
      'login' => $login,
      'pass' => $pass,
      'isActive' => $isActive,
      'isAdmin' => $isAdmin,
    ];
  }

  private function getUserData(): array
  {
    return [
      'firstName' => substr($this->faker->firstName(), 0, 30),
      'lastName' => substr($this->faker->lastName(), 0, 30),
      'address' => substr($this->faker->streetAddress(), 0, 30),
      'postalCode' => $this->faker->postcode(),
      'city' => substr($this->faker->city(), 0, 30),
      'phone' => $this->faker->phoneNumber(),
      'email' => $this->faker->email(),
    ];
  }

  private function getGroup(): array
  {
    return [
      'name' => substr($this->faker->company(), 0, 30),
      'address' => substr($this->faker->streetAddress(), 0, 30),
      'postalCode' => $this->faker->postcode(),
      'city' => substr($this->faker->city(), 0, 30),
      'nip' => $this->faker->taxpayerIdentificationNumber(),
    ];
  }

  private function getEvent()
  {
    $today = Date("Y-m-d");
    $date = $this->faker->dateTimeBetween('-90 days', '+90 days')->format("Y-m-d");
    $status = $this->faker->randomElement(['cancelled', 'pending', 'approved']);
    $wantCancel = 'no';
    if ($status === 'approved' && $date > $today) {
      $wantCancel = $this->faker->randomElement(['no', 'yes']);
    }
    return [
      'dateFrom' => $date,
      'dateTo' => $date,
      'days' => 1,
      'status' => $status,
      'notice' => $this->faker->sentence(),
      'wantCancel' => $wantCancel,
    ];
  }

}