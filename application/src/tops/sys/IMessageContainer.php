<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 7/7/2015
 * Time: 6:37 AM
 */

namespace Application\Tops\sys;


interface IMessageContainer {
    public function AddMessage($messageType,$text);
    public function AddInfoMessage($text);
    public function AddWarningMessage($text);
    public function AddErrorMessage($text);
    public function GetResult();
}