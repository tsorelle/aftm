<?php
namespace Concrete\Package\Aftm;

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

    public function install()
    {
        $pkg = parent::install();
        PageTheme::add('aftm', $pkg);
    }

}