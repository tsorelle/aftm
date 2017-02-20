<?php

/**
 * Created by PhpStorm.
 * User: tsorelle
 * Date: 2/22/14
 * Time: 9:57 AM
 */

namespace Application\Tops\services;
use Application\Tops\sys\IMessageContainer;

/**
 * Class TServiceContext
 *
 * Managing container for a service response
 *
 * @package Tops\services
 */
class TServiceContext implements IMessageContainer {
    /**
     * @var TServiceResponse
     */
    private  $response;

    public function __construct() {
        $this->response = new TServiceResponse();
        $this->response->Messages = array();
        $this->response->Result = ResultType::Success;
    }
    public function AddMessage($messageType,$text) {
        $message = new TServiceMessage();
        $message->MessageType = $messageType;
        $message->Text = $text;
        array_push($this->response->Messages, $message);
    }

    public function AddInfoMessage($text) {
        $this->AddMessage(MessageType::Info,$text);
    }

    public function AddWarningMessage($text) {
        $this->AddMessage(MessageType::Error,$text);
        if ($this->response->Result < ResultType::Warnings)
            $this->response->Result = ResultType::Warnings;
    }


    public function AddErrorMessage($text) {
        $this->AddMessage(MessageType::Error,$text);
        if ($this->response->Result < ResultType::Errors)
            $this->response->Result = ResultType::Errors;
    }

    public function GetResult() {
        return $this->response->Result;
    }

    public function AddServiceFatalErrorMessage($text) {
        $this->AddMessage(MessageType::Error,$text);
        $this->response->Result = ResultType::ServiceFailure;
    }

    public function ServiceNotAvailable() {
        $this->AddMessage(MessageType::Error,'The service is not available.');
        $this->response->Result = ResultType::ServiceNotAvailable;
    }

    /**
     * @return TServiceResponse
     */
    public function GetResponse() {
        return $this->response;
    }

    public function SetReturnValue($value) {
        $this->response->Value = $value;
    }
} 