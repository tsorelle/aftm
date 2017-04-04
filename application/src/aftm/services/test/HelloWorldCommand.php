<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/20/2017
 * Time: 1:45 PM
 */

namespace Application\Aftm\services\test;


use Application\Tops\services\TServiceCommand;

class HelloWorldCommand  extends TServiceCommand
{

    protected function run()
    {
        $this->addInfoMessage("Hello Mars");
    }
}