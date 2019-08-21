<?php
/**
 * Created by PhpStorm.
 * User: serhiy.bolkun
 * Date: 19.02.2019
 * Time: 13:48
 */

namespace Drupal\my_module\Controller;

class MyModuleController{
    public function test(){
        $output = '<p>' . t('Hello World!') . '</p>';
        return array(
            '#markup' => render($output),
        );
    }
}