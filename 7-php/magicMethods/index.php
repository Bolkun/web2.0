<?php

# Taken From https://www.tutorialdocs.com/article/16-php-magic-methods.html

class Person
{
    private $sName;
    private $iAge;
    private $sSex;

    public function __construct($sName = "Serhiy", $iAge = 26, $sSex = "Male")
    {
        $this->sName = $sName;
        $this->sSex = $sSex;
        $this->iAge = $iAge;
    }

    public function say()
    {
        echo "Name：" . $this->sName . ", Sex：" . $this->sSex . ", Age：" . $this->iAge . "<br />";
    }

    function __call($funName, $arguments)
    {
        echo "The function you called：" . $funName . "(parameter：" ;  // Print the method's name that is not existed.
        print_r($arguments); // Print the parameter list of the method that is not existed.
        echo ")does not exist! <br>\n";
    }

    public static function __callStatic($funName, $arguments)
    {
        echo "The static method you called：" . $funName . "(parameter：" ;  // Print the method's name that is not existed.
        print_r($arguments); // Print the parameter list of the method that is not existed.
        echo ")does not exist！<br>\n";
    }

    public function __get($sPropertyName)
    {
        if ($sPropertyName == "iAge") {
            if ($this->iAge > 30) {
                return $this->iAge - 10;
            } else {
                return $this->$sPropertyName;
            }
        } else {
            return $this->$sPropertyName;
        }
    }

    public function __set($sProperty, $value) {
        if ($sProperty=="iAge")
        {
            if ($value > 150 || $value < 0) {
                return;
            }
        }
        $this->$sProperty = $value;
    }

    /**
     * @param $content
     *
     * @return bool
     */
    public function __isset($content) {
        echo "The {$content} property is private，the __isset() method is called automatically.<br>";
        echo  isset($this->$content);
    }

    /**
     * @param $content
     *
     * @return bool
     */
    public function __unset($content) {
        echo "It is called automatically when we use the unset() method outside the class.<br>";
        echo  isset($this->$content);
    }

    /**
     * @return array
     */
    public function __sleep() {
        echo "It is called when the serialize() method is called outside the class.<br>";
        // $this->sName = base64_encode($this->sName);
        return array('sName', 'iAge'); // It must return a value of which the elements are the name of the properties returned.
    }

    public function __wakeup() {
        echo "It is called when the unserialize() method is called outside the class.<br>";
        $this->iAge = 2;
        $this->sSex = 'Male';
        // There is no need to return an array here.
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $s = '';
        $person = array("sName" => $this->sName, "sSex" => $this->sSex, "iAge" => $this->iAge);
        foreach ($person as $k => $v) $s .= "$k = $v; ";
        return  $s . '<br>';
    }

    public function __invoke() {
        echo 'This is an object <br>';
    }

    public static function __set_state($an_array)
    {
        $a = new Person();
        $a->sName = $an_array['sName'];
        return $a;
    }

    public function __clone()
    {
        echo __METHOD__." your are cloning the object.<br>";
    }

    function  __autoload($className) {
        $filePath = "project/class/{$className}.php";
        if (is_readable($filePath)) {
            require($filePath);
        }
    }

    /**
     * @return array
     */
    public function __debugInfo() {
        return [
            'propSquared' => $this->iAge ** 2,
        ];
    }

    public function __destruct()
    {
        echo "<p style='color: red'>Well, my name is ". $this->sName . "</p>";
    }
}

// 1. construct
$Person1 = new Person();
$Person1->say();                // Name：Serhiy, Sex：Male, Age：26
$Person2 = new Person("Jams");
$Person2->say();                // Name：Jams, Sex：Male, Age：26
$Person3 = new Person ("Jack", 25, "Male");
$Person3->say();                // Name：Jack, Sex：Male, Age：25
echo "-----------------------------------------------------------<br />";
// 2. destruct
$Person = new Person("John");
unset($Person);                 // Well, my name is John
                                // At the end all object will be destroyed
                                // Well, my name is Jack
                                // Well, my name is Jams
                                // Well, my name is Serhiy
