/**
 * Created by Terry on 3/31/2017.
 */
/// <reference path='../typings/jquery/jquery.d.ts' />
/// <reference path="../typings/knockout/knockout.d.ts" />
/// <reference path="../typings/custom/head.load.d.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.App/App.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.Peanut/Peanut.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.Peanut/Peanut.d.ts" />
module Tops {

    interface IDonationListItem {
        id : any;
        donationnumber : string;
        datereceived : string;
        amount : any;
        firstname : string;
        lastname : string;
        email : string;
        phone : string;
    }

    interface IDonation extends IDonationListItem{
        address1 : string;
        address2 : string;
        city : string;
        state : string;
        postalcode : string;
        notes : string;
        paypalmemo : string;
    }

    interface IDonationInitResponse {
        year: number;
        yearlist : number[];
        donations: IDonationListItem[];
    }

    export class DonationsViewModel implements IMainViewModel {
        static instance: Tops.DonationsViewModel;
        private application: Tops.IPeanutClient;
        private peanut: Tops.Peanut;

        donations = ko.observableArray<IDonationListItem>();

        // observable declarations here

        // Constructor
        constructor() {
            var me = this;
            Tops.DonationsViewModel.instance = me;
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
                    me.application.hideServiceMessages();
                    me.application.showWaiter('Getting donation list...');
                    me.peanut.executeService('InitDonations',null,
                        function(serviceResponse: IServiceResponse) {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                let response = <IDonationInitResponse>serviceResponse.Value;
                                me.donations(response.donations);
                                // todo: set up year list

                                successFunction();
                            }
                        }
                    ).always(function() {
                        me.application.hideWaiter();
                    });


                }
            );
        }
    }
}

Tops.DonationsViewModel.instance = new Tops.DonationsViewModel();
(<any>window).DonationsViewModel = Tops.DonationsViewModel.instance;
