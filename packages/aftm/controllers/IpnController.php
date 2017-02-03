<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 12/29/2016
 * Time: 7:06 AM
 */
/**
 * Must declare route in \application\bootstrap\app.php
 *
 * Route::register(
 *      '/tops/service/execute/{arg}',
 *      'Concrete\Package\Tops\Controller\ServiceRequestHandler::executeService'
 *      );
 *
 */

namespace Concrete\Package\Aftm\Controller;

use Concrete\Core\Controller\Controller;
use Concrete\Core\Http\Request;
use Core;
use Concrete\Core\Utility\Service\Text;
use Concrete\Core\Support\Facade\Facade;
use Concrete\Core\Support\Facade\Express;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Express\Entry\Search\Result\Result;
use Concrete\Core\Express\EntryList;



class IpnController extends Controller
{
    protected $entityManager;
    protected $app;
    protected $repository;
    protected $members;

    public function __construct() {
        $this->app = Facade::getFacadeApplication();
        $this->entityManager = $this->app->make('database/orm')->entityManager();
        $this->repository = $this->entityManager->getRepository('Concrete\Core\Entity\Express\Entity');
        $this->members = $this->findEntity('member');
    }

    private function saveChanges() {
        $this->entityManager->persist($this->members);
        $this->entityManager->flush();
    }

    private function findEntity($handle) {
        $entityObjects = $this->repository->findAll();
        foreach ($entityObjects as $entity)  {
            if ($entity->getHandle() == $handle) {
                return $entity;
            }
        }
        return false;
    }

    private function findEntry($id) {
        $entries = $this->members->getEntries(); // ->getValues();
        foreach ($entries as $entry) {
            if ($id === $entry->getId()) {
                return $entry;
            };
        }
        return false;
    }

    /**
     *
     */
    public function handleResponse() {
        if ($this->members === false) {
            exit( "Entity not Found");
        }

        $member = $this->findEntry(1);

        if ($member === false) {
            echo "#1 not found<br>";
        }
        else {
            echo "Email: ".$member->getMemberEmail().'<br>';
            $member->setAttribute('member_email','test@email.com');
            // $this->saveChanges();
            $this->repository = $this->entityManager->getRepository('Concrete\Core\Entity\Express\Entity');
            $this->members = $this->findEntity('member');
            $member = $this->findEntry(1);
            echo "New: ".$member->getMemberEmail()."<br>";
        }
        echo "Done<br>";
    }

}