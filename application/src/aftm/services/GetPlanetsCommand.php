<?php

 namespace Application\Aftm\services;

 use Application\Tops\services\TServiceCommand;

 class GetPlanetsCommand  extends TServiceCommand
 {
     private function getPlanet($name) {
         $planet = new \stdClass();
         $planet->name = $name;
         switch($name) {
             case 'Earth' : $planet->description = 'Earth is our home'; break;
             case 'Mercury' : $planet->description = 'Mercury is closest to the sum.'; break;
             case 'Venus' : $planet->description = 'Planet of the love goddess.'; break;
             case 'Pluto' : $planet->description = 'Pluto gets no respect :-('; break;
         }
         return $planet;
     }

     protected function run()
     {
         $request = $this->getRequest();
         if (empty($request)) {
             $this->addErrorMessage('No request received');
             return;
         }
         $includePluto = !empty($request->includePluto);
         $planetList = array();
         $planetList[] = $this->getPlanet('Mercury');
         $planetList[] = $this->getPlanet('Venus');
         $planetList[] = $this->getPlanet('Earth');
         if ($includePluto) {
             $planetList[] = $this->getPlanet('Pluto');
         }


         $this->addInfoMessage('Found '.sizeof($planetList). ' planets.');
         $this->setReturnValue($planetList);
     }
 }