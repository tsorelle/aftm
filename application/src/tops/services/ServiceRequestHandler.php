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
        /**
         * @var $th \Concrete\Core\Utility\Service\Text
         */
        $th = Core::make('helper/text');
        $request = Request::getInstance();
        $method = $request->getMethod();

        if ($method=='POST') {
            $serviceId = $request->get('serviceCode');
            $input = $request->get('request');
            $input = json_decode($input);
            // $serviceId = $input->serviceCode;
            // $input = isset($input->parameters) ? $input->parameters : null;
        }
        else {
            $serviceId = $request->get('sid');
            $serviceId = $th->sanitize($serviceId);
            $input = $request->get('arg');
            $input = $th->sanitize($input);
        }

        $parts = explode('::',$serviceId);
        if (sizeof($parts) == 1) {
            $namespace = sys\TopsConfiguration::getValue('applicationNamespace','services');
        }
        else {
            $namespace = $parts[0];
            $namespace = "\\Concrete\\Package\\$namespace\\Src\\Services";
            $serviceId = $parts[1];
        }

        $className = $namespace."\\".$serviceId.'Command';

        if (!class_exists($className)) {
            throw new \Exception("Cannot instatiate service '$className'.");
        }

        /**
         * @var $cmd TServiceCommand
         */
        $cmd = new $className();
        $response = $cmd->execute($input);
        echo json_encode($response);

        
    }

}