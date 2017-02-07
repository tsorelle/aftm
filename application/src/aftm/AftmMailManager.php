<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/7/2017
 * Time: 10:45 AM
 */

namespace Application\aftm;


class AftmMailManager
{
    private static $instance;
    private static $siteUrl;
    const logoUrl = '/packages/aftm/images/logos/aftm-logo1.png';
    const supportUrl = '/index.php/support-aftm';

    public function getSiteUrl() {
        if (!isset(self::$siteUrl)) {
            $protocol = AftmConfiguration::getValue('site', 'protocol', 'http');
            $url = $protocol . "://" . $_SERVER["SERVER_NAME"];
            if ($_SERVER["SERVER_PORT"] != "80") {
                $url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
            }
            self::$siteUrl = $url;
        }
        return self::$siteUrl;
    }

    public function getLogoMarkup()
    {
        $siteUrl = $this->getSiteUrl();
        $result = array();
        $result[] = '<div style="float:right; padding-left: 8px;">';
        $result[] = '<div><a href="'.$siteUrl.'" ><img src="'.$siteUrl.self::logoUrl.'" /></a>"></div>';
        $result[] =  '<div><a href="'.$siteUrl.self::supportUrl.'"> Support AFTM</a>';
        $result[] = '</div>';
        return implode("\n",$result);
    }

    public static function GetInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new AftmMailManager();
        }
        return self::$instance;
    }

    public static function GetLogo() {
        return self::GetInstance()->getLogoMarkup();
    }
}