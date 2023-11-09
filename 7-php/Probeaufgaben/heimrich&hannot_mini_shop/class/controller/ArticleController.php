<?php


class ArticleController
{
    private $count;

    public function validateArticle()
    {
        if(!empty($_POST['music_cd1_quantity'])){
            $this->count++;
        }
        if(!empty($_POST['film1_quantity'])){
            $this->count++;
        }
        if (!empty($_POST['serie1_quantity'])){
            $this->count++;
        }
        if(!empty($_POST['furniture1_quantity'])){
            $this->count++;
        }
        if(!empty($_POST['furniture2_quantity'])){
            $this->count++;
        }
        if($this->count >= 1){
            return true;
        } else {
            return false;
        }
    }
}