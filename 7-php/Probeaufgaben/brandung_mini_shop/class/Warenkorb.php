<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 17.11.2018
 * Time: 12:06
 */

class Warenkorb
{
    private $warenkorb = array();

    public function setWarenkorb($id)
    {
        $db = new Db();
        $sql = "UPDATE s_articles SET active='0' WHERE id='$id'";

        if ($db->connection()->query($sql) === TRUE) {
            echo "Artikel würde in den Warenkorb hinzugefügt" . "<br>";
        } else {
            echo "Etwas lief schief ...". "<br>" . $db->connection()->error;
        }

        $db->connection()->close();

        return $this->warenkorb[] = $id;
    }

    public function printWarenkorbIds(){
        foreach ($this->warenkorb as $articel){
            echo $articel . "<br>";
        }
    }

    public function deleteWarenkorbId($id)
    {
        $db = new Db();
        $sql = "UPDATE s_articles SET active='1' WHERE id='$id'";

        if ($db->connection()->query($sql) === TRUE) {
            echo "Artikel würde aus der Warenkorb entfernt" . "<br>";
        } else {
            echo "Etwas lief schief ...". "<br>" . $db->connection()->error;
        }

        $db->connection()->close();

        $key = array_search($id, $this->warenkorb);
        unset($this->warenkorb[$key]);

    }

}
?>