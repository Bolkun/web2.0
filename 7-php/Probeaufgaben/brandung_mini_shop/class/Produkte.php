<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 17.11.2018
 * Time: 12:05
 */

class Produkte
{
    private $product_list = array();

    public function getProdukten($kategorie){

        $db = new Db();

        $result = mysqli_query($db->connection(),"SELECT name FROM s_articles WHERE parent='$kategorie'");

        if ( mysqli_num_rows($result) > 0 ) {
            // output data of each row
            echo "<br>";
            while($row = $result->fetch_assoc()) {
                $this->product_list[] = $row["name"];
                if($kategorie == 1) echo "Autos: " . $row["name"]. "<br>";
                if($kategorie == 2) echo "Elektronik: " . $row["name"]. "<br>";
                if($kategorie == 3) echo "Mode: " . $row["name"]. "<br>";
                if($kategorie == 4) echo "Immobilien: " . $row["name"]. "<br>";
            }
            mysqli_close($db->connection());
            return true;
        } else {
            mysqli_close($db->connection());
            echo "<br>0 results";
            return false;
        }
    }
}

?>