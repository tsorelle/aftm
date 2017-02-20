<?php
/**
 * Created by PhpStorm.
 * User: tsorelle
 * Date: 2/25/14
 * Time: 3:13 PM
 */

namespace Application\Tops\services;
use Application\Tops\sys\TSession;
// use Application\Tops\sys\IUser;
//  use Application\Tops\sys\TTracer;
// use Application\Tops\sys\TUser;
use Application\Tops\sys\IMessageContainer;

/**
 * Class TServiceCommand
 * @package Tops\services
 */
abstract class TServiceCommand {
    /**
     * @var TServiceContext
     */
    private  $context;
    private  $request;

    /**
     * @var array
     */
    private $authorizations = array();
    private $errorCount = 0;

    abstract protected function run();

    protected function hasErrors() {
        return ($this->errorCount > 0);
    }

    protected function addErrorMessage($text) {
        $this->context->AddErrorMessage($text);
        $this->errorCount++;
    }

    public function addInfoMessage($text) {
        $this->context->AddInfoMessage($text);
    }

    public function addWarningMessage($text) {
        $this->context->AddWarningMessage($text);
    }

    public function setReturnValue($value) {
        $this->context->SetReturnValue($value);
    }

    /**
     * Get return value for unit testing
     * @return mixed|null
     */
    private function getReturnValue() {
        $response = $this->context->GetResponse();
        return ($response == null) ? null : $response->Value;
    }

    public function runTest($request)
    {
        $this->context = new TServiceContext();
        $this->setRequest($request);
        $this->run();
        return $this->getReturnValue();
    }

    public function getTestResponse($request) {
        $this->context = new TServiceContext();
        $this->setRequest($request);
        $this->run();
        return $this->context->GetResponse();
    }

    /**
     * Set request for unit testing.
     * @param $value
     */
    public function setRequest($value) {
        $this->request = $value;
    }


    public function getRequest() {

        return $this->request;
    }


    /**
     * @return IMessageContainer
     */
    protected function  getMessages()
    {
        return $this->context;
    }

    /**
     * @var IUser
     */
    private $user;

    /**
     * @return IUser
     */
    protected function getUser() {
        // todo:implement user object
        return false;
        if (!isset($this->user)) {
            $this->user = TUser::getCurrent();
        }
        return $this->user;
    }


    public function isAuthorized() {
        // todo:implement authorization
        if (empty($this->authorizations))
            return true;
        /**
         * @var IUser $user
         */
/*
        $user = $this->getUser();
        if ($user->isAdmin()) {
            return true;
        }
        foreach($this->authorizations as $auth) {
            if ($user->isAuthorized($auth)) {
                return true;
            }
        }
*/
        return false;
    }

    protected function addAuthorization($authorization) {
        if (!in_array($authorization, $this->authorizations)) {
            array_push($this->authorizations, $authorization);
        }
    }


    /**
     * Check service request for insecure content, to prevent cross site scripting attacks.
     * The method can be overriden in sub-classes in order to either disable or extend
     * the security checking.
     *
     * The default functionality checks only for specific tags. It may be necessary to perform
     * more rigorous case specific checking in the sub-class.
     *
     * Returns true if no potentially unsave content is found.
     *
     * @param $request  mixed
     * @return bool
     */
    protected function secureContent($request)
    {
        if (empty($request)) {
            return true;
        }

        if (is_object($request)) {
            $content = json_encode($request);
        }
        else {
            $content = $request;
            if (is_numeric(trim($content))) {
                return true;
            }
        }

        // disallow any html tags that might contain script injection
        if (strstr($content,'<')) {
            $tags = array('script', 'object', 'img', 'a','button', 'p', 'span', 'div', 'form', 'section', 'input','ul','ol','select','text','style');
            foreach ($tags as $tag) {
                $pattern = '/(' . $tag . '|<*\s' . $tag . ')(>|\s)/i';
                if (preg_match($pattern, $content)) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * @param $request
     * @return TServiceResponse
     */
    public function execute($request, $securityToken = null) {
        $this->context = new TServiceContext();
        if (TSession::AuthenitcateSecurityToken($securityToken)) {
            if ($this->secureContent($request)) {
                $this->request = $request;
                if ($this->isAuthorized())
                    $this->run();
                else
                    $this->addErrorMessage("Sorry, you are not authorized to use this service.");
            }
            else {
                $this->addErrorMessage("Your request contains potentially insecure content. HTML tags are not allowed.");
            }
        }
        else {
            $this->addErrorMessage("Sorry, your session has expired or is not valid. Please return to home page.");
        }

        return $this->context->GetResponse();
    }
}
