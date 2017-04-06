<?php
namespace Concrete\Package\KnockoutView\Block\KnockoutView;

use Application\Tops\sys\TSession;
use Concrete\Core\Block\BlockController;
use Page;

class Controller extends BlockController
{
    protected $btTable = 'btKnockoutView';
    protected $btInterfaceWidth = "600";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "500";

    protected $btCacheBlockRecord = false;
    protected $btCacheBlockOutput = false;
    protected $btCacheBlockOutputOnPost = false;
    protected $btCacheBlockOutputForRegisteredUsers = false;

/*
    protected $btCacheBlockRecord = true;
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = true;
    protected $btCacheBlockOutputForRegisteredUsers = true;
*/
    protected $btIgnorePageThemeGridFrameworkContainer = true;


    public $content = "";
    public $viewmodel = '';
    public $addwrapper = '';

    public function getBlockTypeDescription()
    {
        return t("Create a Knockout View");
    }

    public function getBlockTypeName()
    {
        return t("Knockout View");
    }

    /**
     * Return structure parsed form $this->viewModel
     * $result->code  -  view model code name
     * $result->path  - location of javascript file
     *
     * Format of $this viewmodel is
     *    [package handle::][subpath\]viewmodel_code
     *
     *  Example
     *      $this->viewmodel == 'myModel';
     *      returns
     *              code: 'myModel';
     *              path: '/application/js/vm/myModelViewModel.js
     *
     *      $this->viewmodel == 'aftm::myModel';
     *      returns
     *              code: 'myModel';
     *              path: '/packages/aftm/js/vm/myModelViewModel.js
     *
     *      $this->viewmodel == 'users/admi/myModel';
     *      returns
     *              code: 'myModel';
     *              path: '/application/js/vm/myModelViewModel.js
     *
     *      $this->viewmodel == 'aftm::users/admin/myModel';
     *      returns
     *              code: 'myModel';
     *              path: '/packages/aftm/js/vm/users/admin/myModelViewModel.js
     *
     *
     * @return bool|\stdClass
     * false if $this->viewModel not assigned.
     */
    private function getViewModel()
    {
        $result = new \stdClass();
        $result->wrapperid = '';
        $result->path = '';
        $result->classname = '';
        $result->viewfile = '';

        if (empty($this->viewmodel)) {
            return $result;
        }

        $vm = $this->viewmodel;
        $parts = explode('::',$vm);
        if (sizeof($parts) == 1) {
            $location = 'application/mvvm/vm/';
        }
        else {
            $location = 'packages/js/vm/'.$parts[0];
            $vm = $parts[1];
        }
        $result->viewfile = $vm.'View.html';
        $parts = explode('/',$vm);
        $len = sizeof($parts);
        $vmcode = $parts[$len - 1];
        $result->wrapperid = strtolower($vmcode)."-mvvm-container";
        $result->classname = $vmcode.'ViewModel';
        $result->path = '/'.$location.$result->classname.'.js?'.$this->random_string(8);

        return $result;
    }

    private static $randkeys;

    private function random_string($length) {
        $key = '';
        if (empty($this->randkeys)) {
            $this->randkeys = array_merge(range(0, 9), range('a', 'z'));
        }
        $keys = $this->randkeys;

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }


    public function view()
    {
        $vm = $this->getViewModel();
        $c = Page::getCurrentPage();
        if (!$c->isEditMode()) {
            $this->requireAsset('javascript','headjs');
            $this->requireAsset('javascript','knockoutjs');
            $this->requireAsset('javascript','topspeanut');
            $this->requireAsset('javascript','topsapp');

            $header = strstr($this->content,'<!-- use view file');
            if ($header !== false) {
                $filepath = DIR_APPLICATION.'/mvvm/view/'.$vm->viewfile;
                $content = @file_get_contents($filepath);
                if ($content === FALSE) {
                    $this->content = "<p>Warning: View file not found: $filepath</p>";
                }
                else {
                    $this->content = $content;
                }
            }
        }




        $this->set('content', $this->content);
        $this->set('viewcontainerid',$vm->wrapperid);
        $this->set('addwrapper',$this->addwrapper);

        if (!$c->isEditMode()) {
            $this->addFooterItem(
                '<script src="' . $vm->path . '"></script>' .
                '<script>' . $vm->classname . '.init(function() {' . $vm->classname . '.application.bindSection(\'' . $vm->wrapperid . '\'); });</script>'
            );
            // init security token
            TSession::Initialize();
        }
    }

    public function add()
    {
        $this->edit();
    }

    public function edit()
    {
        $this->requireAsset('ace');
    }

    public function getSearchableContent()
    {
        return $this->content;
    }

    public function save($data)
    {
        $content = isset($data['content']) ? $data['content'] : '';
        $args['content'] = isset($data['content']) ? $data['content'] : '';
        $args['viewmodel'] = isset($data['viewmodel']) ? $data['viewmodel'] : '';
        $args['addwrapper'] = isset($data['addwrapper']) ? $data['addwrapper'] : 0;
        parent::save($args);
    }

    public static function xml_highlight($s)
    {
        $s = htmlspecialchars($s);
        $s = preg_replace(
            "#&lt;([/]*?)(.*)([\s]*?)&gt;#sU",
            "<font color=\"#0000FF\">&lt;\\1\\2\\3&gt;</font>",
            $s
        );
        $s = preg_replace(
            "#&lt;([\?])(.*)([\?])&gt;#sU",
            "<font color=\"#800000\">&lt;\\1\\2\\3&gt;</font>",
            $s
        );
        $s = preg_replace(
            "#&lt;([^\s\?/=])(.*)([\[\s/]|&gt;)#iU",
            "&lt;<font color=\"#808000\">\\1\\2</font>\\3",
            $s
        );
        $s = preg_replace(
            "#&lt;([/])([^\s]*?)([\s\]]*?)&gt;#iU",
            "&lt;\\1<font color=\"#808000\">\\2</font>\\3&gt;",
            $s
        );
        $s = preg_replace(
            "#([^\s]*?)\=(&quot;|')(.*)(&quot;|')#isU",
            "<font color=\"#800080\">\\1</font>=<font color=\"#FF00FF\">\\2\\3\\4</font>",
            $s
        );
        $s = preg_replace(
            "#&lt;(.*)(\[)(.*)(\])&gt;#isU",
            "&lt;\\1<font color=\"#800080\">\\2\\3\\4</font>&gt;",
            $s
        );

        return nl2br($s);
    }
}
