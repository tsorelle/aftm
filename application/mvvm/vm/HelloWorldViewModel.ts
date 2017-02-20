/**
 * Created by Terry on 3/17/2015.
 */

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

        message : KnockoutObservable<string> = ko.observable("Hi there.")

        // Constructor
        constructor() {

            var me = this;
            Tops.HelloWorldViewModel.instance = me;
            me.application = new Tops.Application(me);
            me.peanut = me.application.peanut;
        }

        onButtonClick() {
            // alert('Hello World');
            var me  = this;

            alert('Hello World.');
            /*
            me.peanut.executeService('HelloWorld',null, function(serviceResponse: Tops.IServiceResponse) {
                me.application.hideWaiter();
                if (serviceResponse.Result == Tops.Peanut.serviceResultSuccess) {
                    alert('Success!');
                }

            }).fail(function() {
                alert('Failed');
            });
            */
        }

        /**
         * @param applicationPath - root path of application or location of service script
         * @param successFunction - page inittializations such as ko.applyBindings() go here.
         *
         * Call this function in a script at the end of the page following the closing "body" tag.
         * e.g.
         *      ViewModel.init('/', function() {
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

                    if (successFunction) {
                        successFunction();
                    }
                }
            );
        }
    }
}

Tops.HelloWorldViewModel.instance = new Tops.HelloWorldViewModel();
(<any>window).HelloWorldViewModel = Tops.HelloWorldViewModel.instance;