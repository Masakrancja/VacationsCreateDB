<?php
declare(strict_types=1);

namespace Piotr\DbVacations;

class Debug
{
  public static function dump($any): void
  {
    print_r($any);
  }
}