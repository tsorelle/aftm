<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 1/25/2017
 * Time: 6:54 AM
 */

namespace Application\Aftm;

use Application\Aftm\AftmConfiguration;
use Application\Aftm\concrete\AftmFileManager;
use Concrete\Core\Controller\Controller;
use Concrete\Core\Http\Request;
use Core;
use Concrete\Core\Utility\Service\Text;
use Concrete\Core\Support\Facade\Facade;
use Concrete\Core\Support\Facade\Express;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Express\Entry\Search\Result\Result;
use Concrete\Core\Express\EntryList;


class TestController extends Controller
{


    public function doTest() {
        $setName = 'Newsletters';

        $result = AftmFileManager::GetFilesBySet($setName);
        if ($result === false) {
            exit('No set');
        }

        foreach ($result as $f) {
            echo "<p>$f->title</p>";
        }


        echo "<br>Done<br>";
    }


}