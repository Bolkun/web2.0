<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 17.11.2018
 * Time: 12:06
 */

class Bestellungen
{
    private $quitungNr;

    public function setQuitungNr() {
        $this->quitungNr = uniqid();
    }

    public function printQuitungNr(){
        echo "QuitingsNr.: " . $this->quitungNr . "<br>";
    }

    public function sendMail($user){
        $message = "Sehr geehrte Herr " . $user . "Ihre Bestellung von Brandung Shop ...";
        try {
            @mail('serhij16@live.de', 'Brandung: BestellungsNr.: $this->quitungNr', $message, "From: localhost@mail.ru \r\n");
            echo "Email gesendet!" . "<br>";
        } catch (Exception $e){
           echo 'Error email würde nicht gesendet: ',  $e->getMessage(), "\n";
       }
    }
}

?>