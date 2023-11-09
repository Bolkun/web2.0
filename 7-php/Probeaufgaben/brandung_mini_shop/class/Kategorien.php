<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 17.11.2018
 * Time: 12:05
 */

class Kategorien
{
    private $kategorien = array("Autos", "Elektronik", "Mode", "Immobilien");

    public function getKategorien(){
        return $this->kategorien;
    }

    public function setKategorien($kategorieName){
        foreach ($this->kategorien as $k){
            if($k === $kategorieName) return 0;
        }
        $this->kategorien[] = $kategorieName;
        return $this->kategorien;
    }

    public function printKategorien(){
        echo "<br>";
        foreach ($this->kategorien as $key){
            echo $key . "<br>";
        }
    }
}

?>