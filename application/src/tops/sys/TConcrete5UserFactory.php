<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/30/2017
 * Time: 7:15 AM
 */

namespace Application\Tops\sys;


use Application\Tops\sys\TConcrete5User;

class TConcrete5UserFactory implements IUserFactory
{

    /**
     * @return IUser
     */
    public function createUser()
    {
        return new TConcrete5User();
    }
}