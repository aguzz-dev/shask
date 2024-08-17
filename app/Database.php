<?php

namespace App;
use App\Traits\FindTrait;

class Database
{
    use FindTrait;
    public $dbConnection;
    public function __construct()
    {
        $host   = env("DB_HOST");
        $user   = env("DB_USERNAME");
        $pass   = env("DB_PASSWORD");
        $db     = env("DB_DATABASE");
        $port   = env("DB_PORT");
        $this->dbConnection = new \mysqli(
            $host,
            $user,
            $pass,
            $db,
            $port
        );
        mysqli_set_charset($this->dbConnection, 'utf8');
    }
    public function query($sql)
    {
        $res = mysqli_query($this->dbConnection, $sql);
        if (!$res) {
            throw new \Exception("Error en la consulta: " . mysqli_error($this->dbConnection));
        }
        return $res;
    }
    public function checkConnection()
    {
        if ($this->dbConnection->connect_error) {
            die("Error de conexión: " . $this->dbConnection->connect_error);
        } else {
            echo "Conexión exitosa a la BD.";
        }
    }
}
