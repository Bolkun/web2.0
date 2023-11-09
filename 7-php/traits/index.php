<?php

trait Id
{
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

}

trait Name
{
    protected $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}

class User
{
    use Id, Name;
}

class Subscriber
{
    use Id, Name;
}

class Article
{
    use Id;
}

$user = new User();
$user->setId(12);
$user->setName('Peter');
echo $user->getId() . '<br />';
echo $user->getName();
