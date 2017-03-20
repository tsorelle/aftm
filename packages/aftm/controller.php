<?php
namespace Concrete\Package\Aftm;

use Concrete\Core\Asset\AssetList;
use Package;
use PageTheme;

class Controller extends Package
{

    protected $pkgHandle = 'aftm';
    protected $appVersionRequired = '5.8.1.0';
    protected $pkgVersion = '1.0.9';

    public function getPackageDescription()
    {
        return t('Theme and other items for AFTM.');
    }

    public function getPackageName()
    {
        return t('Aftm');
    }

    public function on_start()
    {
        // add bootstrap modals, not included in C5
        $al = AssetList::getInstance();
        $al->register(
            'javascript', 'bootstrap/modals',
            'js/bootstrap/modals.js',
            array('minify' => false, 'position' =>  \Concrete\Core\Asset\Asset::ASSET_POSITION_FOOTER),
            $this
        );
    }

        public function install()
    {
        $pkg = parent::install();
        PageTheme::add('aftm', $pkg);
    }
}