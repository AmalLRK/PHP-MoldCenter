<?php

class DatabaseFactory
{

  private static $_instance;

  private function __clone()
  { }
  private function __construct()
  { }

  public static function getInstance()
  {
    if (self::$_instance === null)
      self::connect();

    return self::$_instance;
  }

  private static function getConfig()
  {
    return parse_ini_file('config.ini');
  }

  private static function connect()
  {
    $conf = self::getConfig();

    //Mise en place des valeurs par défaut si nécessaire
    $conf['port'] = isset($conf['port']) ? $conf['port'] : '1521';
    $conf['sid'] = isset($conf['sid']) ? $conf['sid'] : 'ORA';

    $tns = '(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)(HOST=' . $conf['server'] . ')(PORT=' . $conf['port'] . ')))(CONNECT_DATA=(SID=' . $conf['sid'] . ')))';

    $opt = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    switch ($conf['type']) {
      case 'mysql':
        $connectionString = 'mysql:host=' . $conf['server'] . ';dbname=' . $conf['database'];
        break;
      case 'oracle':
        $connectionString = 'oci:dbname=' . $tns . ';charset=AL32UTF8';
        // $connectionString = 'oci:host=' . $conf['server'] . ';dbname=' . $conf['database'].';charset=AL32UTF8';
        break;
    }

    try {
      self::$_instance = new PDO($connectionString, $conf['login'], $conf['password'], $opt);
    } catch (PDOException $Exception) {
      echo 'Failed Database connexion. ' . $Exception->getMessage();
    }
  }
}
