<?php

class User {
    public $email;
    public $password;
    public $lastTime;

    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
        $this->lastTime = time();
    }

    // calls a method before serialize
    public function __sleep()
    {
        // Don't save password in a File
        return ['email', 'lastTime'];
    }

    // calls a method before unserialize
    public function __wakeup()
    {
        $this->lastTime = time();
    }
}
// serialize
$user = new User('abc@mail.de', 123);
print_r($user);
$str = serialize($user);
echo '<br>' . $str . '<br>';

$fp = fopen($user->email, 'w+');
fwrite($fp, $str);
fclose($fp);
// unserialize
$str2 = file_get_contents($user->email);
sleep(2);
$u = unserialize($str2);
print_r($u);