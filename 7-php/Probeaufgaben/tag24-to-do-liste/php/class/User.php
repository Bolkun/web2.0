<?php

class User
{
    private $iId;
    private $sName;
    private $sPassword;

    public function regUser()
    {
        if(!empty($_POST["name"]) && !empty($_POST["password"]) && !empty($_POST["password2"])){
            $sName = $_POST["name"];
            $sPassword = md5($_POST["password"]);
            $sPassword2 = md5($_POST["password2"]);

            if($this->validateRegName($sName)){
                $this->sName = $sName;

                if($this->validateRegPassword($sPassword, $sPassword2)){
                    $this->sPassword = $sPassword;
                } else {
                    echo 'Password is not the same as reentered password! <br>';
                }
            } else {
                echo 'User with this name already exist! <br>';
            }

            if(!empty($this->sName) && !empty($this->sPassword)){
                $db = new Database();
                $db->insertUser($this->sName, $this->sPassword);
                header("Location: index.html");
            }
        }
    }

    public function validateRegName($sName)
    {
        $db = new Database();
        if(!empty($db->selectUserData($sName))) return false;
        else return true;
    }

    public function validateRegPassword($sPassword, $sPassword2)
    {
        if($sPassword !== $sPassword2) return false;
        else return true;
    }

    public function logUser()
    {
        if(!empty($_POST["name"]) && !empty($_POST["password"])){
            $sName = $_POST["name"];
            $sPassword = md5($_POST["password"]);

            if($this->validateLogName($sName)){
                $this->sName = $sName;

                $this->startSession($this->sName);

                if($this->validateLogPassword($sName, $sPassword)){
                    $this->sPassword = $sPassword;
                } else {
                    echo 'Password is not the same as reentered password! <br>';
                }
            } else {
                echo 'User with this name does not exist! <br>';
            }

            if(empty($this->sName) || empty($this->sPassword)){
                header("Location: index.html");
            }
        }
    }

    public function validateLogName($sName)
    {
        $db = new Database();
        if(!empty($db->selectUserData($sName))) return true;
        else return false;
    }

    public function validateLogPassword($sName, $sPassword)
    {
        $db = new Database();
        $aUserData = $db->selectUserData($sName);
        $sCorrectPassword = $aUserData[0]['password'];
        if($sPassword == $sCorrectPassword) return true;
        else return false;
    }

    public function startSession($sName)
    {
        $_SESSION['username'] = $sName;

        if(!isset($_SESSION['username'])) {
            die("Please login first!");
        }
    }

    public function getName()
    {
        echo $_SESSION['username'];
    }

    public function getEncodeName()
    {
        $aName = array('Name' => $this->sName);
        $jsName = json_encode($aName);
        echo $jsName;
    }

    public function getDate()
    {
        echo date("Y-m-d");
    }
}