echo "-----------------------------------------------------------<br />";
// 3. call (Info: When an undefined method is called in a program, the __call() method will be called automatically.)
$Person = new Person();
$Person->run("teacher");
$Person->eat("John", "apple");
$Person->say();
echo "-----------------------------------------------------------<br />";
// 4. callStatic (Info: If the method which is not existed is called within the object, then the __callStatic() method will be called automatically.)
$Person4 = new Person("Maria", 24, "Female");
$Person4::run("teacher");
$Person4::eat("John", "apple");
$Person4->say();
echo "-----------------------------------------------------------<br />";
// 5. get (Info: When you try to access a private property of an external object in a program, the program will throw an exception and end execution. We can use the magic method __get() to solve this problem. It can get the value of the private property of the object outside the object)
$Person5 = new Person("John", 60);   // Instantiate the object with the Person class and assign initial values to the properties with the constructor.
echo "Name：" . $Person5->sName . "<br>";   // When the private property is accessed, the __get() method will be called automatically,so we can get the property value indirectly.
echo "Age：" . $Person5->iAge . "<br>";    // The __get() method is called automatically，and it returns different values according to the object itself.
echo "-----------------------------------------------------------<br />";
// 6. set
$Person6=new Person("Kennedy", 25); //Note that the initial value will be changed by the code below.
$Person6->sName = "Lili";     //The "name" property is assigned successfully. If there is no __set() method, then the program will throw an exception.
$Person6->iAge = 16; //The "age" property is assigned successfully.
$Person6->iAge = 160; //160 is an invalid value, so it fails to be assigned.
$Person6->say();  //print：My name is Lili, I'm 16 years old.
echo "-----------------------------------------------------------<br />";
// 7. isset
$person7 = new Person("John", 25); // Initially assigned.
echo isset($person7->sSex),"<br>";
echo isset($person7->sName),"<br>";
echo isset($person7->iAge),"<br>";
echo "-----------------------------------------------------------<br />";
// 8. unset
$person8 = new Person("Peter", 25); // Initially assigned.
unset($person8->sSex); echo "<br>";
unset($person8->sName); echo "<br>";
unset($person8->iAge); echo "<br>";
echo "-----------------------------------------------------------<br />";
// 9. sleep (Info: The serialize() method will check if there is a magic method __sleep() in the class. If it exists, the method will be called first and then perform the serialize operation.)
$person9 = new Person('Mischa'); // Initially assigned.
echo serialize($person9);   // Generates a storable representation of a value
echo '<br/>';
echo "-----------------------------------------------------------<br />";
// 10. wakeup
$person10 = new Person('Kola'); // Initially assigned.
var_dump(serialize($person10));
var_dump(unserialize(serialize($person10)));
echo "-----------------------------------------------------------<br />";
// 11. toString (Info:  method will be called when using echo method to print an object directly.)
$person11 = new Person('Muhamed'); // Initially assigned.
echo $person11;
echo "-----------------------------------------------------------<br />";
// 12. invoke (Info: When you try to call an object in the way of calling a function, the __ invoke() method will be called automatically.)
$person12 = new Person('Lee'); // Initially assigned.
$person12();
echo "-----------------------------------------------------------<br />";
// 13. set_state (Info: method is called automatically when calling var_export() to export the class code.)
// The parameters of the __set_state() method is an array containing the values of all the properties, with the format of array('property' => value,...)

// We don't define __set_state() method in the following example:
$person13 = new Person('Jackie'); // Initially assigned.
var_export($person13);  //print Person::__set_state(array( 'sName' => 'Jackie', 'iAge' => 26, 'sSex' => 'Male', ))
echo "<br>";
// Example 2
$person14 = new Person('Nick'); // Initially assigned.
$person14->sName = 'Jams';
var_export($person14);
echo "<br>-----------------------------------------------------------<br />";
// 14. clone
$person15 = new Person('Naruto'); // Initially assigned.
$person16 = clone $person15;

var_dump('persion15:');
var_dump($person15);
echo '<br>';
var_dump('persion16:');
var_dump($person16);
echo "<br></br>-----------------------------------------------------------<br />";
// 15. autoload (Info: method can try to load an undefined class.)
echo "-----------------------------------------------------------<br />";
// 16. debugInfo (Info: method will be called when the var_dump() method is executed. If the __debugInfo() method is not defined, then the var_dump() method will print out all the properties in the object.)
var_dump(new Person("Saske", "42"));
echo "-----------------------------------------------------------<br />";

