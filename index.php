<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use \Piotr\DbVacations\DB;
use \Piotr\DbVacations\Create;
use \Piotr\DbVacations\DatabaseException;
use CliArgs\CliArgs;

$config = [
  'host' => [
    'help' => 'Hostname of database. Default: localhost',
    'default' => 'localhost',
  ],
  'name' => [
    'help' => 'Name of database. Default: Vacations',
    'default' => 'Vacations',
  ],
  'user' => [
    'help' => 'Database user',
  ],
  'pass' => [
    'help' => 'Database password',
  ],
  'empty' => [
    'help' => 'If you use --empty flag -> database will be empty'
  ]
];

$cliArgs = new CliArgs($config);

$host = $cliArgs->getArg('host');
$name = $cliArgs->getArg('name');
$user = $cliArgs->getArg('user');
$pass = $cliArgs->getArg('pass');
$isEmpty = $cliArgs->isFlagExist('empty');
$isHelp = $cliArgs->isFlagExist('help');

if ($isHelp) {
  echo "\n";
  echo $cliArgs->getHelp() . "\n";
  exit();
}

if ($user === null) {
  echo "\n" . 'Give database user' . "\n";
  echo 'use --help flag for more informations' . "\n\n";
  exit();
}

if ($pass === null) {
  echo "\n" . 'Give database password' . "\n";
  echo 'use --help flag for more informations' . "\n\n";
  exit();
}

$create = new Create(new DB($host, $user, $name, $pass));

try {
  $create->dropDatabase($name);
  $create->createDatabase($name);
  $create->createTables();
  if (!$isEmpty) {
    $create->reasons();
    $create->admins(12);
    $create->users(4, 6);
    $create->events(8, 12);
  }
} catch (DatabaseException $e) {
  echo $e->getMessage();
}
