<?php

class Database
{
    protected $db_host = 'localhost';
    protected $db_user = 'root';
    protected $db_password = '';
    protected $db_name = 'to-do-list';

    public function connection()
    {
        try {
            $pdo = new PDO("mysql:host=$this->db_host;dbname=$this->db_name", $this->db_user, $this->db_password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        return $pdo;
    }

    public function insertUser($sName, $sPassword)
    {
        $pdo = $this->connection();
        try {
            $stmt = $pdo->prepare("INSERT INTO user (name, password) VALUES (?, ?)");
            $stmt->execute([$sName, $sPassword]);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $pdo = null;
    }

    public function insertTask($sName, $sTask)
    {
        $pdo = $this->connection();
        try {
            $sDate = date("Y-m-d");
            $stmt = $pdo->prepare("INSERT INTO liste (`date`, text, `name`) VALUES (?, ?, ?)");
            $stmt->execute([$sDate, $sTask, $sName]);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $pdo = null;
    }

    public function selectUserData($sName)
    {
        $pdo = $this->connection();
        try {
            $stmt = $pdo->query("SELECT * FROM `user` WHERE name = '$sName'");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $pdo = null;

        return $rows;
    }

    public function selectListData($sName)
    {
        $pdo = $this->connection();
        try {
            $stmt = $pdo->query("SELECT * FROM liste WHERE name = '$sName'");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $pdo = null;

        return $rows;
    }

    public function deleteTask($sTask)
    {
        $pdo = $this->connection();
        try {
            $sql = "DELETE FROM liste WHERE id = '$sTask'";
            $pdo->exec($sql);
        } catch(PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
        $pdo = null;
    }

}