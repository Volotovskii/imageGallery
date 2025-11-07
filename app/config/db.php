<?php
require_once 'config.php';



class DataBase
{

    function get_connection()
    {
        
        return new PDO("mysql:host=" . DB_HOST . "; dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASSWORD);
    }
}
