<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 17.11.2018
 * Time: 13:39
 */

class Db
{
    protected $servername = "localhost";
    protected $username = "root";
    protected $password = "";
    protected $database = "brandung";

    public function connection(){
        $con=mysqli_connect($this->servername, $this->username,$this->password,$this->database);

        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            return 0;
        } else {
            return $con;
        }
    }

}

?>