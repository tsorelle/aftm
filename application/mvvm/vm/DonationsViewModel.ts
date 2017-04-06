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

    interface IDonationListResponse {
        year: string;
        yearlist : string[];
        donations: IDonationListItem[];
    }

    interface IDonationInitResponse extends IDonationListResponse {
        canEdit: boolean;
    }

    export class donationObservable {

        // validation
        public hasErrors = ko.observable(false);
        public donationFirstNameError = ko.observable('');
        public donationLastNameError = ko.observable('');
        public donationAmountError = ko.observable('');
        public donationDateError = ko.observable('');

        // state
        public viewState = ko.observable('view');
        public formTitle = ko.observable('Donation');
        public activeTab = ko.observable('main');

        // donation properties
        public id             = ko.observable();
        public donationnumber = ko.observable('');
        public datereceived   = ko.observable('');
        public amount         = ko.observable('');
        public firstname      = ko.observable('');
        public lastname       = ko.observable('');
        public email          = ko.observable('');
        public phone          = ko.observable('');
        public address1       = ko.observable('');
        public address2       = ko.observable('');
        public city           = ko.observable('');
        public state          = ko.observable('');
        public postalcode     = ko.observable('');
        public notes          = ko.observable('');
        public paypalmemo     = ko.observable('');


        private clearErrors() {
            let me = this;
            me.donationFirstNameError('');
            me.donationLastNameError('');
            me.donationAmountError('');
            me.donationDateError('');

            me.hasErrors(false);
        }

        public clear() {
            let me = this;
            me.clearErrors();
        }

        public assign(donation: IDonation) {
            let me = this;
            me.id(donation.id);
            me.donationnumber(donation.donationnumber);
            me.datereceived(donation.datereceived);
            me.amount(donation.amount);
            me.firstname(donation.firstname);
            me.lastname(donation.lastname);
            me.email(donation.email);
            me.phone(donation.phone);
            me.address1(donation.address1);
            me.address2(donation.address2);
            me.city(donation.city);
            me.state(donation.state);
            me.postalcode(donation.postalcode);
            me.notes(donation.notes);
            me.paypalmemo(donation.paypalmemo);
            me.clearErrors();

            me.formTitle(
                (donation.id) ? "Donation #" + donation.donationnumber : "New Donation"
            );



        }


        public validate = ():boolean => {
            let me = this;
            me.clearErrors();
            let valid = true;
            /*
             let value = me.meetingName();
             if (!value) {
             me.meetingNameError(": Please enter the name of the meeting.");
             valid = false;
             }
             */

            me.hasErrors(!valid);
            return valid;
        };


        public update(donation: IDonation) {
            let me = this;
            // donation.editState = me.donationId() ? editState.updated : editState.created;
        }

        public changeTab = () => {
            let me = this;
            let current = me.activeTab();
            me.activeTab(current == 'notes' ? 'main' : 'notes')
        }
    }


    export class DonationsViewModel implements IMainViewModel {
        static instance: Tops.DonationsViewModel;
        private application: Tops.IPeanutClient;
        private peanut: Tops.Peanut;

        donationForm = new donationObservable();
        donations = ko.observableArray<IDonationListItem>();
        years = ko.observableArray<string>();
        selectedYear = ko.observable('');
        userCanEdit = ko.observable(false);
        yearFilter : any = null;


        // observable declarations here

        // Constructor
        constructor() {
            let me = this;
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
            let me = this;
            // setup messaging and other application initializations
            me.application.initialize(
                function() {
                    // do view model initializations here.
                    me.application.hideServiceMessages();
                    me.application.showWaiter('Getting donation list...');
                    me.peanut.executeService('donations\\InitDonationList',null,
                        function(serviceResponse: IServiceResponse) {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                me.userCanEdit(serviceResponse.Value.canEdit);
                                me.setupLists(serviceResponse.Value);
                                successFunction();
                            }
                        }
                    ).always(function() {
                        me.application.hideWaiter();
                    });
                }
            );
        }

        setupLists = (response : IDonationListResponse) => {
            let me = this;
            if (me.yearFilter) {
                me.yearFilter.dispose();
            }
            me.donations(response.donations);
            response.yearlist.push('All years');
            me.years(response.yearlist);
            if (response.year) {
                me.selectedYear(response.year);
            }
            else {
                me.selectedYear('All years');
            }
            me.yearFilter = me.selectedYear.subscribe(me.selectYear);
        };

        selectYear = (year: string) => {
            let me = this;
            me.application.hideServiceMessages();
            me.application.showWaiter('Getting donation list for '+year+'...');
            me.peanut.executeService('donations\\GetDonationList',{'year' : year},
                function(serviceResponse: IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.donations(serviceResponse.Value);
                    }
                }
            ).always(function() {
                me.application.hideWaiter();
            });
        };

        showDonation = (donation : IDonationListItem) => {
            let me = this;
            me.application.hideServiceMessages();
            me.application.showWaiter('Getting donation #'+donation.donationnumber);
            me.peanut.executeService('donations\\GetDonation',{'donationId' : donation.id},
                function(serviceResponse: IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.donationForm.assign(serviceResponse.Value);
                        me.donationForm.viewState('view');
                        jQuery("#donation-detail-modal").modal('show');
                    }
                }
            ).always(function() {
                me.application.hideWaiter();
            });

        };
        createDonation = () => {
            let me = this;
            alert('create donation');
        };
        editDonation = () => {
            let me = this;
            me.donationForm.viewState('edit');
        };
        updateDonation = () => {
            let me = this;
            alert('update donation');
        };
        cancelEdit = () => {
            let me = this;
            jQuery("#donation-detail-modal").modal('hide');
        };
        deleteDonation  = (donation : IDonationListItem) => {
            let me = this;
            let message = (donation) ? 'delete ' + donation.donationnumber : 'delete donation';
            alert(message);
        };
    }
}

Tops.DonationsViewModel.instance = new Tops.DonationsViewModel();
(<any>window).DonationsViewModel = Tops.DonationsViewModel.instance;
