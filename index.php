<?php
declare(strict_types=1);

require 'vendor/autoload.php';
//$config = require 'config.php';

use \Piotr\DbVacations\Debug;
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
    'help' => 'If you use --empty=true database will be empty. Default: false',
    'default' => 'false',
  ]
];

$cliArgs = new CliArgs($config);

$host = $cliArgs->getArg('host');
$name = $cliArgs->getArg('name');
$user = $cliArgs->getArg('user');
$pass = $cliArgs->getArg('pass');
$empty = $cliArgs->getArg('empty');

if ($user === null) {
  echo "\n" . 'Give database user' . "\n";
  echo $cliArgs->getHelp();
  exit();
}

if ($pass === null) {
  echo "\n" . 'Give database password' . "\n";
  echo $cliArgs->getHelp();
  exit();
}

$create = new Create(new DB($host, $user, $name, $pass));

try {
  $create->dropDatabase('Vacations');
  $create->createDatabase('Vacations');
  $create->createTables();
  if (strtolower($empty) !== 'true') {
    $create->reasons();
    $create->admins(24);
    $create->users(1, 6);
    $create->events(6, 12);
  }
} catch (DatabaseException $e) {
  echo $e->getMessage();
}

