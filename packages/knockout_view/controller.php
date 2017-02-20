<?php

namespace Concrete\Package\KnockoutView;

use Concrete\Core\Asset\AssetList;
use Package;
use BlockType;
use SinglePage;
use Route;
use Database;

class Controller extends Package
{
    protected $pkgHandle = 'knockout_view';
    protected $appVersionRequired = '5.8.0';
    protected $pkgVersion = '1.1';

    public function getPackageName()
    {
        return t('Knockout View');
    }

    public function getPackageDescription()
    {
        return t('Support for Knockout and Typescript');
    }


    public function on_start()
    {
        $al = AssetList::getInstance();
        $al->register(
            'javascript', 'knockoutjs',
            'https://cdnjs.cloudflare.com/ajax/libs/knockout/3.4.1/knockout-min.js',
            // 'js/ko/knockout-3.4.1.js',
            array('local' => false,'minify' => false, 'position' =>  \Concrete\Core\Asset\Asset::ASSET_POSITION_HEADER)
        );

        $al->register(
            'javascript', 'headjs',
            'http://cdnjs.cloudflare.com/ajax/libs/headjs/1.0.3/head.load.min.js',
            // 'js/ko/knockout-3.4.1.js',
            array('local' => false,'minify' => false, 'position' =>  \Concrete\Core\Asset\Asset::ASSET_POSITION_HEADER)
        );

        $al->register(
            'javascript', 'topspeanut',
            'js/Tops.Peanut/Peanut.js',
            array('minify' => false, 'position' =>  \Concrete\Core\Asset\Asset::ASSET_POSITION_HEADER),
            $this
        );
        $al->register(
            'javascript', 'topsapp',
            'js/Tops.App/App.js',
            array('minify' => false, 'position' =>  \Concrete\Core\Asset\Asset::ASSET_POSITION_HEADER),
            $this
        );
    }


    public function install()
    {
        $pkg = parent::install();
        BlockType::installBlockType('knockout_view', $pkg);
        return $pkg;
    }

    public function uninstall() {
        parent::uninstall();
        $db = Database::connection();
        $db->executeQuery('DROP TABLE IF EXISTS btKnockoutView');
    }

}