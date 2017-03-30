<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/30/2017
 * Time: 7:10 AM
 */

namespace Application\Tops\sys;

/**
 * Class TObjectContainer
 * @package Application\Tops\sys
 *
 * Temporary "poor man's" dependency injection
 * Only supports parameterless singleton instances.
 */
class TObjectContainer
{
    private static $instances = array();

    /**
     * @param $key
     * @return mixed
     *
     * Retrieve instance from the container.
     */
    public static function Get($key) {
        // return self::GetContainer()->get($key);
        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        };
        $className = TopsConfiguration::getValue($key,'classes',false);
        if (empty($className)) {
            return false;
        }
        $instance = new $className();
        self::$instances[$key] = $instance;
        return $instance;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function HasDefinition($id) {
        $className = TopsConfiguration::getValue($id,'classes',false);
        return (!empty($className));
        // return self::GetContainer()->hasDefinition($id);
    }

}