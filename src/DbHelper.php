<?php

/**
 * Contains helper function to create a DB connection.
 */
class DbHelper
{

    private $pdo;

    /**
     * Constructor
     */
    public function __construct($pdo = NULL)
    {
        $settings = parse_ini_file(__DIR__ . '/../conf/settings.ini');
        $dsn = "mysql:dbname={$settings['db']};host={$settings['host']}";
        $user = $settings['user'];
        $password = $settings['password'];
        $this->pdo = $pdo ?  : new PDO($dsn, $user, $password);
    }

    /**
     * Returns a PDO instance containing connection info.
     */
    public function getConnection()
    {
        return $this->pdo;
    }
}
