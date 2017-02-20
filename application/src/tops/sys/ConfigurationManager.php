<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 1/25/2017
 * Time: 6:27 AM
 */

namespace Application\Tops\sys;


/**
 * Class AftmConfiguration
 * @package Application\Aftm
 *
 * Handles access to configuration file config.ini, located in same directory as this class file.
 * For ini file documentation see http://php.net/manual/en/function.parse-ini-file.php
 *
 */
class ConfigurationManager
{
    public function __construct($iniPath)
    {
        $this->ini = parse_ini_file($iniPath,true);
    }

    /**
     * @var array
     */
    private $ini;

    /**
     * Retrieve value by key and section
     *
     * @param $key
     * @param $sectionKey
     * @param bool $defaultValue - returned if value does not exist
     * @return bool|mixed
     */
    public function getIniValue($key, $sectionKey, $defaultValue = false)
    {
        $section = $this->getIniSection($sectionKey);
        if (is_array($section) && array_key_exists($key,$section)) {
            return $section[$key];
        }
        return $defaultValue;
    }

    /**
     * Get array of values by section key
     *
     * @param $sectionKey
     * @return bool|mixed
     */
    public function getIniSection($sectionKey)
    {
        if (array_key_exists($sectionKey,$this->ini)) {
            return $this->ini[$sectionKey];
        }
        return false;
    }


    public function getIniEmailValues($key, $sectionKey) {
        $result = array();
        $keys = $this->getIniValue($key,$sectionKey);
        if ($keys !== false) {
            $keys = explode(',', $keys);
            foreach ($keys as $key) {
                $key = trim($key);
                if (strstr($key,'@')) {
                    $email = $key;
                }
                else {
                    $email = $this->getIniValue($key, 'email');
                }
                if (!empty($email)) {
                    $result[] = $email;
                }
            }
        }
        return $result;
    }

}