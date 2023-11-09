<?php

class Liste
{
    private $aData;
    private $sUser;

    public function getListData()
    {
        $this->sUser = $_SESSION['username'];
        $db = new Database();
        $aListeData = $db->selectListData($this->sUser);
        return $aListeData;
    }

    public function printListData()
    {
        $this->aData = $this->getListData();
        if(!empty($this->aData)){
            foreach ($this->aData as $row){
                $iEncodedId = $this->encodeId($row['id']);
                echo "<form name='myform'>";
                    echo "<div class='row'>";
                        echo "<div class='input-group mb-3'>";
                            echo "<div class='input-group-prepend'>";
                                echo "<div class='input-group-text'>";
                                    echo"<input type='checkbox' onclick='deleteTask({$iEncodedId})'>";
                                echo "</div>";
                            echo "</div>";
                            echo "<input type='text' value='{$row['text']}' class='form-control' disabled> <br>";
                            echo "</div>";
                    echo "</div>";
            }
            //echo "<input type='submit' value='Delete'>";
            echo "</form>";
        } else {
            echo "No tasks to do! <br>";
        }
    }

    public function encodeId($iId)
    {
        $aId = array('Id' => $iId);
        $jsId = json_encode($aId);
        return  $jsId;
    }

    public function saveTask()
    {
        $this->sUser = $_SESSION['username'];
        if(!empty($_POST['task']) && !empty($this->sUser)){
            $sTask = $_POST['task'];
            $db = new Database();
            $db->insertTask($this->sUser, $sTask);
        }
    }

    public function deleteTask()
    {
        // var_export($_POST);  //shows POST vars
        if(isset($_POST['Id'])){
            $sIdCheckbox = $_POST['Id'];
            $db = new Database();
            $db->deleteTask($sIdCheckbox);
        }
    }

}