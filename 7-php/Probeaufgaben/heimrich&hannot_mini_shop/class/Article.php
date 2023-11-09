<?php


class Article
{
    protected $iId;
    protected $sCategory;
    protected $sName;
    protected $fPrice;

    public function __construct($iId, $sCategory, $sName, $fPrice)
    {
        $this->iId = $iId;
        $this->sCategory = $sCategory;
        $this->sName = $sName;
        $this->fPrice = $fPrice;
    }

    public function getCategory()
    {
        return $this->sCategory;
    }

    public function getName()
    {
        return $this->sName;
    }

    public function getPrice()
    {
        return $this->fPrice;
    }

    public function printCategory()
    {
        echo $this->sCategory . ' | ';
    }

    public function printName()
    {
        echo $this->sName . ' | ';
    }

    public function printPrice()
    {
        echo $this->fPrice . '€ | ';
    }

}