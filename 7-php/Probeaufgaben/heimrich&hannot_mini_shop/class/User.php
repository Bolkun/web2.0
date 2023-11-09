<?php


class User
{
    private $sForname;
    private $sSurname;
    private $sStreet;
    private $iPostcode;
    private $sCity;
    private $sEmail;
    private $sBirthsday;

    public function setForname($sForname)
    {
        $this->sForname = $sForname;
    }

    public function setSurname($sSurname)
    {
        $this->sSurname = $sSurname;
    }

    public function setStreet($sStreet)
    {
        $this->sStreet = $sStreet;
    }

    public function setPostcode($iPostcode)
    {
        $this->iPostcode = $iPostcode;
    }

    public function setCity($sCity)
    {
        $this->sCity = $sCity;
    }

    public function setEmail($sEmail)
    {
        $this->sEmail = $sEmail;
    }

    public function setBirthsday($sBirthsday)
    {
        $this->sBirthsday = $sBirthsday;
    }

}