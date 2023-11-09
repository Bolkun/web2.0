<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 17.12.2018
 * Time: 10:05
 */

class Db
{
    protected $servername = "localhost";
    protected $username = "serhiybolkun_cmo";
    protected $password = "325500";
    protected $database = "serhiybolkun_cmo";

    public function __construct(){
        // Connect to MySQL
        $link = mysqli_connect($this->servername, $this->username, $this->password);
        if (!$link) {
            die('Could not connect: ' .  mysqli_error($link));
        }

        // Make queo the current database
        $db_selected = mysqli_select_db($link, $this->database);

        if (!$db_selected) {
            // If we couldn't, then it either doesn't exist, or we can't see it.
            $sql = 'CREATE DATABASE ' . $this->database . 'IF NOT EXISTS';

            if (mysqli_query($link, $sql)) {
                echo "Database created successfully \n";
            } else {
                echo 'Error creating database: ' . mysqli_error($link) . "\n";
            }
        }

        // sql to create table
        $sql = "CREATE TABLE IF NOT EXISTS `events` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `titel` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                  `ort` varchar(33) COLLATE utf8_unicode_ci NOT NULL,
                  `datetime` datetime NOT NULL,
                  `link` varchar(65535) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `kategorie` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        if (!mysqli_query($link, $sql)) {
            echo "Error creating table: " . $link->error;
        }

        mysqli_close($link);
    }

    public function createConnection(){
        // Create connection
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }else {
            return $conn;
        }

    }

    public function fillEvents(){
        $conn = $this->createConnection();

        $sql = "SELECT id FROM events";
        $result = $conn->query($sql);

        if ($result->num_rows == 0) {

            $sql_arr[] = "INSERT INTO events (titel, ort, datetime, link, kategorie)
                          VALUES ('Stadtrundfahrt mit der Semperoper', 'Dresden', '2018.12.17 13:00', 'https://veranstaltungen.meinestadt.de/dresden/event-detail/34618570/96745107', 'Stadtrundfahrt')";
            $sql_arr[] = "INSERT INTO events (titel, ort, datetime, link, kategorie)
                  VALUES ('Führung im Neuen Grünen Gewölbe und in der Semperoper', 'Dresden', '2018.12.17 13:30', 'https://veranstaltungen.meinestadt.de/dresden/event-detail/34838257/97154226', 'Semperoper')";
            $sql_arr[] = "INSERT INTO events (titel, ort, datetime, link, kategorie)
                  VALUES ('Die große Stadtrundfahrt', 'Dresden', '2018.12.17 14:00', 'https://veranstaltungen.meinestadt.de/dresden/event-detail/34948948/97402042', 'Stadtrundfahrt')";
            $sql_arr[] = "INSERT INTO events (titel, ort, datetime, link, kategorie)
                  VALUES ('Buntewelt am Altmarkt in Dresden - Spielspaß für die ganze Familie', 'Dresden', '2018.12.17 14:00', 'https://veranstaltungen.meinestadt.de/dresden/event-detail/34930431/97359290', 'Altmarkt')";
            $sql_arr[] = "INSERT INTO events (titel, ort, datetime, link, kategorie)
                  VALUES ('Schlossführung mit Neuem Grünen Gewölbe, Türckische Cammer, Riesensaal und Renaissanceausstellung', 'Dresden', '2018.12.17 13:30', 'https://veranstaltungen.meinestadt.de/dresden/event-detail/34842208/97163328', 'Schlossführung')";
            $sql_arr[] = "INSERT INTO events (titel, ort, datetime, link, kategorie)
                  VALUES ('Dresdner Weihnachts-Circus', 'EnergieVerbund Arena Dresden', '2018.12.19 18:30', 'http://safetickets.net/en/dresdner-weihnachts-circus-ostragehege-cricket-field-dresden-19-dec/1122727', 'Circus')";

            foreach ($sql_arr as $s) {
                if ($conn->query($s) === TRUE) {
                    //echo "New record created successfully <br>";
                } else {
                    echo "Error: " . $s . "<br>" . $conn->error;
                }
            }
        }

        $conn->close();
    }

    public function getKategorien(){
        $conn = $this->createConnection();

        $kategorien = array();

        $sql = "SELECT DISTINCT kategorie FROM events";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $kategorien[] = $row["kategorie"];
            }
        }

        $conn->close();

        return $kategorien;
    }

    public function getDataToKategorie($kategorie){
        $conn = $this->createConnection();

        $sql = "SELECT id, titel, ort, `datetime`, link FROM events WHERE kategorie='$kategorie'";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $id[] = $row["id"];
                $titel[] = $row["titel"];
                $ort[] = $row["ort"];
                $datetime[] = substr($row["datetime"], 0, -3);
                $link[] = $row["link"];
            }
        }

        $conn->close();

        return array("id"=>$id, "titel"=>$titel, "ort"=>$ort, "datetime"=>$datetime, "link"=>$link);
    }

    public function getAllEvents(){
        $conn = $this->createConnection();

        $sql = "SELECT id, titel, ort, `datetime`, link, kategorie FROM events";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $id[] = $row["id"];
                $titel[] = $row["titel"];
                $ort[] = $row["ort"];
                $datetime[] = substr($row["datetime"], 0, -3);
                $link[] = $row["link"];
                $kategorie[] = $row["kategorie"];
            }
        }

        $conn->close();

        return array("id"=>$id, "titel"=>$titel, "ort"=>$ort, "datetime"=>$datetime, "link"=>$link, "kategorie"=>$kategorie);
    }
}