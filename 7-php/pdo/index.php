<?php
/**
 * Created by PhpStorm.
 * User: Serhiy Bolkun
 * Date: 25.12.2018
 * Time: 10:31
 */

class Mypdo {
    protected $db_host = 'localhost';
    protected $db_user = 'root';
    protected $db_password = '';
    protected $db_name = 'pdo';

    public function connection(){
        try {
            $pdo = new PDO("mysql:host=$this->db_host;dbname=$this->db_name", $this->db_user, $this->db_password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        return $pdo;
    }

    public function createDatabase(){
        try {
            $pdo = new PDO("mysql:host=$this->db_host", $this->db_user, $this->db_password);
            // set the PDO error mode to exception
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "CREATE DATABASE mydbpdo";
            // use exec() because no results are returned
            $pdo->exec($sql);
            echo 'Database created successfully<br>';
        } catch(PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }

        $pdo = null;
    }

    public function createTable(){
        $pdo = $this->connection();
        try {
            // sql to create table
            $sql = "CREATE TABLE MyGuests (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            firstname VARCHAR(30) NOT NULL,
            lastname VARCHAR(30) NOT NULL,
            email VARCHAR(50),
            reg_date TIMESTAMP
            )";
            $pdo->exec($sql);
            echo "Table MyGuests created successfully";
        } catch(PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }

        $pdo = null;
    }

    public function insertData(){
        $pdo = $this->connection();
        try {
            $sql = "INSERT INTO MyGuests (firstname, lastname, email) VALUES ('John', 'Doe', 'john@example.com')";
            // use exec() because no results are returned
            $pdo->exec($sql);
        } catch(PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }

        $pdo = null;
    }

    public function insertMultiple(){
        $pdo = $this->connection();
        try {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // begin the transaction
            $pdo->beginTransaction();
            // our SQL statements
            $pdo->exec("INSERT INTO MyGuests (firstname, lastname, email) VALUES ('Yehress', 'Blow', 'yehress@example.com')");
            $pdo->exec("INSERT INTO MyGuests (firstname, lastname, email) VALUES ('Mary', 'Moe', 'mary@example.com')");
            $pdo->exec("INSERT INTO MyGuests (firstname, lastname, email) VALUES ('Julie', 'Dooley', 'julie@example.com')");
            // commit the transaction
            $pdo->commit();
        } catch(PDOException $e) {
            // roll back the transaction if something failed
            $pdo->rollback();
            echo "Error: " . $e->getMessage();
        }

        $pdo = null;
    }

    public function selectData(){
        $pdo = $this->connection();
		/*
			$stmt = $pdo->prepare('SELECT * FROM employees WHERE name = :name');
			$stmt->execute(array('name' => $name));
			foreach ($stmt as $row) {
				// do something with $row
			}
		*/
        try {
            $stmt = $pdo->query("SELECT * FROM MyGuests");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //print_r($rows);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $this->printData($rows);

        $pdo = null;

        return $rows;
    }

    public function deleteRow(){
        $pdo = $this->connection();
        try {
            // sql to delete a record
            $sql = "DELETE FROM MyGuests WHERE id=3";
            // use exec() because no results are returned
            $pdo->exec($sql);
        } catch(PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
        //display result
        $this->selectData();

        $pdo = null;
    }

    public function updateData(){   //Row
        $pdo = $this->connection();
        try {
            $sql = "UPDATE MyGuests SET lastname='Peterson' WHERE id=4";
            // Prepare statement
            $stmt = $pdo->prepare($sql);
            // execute the query
            $stmt->execute();
        } catch(PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
        //display result
        $this->selectData();

        $pdo = null;
    }

    public function printData($array){
        foreach ($array as $num=>$key){
            foreach ($array[$num] as $row=>$secondKey){
                echo $array[$num][$row] . "\t";
            }
            echo "<br>";
        }
    }
}

$pdo = new Mypdo();
//$pdo->createDatabase();
//$pdo->createTable();
//$pdo->insertData();
//$pdo->insertMultiple();
//$pdo->selectData();
//$pdo->deleteRow();
$pdo->updateData();
