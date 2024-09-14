<?php
declare(strict_types=1);

require 'vendor/autoload.php';
$config = require 'config.php';

use \Piotr\DbVacations\Debug;
use \Piotr\DbVacations\DB;
use \Piotr\DbVacations\Create;

$create = new Create(new DB($config['host'], $config['user'], $config['name'], $config['pass']));

$create->dropDatabase('Vacations');
$create->createDatabase('Vacations');
$create->createTables();
$create->reasons();
$create->admins(24);
$create->users(1, 6);
$create->events(6, 12);




