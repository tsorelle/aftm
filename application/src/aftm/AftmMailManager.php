<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/7/2017
 * Time: 10:45 AM
 */

namespace Application\Aftm;


class AftmMailManager
{
    private static $instance;
    private static $siteUrl;
    private static $logoMarkup;
    const logoUrl = '/packages/aftm/images/logos/aftm-logo-email.png';
    const supportUrl = '/index.php/support-aftm';
    const contactUrl = '/index.php/contact';

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

    public function formatAddressHtml($address1,$address2,$city,$state,$zipcode,$name='') {
        $contact = array();
        if (!empty($name)) {
            $contact[] = $name;
        }
        if (!empty($address1)) {
            $contact[] = $address1;
        }
        if (!empty($address2)) {
            $contact[] = $address2;
        }
        $cityline = array();
        if (!empty($city)) {
            $cityline[] = $city;
        }
        if (!empty($state)) {
            $cityline[] = $state;
        }
        if (!empty($zipcode)) {
            $cityline[] = $zipcode;
        }
        if (!empty($cityline)) {
            $contact[] = implode(' ',$cityline);
        }
        if (empty($contact)) {
            return '';
        }

        return '<p>'.implode("<br>\n",$contact).'</p>';
    }
    
    public function getLogoMarkup()
    {
        if (!isset(self::$logoMarkup)) {
            $siteUrl = $this->getSiteUrl();
            $result = array();
            $result[] ='<table style="border: 0"><tr><td>';
            $result[] ='</td><td><a href="'.$siteUrl.'" ><img src="'.$siteUrl.self::logoUrl.'" /></a></td>';

            $result[] ='<td valign="middle" style="vertical-align: middle">';
            $result[] = "<p><a href='$siteUrl'>Website (aftm.us)</a> </p>";
            $result[] = '<p><a href="'.$siteUrl.self::contactUrl.'">Contact AFTM</a> </p>';
            $result[] = '<p><a href="'.$siteUrl.self::supportUrl.'">Support AFTM</a> </p>';
            $result[] = "</td></tr></table>";

            // $result[] =     '<div><a href="'.$siteUrl.'" ><img src="'.$siteUrl.self::logoUrl.'" /></a></div>';
            // $result[] =     '<div><a href="'.$siteUrl.self::supportUrl.'"> Support AFTM</a></div>';
            // $result[] = '</div>';
            self::$logoMarkup = implode("\n",$result);
        }
        return self::$logoMarkup;
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

    public static function FormatAddress($address1,$address2,$city,$state,$zipcode,$name='') {
        return self::GetInstance()->formatAddressHtml($address1,$address2,$city,$state,$zipcode,$name);
    }
}