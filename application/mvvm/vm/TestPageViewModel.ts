/**
 * Created by Terry on 2/19/2015.
 */
/// <reference path='../typings/jquery/jquery.d.ts' />
/// <reference path="../typings/knockout/knockout.d.ts" />
/// <reference path="../typings/custom/head.load.d.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.App/App.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.Peanut/Peanut.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.Peanut/Peanut.d.ts" />

// Module
module Tops {


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
        init(successFunction?: () => void) {
            var me = this;
            // setup messaging and other application initializations
            me.application.initialize(
                function() {
                    me.application.loadComponent('modal-confirm', successFunction);
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
            let me = this;
            me.application.showWaiter();
            var t = window.setInterval(function() {
                    clearInterval(t);
                    me.application.hideWaiter();
            }, 1000);

        }

        onShowProgressWaiter() {
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

        onShowModalForm() {
            jQuery("#test-modal").modal('show');
        }

        onSaveChanges() {
            jQuery("#test-modal").modal('hide');
            jQuery("#confirm-save-modal").modal('show');
        }

        save = () => {
            jQuery("#confirm-save-modal").modal('hide');
            alert('you saved');
        };

    }
}

Tops.TestPageViewModel.instance = new Tops.TestPageViewModel();
(<any>window).TestPageViewModel = Tops.TestPageViewModel.instance;