<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 4/24/2015
 * Time: 4:15 AM
 */

namespace Application\Tops\sys;



use Application\Tops\sys\IUser;

interface IUserFactory {
    /**
     * @return IUser
     */
    public function createUser();
}