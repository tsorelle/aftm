/**
 * Created by Terry on 2/19/2015.
 */
///<reference path='../typings/knockout/knockout.d.ts' />
/// <reference path="./App.ts" />
/// <reference path="../Tops.Peanut/Peanut.ts" />
///<reference path='../Tops.Peanut/Peanut.d.ts' />
// Module
module Tops {

//hello

    // view model
    export class TestPageViewModel implements IMainViewModel {
        static instance: Tops.TestPageViewModel;
        private application: Tops.Application;
        private peanut: Tops.Peanut;


        // Constructor
        constructor() {
            var me = this;
            Tops.TestPageViewModel.instance = me;
            me.application = new Tops.Application(me);
            me.peanut = me.application.peanut;

        }


        messageText = ko.observable('');

        itemName = ko.observable('');
        itemId = ko.observable(1);

        onGetItem() {
            var me = this;
            me.application.showWaiter('Please wait...');
            me.peanut.getFromService( 'TestGetService',3, function (serviceResponse: Tops.IServiceResponse) {
                    if (serviceResponse.Result == Tops.Peanut.serviceResultSuccess) {
                        me.itemName(serviceResponse.Value.name);
                        me.itemId(serviceResponse.Value.id);
                    }
                    else {
                        alert("Service failed");
                    }
                }
            ).always(function() {
                    me.application.hideWaiter();
                });

        }

        onPostItem() {
            var me = this;
            var request = {
                testMessageText : me.itemName()
            };

            me.application.showWaiter('Please wait...');
            me.peanut.executeService( 'TestService',request)
                .always(function() {
                    me.application.hideWaiter();
                });

        }




        // person: KnockoutObservable<any> = ko.observable();
        // Declarations
        // Examples:
        //  templateList: KnockoutObservableArray = ko.observableArray([]);
        //  currentPage: KnockoutObservableString = ko.observable("");


        // Methods
        // test() { alert("hello"); }

        // call this funtions at end of page
        init(applicationPath: string, successFunction?: () => void) {
            var me = this;

            // setup messaging and other application initializations
            me.application.initialize(applicationPath,
            function() {
                    // me.clearPerson();
                    me.application.showMessage("initialized");
                    if (successFunction) {
                        successFunction();
                    }
                }
            );
        }

        onAddMessageClick() {
            var me = this;
            var msg = me.messageText();
            me.application.showMessage(msg);
            me.messageText('');
        }

        onAddErrorMessageClick() {
            var me = this;
            var msg = me.messageText();
            me.application.showError(msg);
            me.messageText('');
        }
        onAddWarningMessageClick() {
            var me = this;
            var msg = me.messageText();
            me.application.showWarning(msg);
            me.messageText('');
        }



        onShowSpinWaiter() {
            var count = 0;
            Tops.waitMessage.show("Hello " + (new Date()).toISOString());
            var t = window.setInterval(function() {
                if (count > 100) {
                    clearInterval(t);
                    Tops.waitMessage.hide();
                }
                else {
                    Tops.waitMessage.setMessage('Counting ' + count);
                    // Tops.waitMessage.setProgress(count,true);
                }
                count += 1;
            }, 100);

        }

        onShowWaiter() {
            var count = 0;
            Tops.waitMessage.show("Hello " + (new Date()).toISOString(), 'progress-waiter');
            var t = window.setInterval(function() {
                if (count > 100) {
                    clearInterval(t);
                    Tops.waitMessage.hide();
                }
                else {
                    Tops.waitMessage.setMessage('Counting ' + count);
                    Tops.waitMessage.setProgress(count,true);
                }
                count += 1;
            }, 100);
        }

        onHideWaiter() {
            Tops.waitMessage.hide();

        }
    }
}

Tops.TestPageViewModel.instance = new Tops.TestPageViewModel();
(<any>window).ViewModel = Tops.TestPageViewModel.instance;