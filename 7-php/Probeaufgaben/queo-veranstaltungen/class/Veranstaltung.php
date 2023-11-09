<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 17.12.2018
 * Time: 08:22
 */

class Veranstaltung
{
    private $titel;
    private $ort;
    private $dateTime;
    private $link;
    private $kategorie;

    public function __construct($titel, $ort, $dateTime, $link, $kategorie){
        $this->titel = $titel;
        $this->ort = $ort;
        $this->dateTime = $dateTime;
        $this->link = $link;
        $this->kategorie = $kategorie;
    }

    public function getTitel(){
        return $this->titel;
    }

    public function setTitel($new_titel){
        $this->titel = $new_titel;
    }

    public function getOrt(){
        return $this->ort;
    }

    public function setOrt($new_ort){
        $this->ort = $new_ort;
    }

    public function getDateTime(){
        return $this->dateTime;
    }

    public function setDateTime($new_dateTime){
        $this->dateTime = $new_dateTime;
    }

    public function getLink(){
        return $this->link;
    }

    public function setLink($new_link){
        $this->link = $new_link;
    }

    public function getKategorien(){
        return $this->kategorie;
    }

    public function setKategorie($new_kategorie){
        $this->kategorie = $new_kategorie;
    }
}