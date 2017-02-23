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
namespace Application\Tops\services;

use Concrete\Core\Controller\Controller;
use Concrete\Core\Http\Request;
use Core;
use Concrete\Core\Utility\Service\Text;
use Application\Tops\sys;


class ServiceRequestHandler extends Controller
{
    public function executeService() {
        $response = '';
        try {
            /**
             * @var $th \Concrete\Core\Utility\Service\Text
             */
            $th = Core::make('helper/text');
            $request = Request::getInstance();
            $method = $request->getMethod();

            if ($method == 'POST') {
                $serviceId = $request->get('serviceCode');
                $input = $request->get('request');
                $input = json_decode($input);
                // $serviceId = $input->serviceCode;
                // $input = isset($input->parameters) ? $input->parameters : null;
            } else {
                $serviceId = $request->get('sid');
                $serviceId = $th->sanitize($serviceId);
                $input = $request->get('arg');
                $input = $th->sanitize($input);
            }

            $parts = explode('::', $serviceId);
            if (sizeof($parts) == 1) {
                $namespace = sys\TopsConfiguration::getValue('applicationNamespace', 'services');
            } else {
                $namespace = $parts[0];
                $namespace = "\\Concrete\\Package\\$namespace\\Src\\Services";
                $serviceId = $parts[1];
            }

            $className = $namespace . "\\" . $serviceId . 'Command';

            if (!class_exists($className)) {
                throw new \Exception("Cannot instatiate service '$className'.");
            }

            /**
             * @var $cmd TServiceCommand
             */
            $cmd = new $className();
            $response = $cmd->execute($input);

        }
        catch (\Exception $ex) {
            // todo: exception handling
            /*
            $rethrow = $instance->handleException($ex);
            if ($rethrow) {
                throw $ex;
            }
            */
            $debugInfo = new \stdClass();
            $debugInfo->message = $ex->getMessage();
            $debugInfo->location = $ex->getFile().": Line ".$ex->getLine();
            $debugInfo->trace = $ex->getTraceAsString();

            $response = $this->getFailureResponse($debugInfo);
        }

        echo json_encode($response);
    }

    /**
     * @return TServiceResponse
     */
    private function getFailureResponse($debugInfo = null) {
        if (!isset($this->failureResponse)) {
            $this->failureResponse = new TServiceErrorResponse();
            $this->failureResponse->Result = ResultType::ServiceFailure;
            $message = new TServiceMessage();
            $message->MessageType = MessageType::Error;
            $message->Text = 'Service failed. If the problem persists contact the site administrator.';
            $this->failureResponse->Messages = array($message);
            $this->failureResponse->debugInfo = $debugInfo;
        }
        return $this->failureResponse;
    }

}