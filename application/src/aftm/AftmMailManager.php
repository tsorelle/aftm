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
    private static $styleSheet;
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

    public function getTemplate($templateName,array $replacements = null) {
        $filePath = __DIR__.'/email/'.$templateName;
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }
        if (!empty($replacements)) {
            foreach ($replacements as $key => $value) {
                $content = str_replace("[[$key]]",$value,$content);
            }
        }
        return $content;
    }

    public function mergeHtml($content) {
        return
            '<!DOCTYPE html>'."\n".
            '<html lang="de">'."\n".
            '  <head>'."\n".
            '      <meta charset="utf-8">'."\n".
            $this->getStyleSheet()."\n".
            '  </head>'."\n".
            '  <body>'."\n".
            '      <div>'."\n".
            $content."\n".
            "      </div>\n".
            "  </body>\n".
            "</html>\n";
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

        $html = '<p>'.implode("<br>\n",$contact).'</p>';
        // $result->text = implode("\n",$contact);
        return $html;
    }

    public function getStyleSheet() {
        if (!isset(self::$styleSheet)) {
            // add default styles
            self::$styleSheet = array(
                ' body { font-family: arial;}',
                ' h1,h2,h3,h4 { color: #b15300;}',
                ' .linklist { list-style: none }',
                ' .linklist li { padding: 3px; }'
            );
        }
        return  "<style>\n".
                    implode("\n", self::$styleSheet)
                ."</style>\n";
    }

    public function getLogoMarkup()
    {
        if (!isset(self::$logoMarkup)) {
            $siteUrl = $this->getSiteUrl();
            $result = array();
            $result[] ='<table style="border: 0"><tr><td>';
            $result[] ='</td><td><a href="'.$siteUrl.'" ><img src="'.$siteUrl.self::logoUrl.'" /></a></td>';

            $result[] ='<td valign="middle" style="vertical-align: middle">';
            $result[] ="<ul class='linklist'>";
            $result[] = "<li><a href='$siteUrl'>AFTM Website (aftm.us)</a> </li>";
            $result[] = '<li><a href="'.$siteUrl.self::contactUrl.'">Contact AFTM</a> </li>';
            $result[] = '<li><a href="'.$siteUrl.self::supportUrl.'">Support AFTM</a> </li>';
            $result[] ="</ul>";
            $result[] = "</td></tr></table>";

            // $result[] =     '<div><a href="'.$siteUrl.'" ><img src="'.$siteUrl.self::logoUrl.'" /></a></div>';
            // $result[] =     '<div><a href="'.$siteUrl.self::supportUrl.'"> Support AFTM</a></div>';
            // $result[] = '</div>';
            self::$logoMarkup = implode("\n",$result);
        }
        return self::$logoMarkup;
    }

    public function replaceTokens($template, $tokens, $value= null) {
        if (!is_array($tokens)) {
            return str_replace('[['.$tokens.']]',$value,$template);
        }
        $result = $template;
        foreach ($tokens as $name => $value) {
            $result = str_replace('[['.$name.']]',$value,$result);
        }
        return $result;
    }

    private function convertLinks($html) {
        $result = '';
        $parts = explode('<a',$html);
        $result = $parts[0];
        $end = sizeof($parts) - 1;
        for ($i=1; $i <= $end; $i++) {
            $part = $parts[$i];
            $href = stristr($part,'href');
            if ($href === false) {
                return $html;
            }
            $href = strstr($href,'>',true);
            $href = str_ireplace('href','',$href);
            $href = str_replace('=','',$href);
            $href = str_replace("'",'',$href);
            $href = str_replace('"','',$href);
            $href = trim($href);
            $part = strstr($part,'>');
            $value = strstr($part,'<',true);
            $value = strstr($value,'>');
            $value = substr($value,1);
            $value = trim($value);
            $part = strstr($part,'</a>');
            $part = str_ireplace('</a>','',$part);
            if (!empty($value.$href) ) {
                $result .= " $value ($href) ";
            }
            $result .= $part;
        }
        return $result;
    }

    public function toPlainText($html) {
        $html = str_replace("\n",'',$html);
        $html = str_ireplace('<p>','',$html);
        $html = str_ireplace('</p>',"\n",$html);
        $html = str_ireplace('<br>',"\n",$html);
        $html = str_ireplace('<hr>',"\n---------------------------------------\n",$html);
        $html = str_ireplace('<h1>','',$html);
        $html = str_ireplace('</h1>',"\n\n",$html);
        $html = str_ireplace('<h2>','',$html);
        $html = str_ireplace('</h2>',"\n\n",$html);
        $html = str_ireplace('<h3>','',$html);
        $html = str_ireplace('</h3>',"\n\n",$html);
        $html = str_ireplace('<h4>','',$html);
        $html = str_ireplace('</h4>',"\n\n",$html);
        $html = $this->convertLinks($html);
        return strip_tags($html);
    }

    public function getPlainLinks() {
        $siteUrl = $this->getSiteUrl();
        return
            'AFTM Website: '.$siteUrl."\n".
            'Contact AFTM: '.$siteUrl.self::contactUrl."\n".
            'Support AFTM: '.$siteUrl.self::supportUrl."\n";
    }

    public static function Create()
    {
        return new AftmMailManager();
    }


}