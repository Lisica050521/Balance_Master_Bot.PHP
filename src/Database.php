<?php

namespace App;

class Database
{
    private $connection;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $host = '127.0.0.1';
        $port = '5432';
        $dbname = 'balancemasterbot';
        $user = 'laravel';
        $password = 'secret';

        $this->connection = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

        if (!$this->connection) {
            throw new \Exception('Could not connect to the database');
        }
    }

    public function query($sql)
    {
        return pg_query($this->connection, $sql);
    }

    public function getResult($sql)
    {
        $result = $this->query($sql);
        return pg_fetch_all($result);
    }

    public function close()
    {
        pg_close($this->connection);
    }
}
