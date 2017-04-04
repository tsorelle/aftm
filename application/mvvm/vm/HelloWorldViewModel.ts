/// <reference path='../typings/jquery/jquery.d.ts' />
/// <reference path="../typings/knockout/knockout.d.ts" />
/// <reference path="../typings/custom/head.load.d.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.App/App.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.Peanut/Peanut.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.Peanut/Peanut.d.ts" />
module Tops {
    export class HelloWorldViewModel implements IMainViewModel {
        static instance: Tops.HelloWorldViewModel;
        private application: Tops.IPeanutClient;
        private peanut: Tops.Peanut;

        private planetList = [];
        private nextPlanet = 1;

        // observable declarations here
        planetName = ko.observable('');
        planetDescription = ko.observable('');


        getPlanet() {
            let me = this;
            let planet = me.planetList[me.nextPlanet - 1];
            if (me.nextPlanet == me.planetList.length) {
                me.nextPlanet = 1;
            }
            else {
                me.nextPlanet++;
            }
            me.planetName(planet.name);
            me.planetDescription(planet.description)
        }

        getPlanetList(successFunction: ()=> void) {
            let me = this;
            let request = {'includePluto' : 1};
            me.application.hideServiceMessages();
            me.application.showWaiter('Getting the solar system...');
            me.peanut.executeService('test\\GetPlanets',request,
                function(serviceResponse: IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.planetList = serviceResponse.Value;
                        successFunction();
                    }
                }
            ).always(function() {
                me.application.hideWaiter();
            }).fail(
                function () {
                    alert('Process failed!!')
                }
            );
        }

        // Constructor
        constructor() {
            var me = this;
            Tops.HelloWorldViewModel.instance = me;
            me.application = new Tops.Application(me);
            me.peanut = me.application.peanut;
        }

        /**
         * @param successFunction - page inittializations such as ko.applyBindings() go here.
         *
         * Call this function in a script at the end of the page following the closing "body" tag.
         * e.g.
         *      ViewModel.init(function() {
         *          ko.applyBindings(ViewModel);
         *      });
         *
         */
        init(successFunction?: () => void) {
            var me = this;
            // setup messaging and other application initializations
            me.application.initialize(
                function() {
                    // do view model initializations here.
                    me.getPlanetList(successFunction)
                }
            );
        }
    }
}

Tops.HelloWorldViewModel.instance = new Tops.HelloWorldViewModel();
(<any>window).HelloWorldViewModel = Tops.HelloWorldViewModel.instance;
