<?php
/**
 * Created by PhpStorm.
 * User: Serhiy Bolkun
 * Date: 17.11.2018
 * Time: 11:06
 */

class User
{
    private $user_name;
    private $user_passwort;

    public function __construct($name, $passwort) {
        $this->user_name = $name;
        $this->user_passwort = $passwort;
    }
    public function getUserName(){
        return $this->user_name;
    }
    public function setUserName($name){
        $this->user_name = $name;
    }
    public function getUserPasswort(){
        return $this->user_passwort;
    }
    public function setUserPasswort($passwort){
        $this->user_passwort = $passwort;
    }
    public function printUserName(){
        echo "User:" . $this->user_name . "<br>";
    }
    public function printUserPasswort(){
        echo "Passwort:" . md5($this->user_passwort) . "<br>";
    }
}
?>