<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/30/2017
 * Time: 7:17 AM
 */

namespace Application\Tops\sys;


use Application\Tops\sys\IUser;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;

/**
 * Class TConcrete5User
 * @package Application\Tops\sys
 *
 * see https://documentation.concrete5.org/developers/users-groups/reading-data-existing-users
 */
class TConcrete5User implements IUser
{

    /**
     * @var $user User
     */
    private $user;

    /**
     * @var $userInfo UserInfo
     */
    private $userInfo;

    private $memberGroups = false;

    private function getUserInfo() {
        if (empty($this->user)) {
            return false;
        }
        if (empty ($this->userInfo)) {
            $id = $this->user->getUserID();
            $this->userInfo = UserInfo::getByID($id);
        }
        return $this->userInfo;
    }

    private function setUser($user) {
        $this->user = $user;
        $this->userInfo = null;
        $this->memberGroups = false;
    }

    private function getMemberGroups()
    {
        if ($this->memberGroups === false) {
            $this->memberGroups = array();
            $groups = $this->user->getUserGroups();
            foreach($groups as $groupID => $groupName) {
                $group = \Concrete\Core\User\Group\Group::getByID($groupID);
                $this->memberGroups[] = strtolower($group->getGroupName());
            }
        }
        return $this->memberGroups;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function loadById($id)
    {
        $this->setUser(User::getByUserID($id));
    }

    /**
     * @param $userName
     * @return mixed
     */
    public function loadByUserName($userName)
    {
        $this->userInfo = UserInfo::getByName($userName);
        $this->user = User::getByUserID($this->userInfo->getUserID());
    }

    /**
     * @return mixed
     */
    public function loadCurrentUser()
    {
        $this->setUser(new User());
    }

    /**
     * @param $roleName
     * @return bool
     */
    public function isMemberOf($roleName)
    {
        $groups = $this->getMemberGroups();
        $roleName = strtolower($roleName);
        return in_array($roleName,$groups);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->user->getUserID();
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->user->isRegistered();
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isAuthorized($value = '')
    {
        if ($this->user->isSuperUser()) {
            return true;
        }

        $permissions =TopsConfiguration::getValue($value,'permissions','');
        if (trim($permissions) == 'authenticated'  && $this->user->isRegistered()) {
            return true;
        }
        $permissionList = explode(',',$permissions);
        if (!empty($permissionList)) {
            $memberGroups = $this->getMemberGroups();
            foreach ($permissionList as $group) {
                if (in_array($group, $memberGroups)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        // TODO: Implement getFirstName() method.
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        // TODO: Implement getLastName() method.
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        // TODO: Implement getUserName() method.
    }

    /**
     * @param bool $defaultToUsername
     * @return string
     */
    public function getFullName($defaultToUsername = true)
    {
        // TODO: Implement getFullName() method.
    }

    /**
     * @param bool $defaultToUsername
     * @return string
     */
    public function getUserShortName($defaultToUsername = true)
    {
        // TODO: Implement getUserShortName() method.
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        $info = $this->getUserInfo();
        if (empty($info)) {
            return '';
        }
        return $info->getUserEmail();
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->user->isSuperUser();
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return $this->user->isRegistered();
    }

    public function getProfileValue($key)
    {
        // TODO: Implement getProfileValue() method.
    }

    public function setProfileValue($key, $value)
    {
        // TODO: Implement setProfileValue() method.
    }
}