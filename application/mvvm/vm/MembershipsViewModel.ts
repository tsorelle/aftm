/**
 * Created by Terry on 4/4/2017.
 */
/// <reference path='../typings/jquery/jquery.d.ts' />
/// <reference path="../typings/knockout/knockout.d.ts" />
/// <reference path="../typings/custom/head.load.d.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.App/App.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.Peanut/Peanut.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.Peanut/Peanut.d.ts" />
module Tops {

    interface IMembershipListItem {
        id     : any;
        invoicenumber  : string;
        reneweddate    : string;
        membershiptype : string;
        firstname      : string;
        lastname       : string;
        email          : string;
    }

    interface IMembership extends IMembershipListItem{
        address1            : string;
        address2            : string;
        city                : string;
        state               : string;
        postalcode          : string;
        groupname           : string;
        groupwebsite        : string;
        volunteerinterests  : string;
        startdate           : string;
        expirationdate      : string;
        paymentmethod       : string;
        paymentreceiveddate : string;
        neworrenewal        : string;
        amount              : string;
        ideas               : string;
        notes               : string;
        paypalmemo          : string;
    }

    interface IMembershipListResponse {
        year: string;
        yearlist : string[];
        memberships: IMembershipListItem[];
    }

    export class MembershipsViewModel implements IMainViewModel {
        static instance: Tops.MembershipsViewModel;
        private application: Tops.IPeanutClient;
        private peanut: Tops.Peanut;

        memberships = ko.observableArray<IMembershipListItem>();
        years = ko.observableArray<string>();
        selectedYear = ko.observable('');
        yearFilter : any = null;

        // observable declarations here

        // Constructor
        constructor() {
            let me = this;
            Tops.MembershipsViewModel.instance = me;
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
                    me.application.showWaiter('Getting membership list...');
                    me.peanut.executeService('membership\\InitMembershipList',null,
                        function(serviceResponse: IServiceResponse) {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
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

        setupLists = (response : IMembershipListResponse) => {
            let me = this;
            if (me.yearFilter) {
                me.yearFilter.dispose();
            }
            me.memberships(response.memberships);
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
            me.application.showWaiter('Getting membership list for '+year+'...');
            me.peanut.executeService('membership\\GetMembershipList',{'year' : year},
                function(serviceResponse: IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.setupLists(serviceResponse.Value);
                    }
                }
            ).always(function() {
                me.application.hideWaiter();
            });
        }
    }
}

Tops.MembershipsViewModel.instance = new Tops.MembershipsViewModel();
(<any>window).MembershipsViewModel = Tops.MembershipsViewModel.instance;
