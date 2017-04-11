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
        phone          : string;
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

    interface IMembershipInitResponse extends  IMembershipListResponse {
        membershipTypes: INameValuePair[];
    }

    export class membershipObservable {

        // validation
        public hasErrors = ko.observable(false);
        public membershipFirstNameError = ko.observable('');
        public membershipLastNameError = ko.observable('');
        public membershipDateError = ko.observable('');
        public paymentAmountError = ko.observable('');
        public paymentDateError = ko.observable('');

        // state
        public viewState = ko.observable('view');
        public formTitle = ko.observable('Membership');
        public activeTab = ko.observable('main');
        public viewTab = ko.observable('main');

        // membership properties
        public id             = ko.observable();
        public invoicenumber = ko.observable('');
        public reneweddate        = ko.observable('');
        // public membershiptype     = ko.observable('');
        public firstname          = ko.observable('');
        public lastname           = ko.observable('');
        public email              = ko.observable('');
        public address1            = ko.observable('');
        public address2            = ko.observable('');
        public city                = ko.observable('');
        public state               = ko.observable('');
        public postalcode          = ko.observable('');
        public groupname           = ko.observable('');
        public groupwebsite        = ko.observable('');
        public volunteerinterests  = ko.observable('');
        public paymentmethod       = ko.observable('');
        public paymentreceiveddate = ko.observable('');
        public neworrenewal        = ko.observable('');
        public amount              = ko.observable('');
        public ideas               = ko.observable('');
        public notes               = ko.observable('');
        public paypalmemo          = ko.observable('');
        public phone               = ko.observable('');

        // interest checkboxes
        public concertsInterest		= ko.observable<boolean>(false);
        public newslettersInterest  = ko.observable<boolean>(false);
        public publicityInterest    = ko.observable<boolean>(false);
        public festivalsInterest    = ko.observable<boolean>(false);
        public membershipInterest   = ko.observable<boolean>(false);
        public mailingsInterest     = ko.observable<boolean>(false);
        public webpageInterest      = ko.observable<boolean>(false);

        // lookups
        public membershiptypes = ko.observableArray<INameValuePair>();
        public selectedMembershipType = ko.observable<INameValuePair>();
        public membershiptype = ko.observable('');
        public renewaltypes = ko.observableArray<string>(['New','Renewal']);
        public paymentmethods = ko.observableArray<string>(['paypal','check']);


        private clearErrors() {
            let me = this;
            me.membershipFirstNameError('');
            me.membershipLastNameError('');
            me.paymentAmountError('');
            me.paymentDateError('');
            me.membershipDateError('');

            me.hasErrors(false);
        }

        public clear() {
            let me = this;
            me.id(0);
            me.activeTab('main');
            me.viewTab('main');
            me.firstname('');
            me.lastname('');
            me.address1('');
            me.address2('');
            me.city('');
            me.state('');
            me.postalcode('');
            me.email('');
            me.selectedMembershipType(null);
            me.groupname('');
            me.groupwebsite('');
            me.volunteerinterests('');
            me.reneweddate('');
            me.paymentmethod('');
            me.paymentreceiveddate('');
            me.invoicenumber('');
            me.neworrenewal('');
            me.amount('');
            me.ideas('');
            me.notes('');
            me.paypalmemo('');
            me.phone('');
            me.clearInterests();
            me.clearErrors();
        }

        private clearInterests() {
            let me = this;
            me.concertsInterest(false);
            me.newslettersInterest(false);
            me.publicityInterest(false);
            me.festivalsInterest(false);
            me.membershipInterest(false);
            me.mailingsInterest(false);
            me.webpageInterest(false);
        }

        private assignInterests(membership: IMembership) {
            let me = this;
            if (membership.volunteerinterests) {
                // remove spaces
                let interests = membership.volunteerinterests.replace(/\s+/g, '');;
                interests = ',' + interests + ',';
                me.concertsInterest(interests.indexOf(',concerts,') !== -1);
                me.newslettersInterest(interests.indexOf(',newsletters,') !== -1);
                me.publicityInterest(interests.indexOf(',publicity,') !== -1);
                me.festivalsInterest(interests.indexOf(',festivals,') !== -1);
                me.membershipInterest(interests.indexOf(',membership,') !== -1);
                me.mailingsInterest(interests.indexOf(',mailings,') !== -1);
                me.webpageInterest(interests.indexOf(',webpage,') !== -1);
            }
            else {
                me.clearInterests();
            }
        }


        public assign(membership: IMembership) {
            let me = this;
            me.id(membership.id);
            me.firstname(membership.firstname);
            me.lastname(membership.lastname);
            me.address1(membership.address1);
            me.address2(membership.address2);
            me.city(membership.city);
            me.state(membership.state);
            me.postalcode(membership.postalcode);
            me.email(membership.email);
            me.groupname(membership.groupname);
            me.groupwebsite(membership.groupwebsite);
            me.volunteerinterests(membership.volunteerinterests);
            me.reneweddate(membership.reneweddate);
            me.paymentmethod(membership.paymentmethod);
            me.paymentreceiveddate(membership.paymentreceiveddate);
            me.invoicenumber(membership.invoicenumber);
            me.neworrenewal(membership.neworrenewal);
            me.amount(membership.amount);
            me.ideas(membership.ideas);
            me.notes(membership.notes);
            me.paypalmemo(membership.paypalmemo);
            me.phone(membership.phone);


            let type : any = _.findWhere(me.membershiptypes(), {Value: membership.membershiptype});
            me.selectedMembershipType(type);
            me.membershiptype(type.Value || '');

            me.assignInterests(membership);


            me.activeTab('main');
            me.viewTab('main');
            me.clearErrors();

            me.formTitle(
                (membership.id) ? "Membership #" + membership.invoicenumber : "New Membership"
            );
        }

        private getInterests() : string {
            let me = this;
            let interests = [];
            if (me.concertsInterest()) {interests.push('concerts');}
            if (me.newslettersInterest()) {interests.push('newsletters')}
            if (me.publicityInterest()) {interests.push('publicity')}
            if (me.festivalsInterest()) {interests.push('festivals')}
            if (me.membershipInterest()) {interests.push('membership')}
            if (me.mailingsInterest()) {interests.push('mailings')}
            if (me.webpageInterest()) {interests.push('webpage')}
            return interests.join(', ');
        }

        public getMembership = () : IMembership => {
            let me = this;
            let renewedDate = new Date(me.reneweddate());
            let renewedDateString = (renewedDate.toString() == 'Invalid Date') ? '' : renewedDate.toISOString().slice(0, 10);
            let interests = me.getInterests();
            let membershipType = me.selectedMembershipType();

            let membership : IMembership = {
                id     		   		: me.id(),
                invoicenumber  		: renewedDateString,
                reneweddate    		: me.reneweddate(),
                membershiptype 		: (membershipType) ? membershipType.Value : '',
                firstname      		: me.firstname(),
                lastname       		: me.lastname(),
                email          		: me.email(),
                address1            : me.address1(),
                address2            : me.address2(),
                city                : me.city(),
                state               : me.state(),
                postalcode          : me.postalcode(),
                groupname           : me.groupname(),
                groupwebsite        : me.groupwebsite(),
                volunteerinterests  : interests,
                paymentmethod       : me.paymentmethod(),
                paymentreceiveddate : me.paymentreceiveddate(),
                neworrenewal        : me.neworrenewal(),
                amount              : me.amount(),
                ideas               : me.ideas(),
                notes               : me.notes(),
                paypalmemo          : me.paypalmemo(),
                phone               : me.phone()
            };

            return membership;
        };


        public validate = ():boolean => {
            let me = this;
            me.clearErrors();
            let valid = true;

            let value = me.firstname();
            if (!value) {
                me.membershipFirstNameError(': First and last name are required');
                valid = false;
            }
            value = me.lastname();
            if (!value) {
                me.membershipLastNameError(': First and last name are required');
                valid = false;
            }

            let amount = me.amount() || '';
            amount = amount.trim();
            if (amount) {
                if (amount !== '') {
                    let n = Number(amount);
                    let s = n.toString();
                    if (n.toString() == 'NaN') {
                        me.paymentAmountError(': Please enter a valid amount or leave blank.');
                        valid = false;
                    }
                }
            }

            value = me.reneweddate();
            value = value.trim();
            if (value) {
                let d = new Date(value);
                let result = d.toString();
                if (result == 'Invalid Date') {
                    me.membershipDateError(': This is not a vaild date.');
                    valid = false;
                }
            }
            else {
                me.membershipDateError(': Please membership renewed date.');
                valid = false;
            }

            let paymentdate = me.paymentreceiveddate();
            paymentdate = paymentdate.trim();
            if (paymentdate) {
                let d = new Date(paymentdate);
                paymentdate = d.toString();
                if (paymentdate == 'Invalid Date') {
                    me.membershipDateError(': This is not a vaild date.');
                    valid = false;
                }
            }

            if (valid) {
                if (amount == '' && paymentdate != '') {
                    me.paymentAmountError(': Please enter payment amount.');
                    valid = false;
                }
                else if (amount != '' && paymentdate == '') {
                    me.paymentDateError(': Please enter date of payment.')
                    valid = false;
                }
            }

            me.hasErrors(!valid);
            return valid;
        };


        public mainTab = () => {
            let me = this;
            me.activeTab('main');
        };
        public notesTab = () => {
            let me = this;
            me.activeTab('notes');
        };
        public interestsTab = () => {
            let me = this;
            me.activeTab('interests');
        };
        public mainViewTab = () => {
            let me = this;
            me.viewTab('main');
        };
        public notesViewTab = () => {
            let me = this;
            me.viewTab('notes');
        };
        public interestsViewTab = () => {
            let me = this;
            me.viewTab('interests');
        };
    }



    export class MembershipsViewModel implements IMainViewModel {
        static instance: Tops.MembershipsViewModel;
        private application: Tops.IPeanutClient;
        private peanut: Tops.Peanut;

        private membershipDeleteId = 0;
        editMode = ko.observable('none');
        membershipForm = new membershipObservable();
        memberships = ko.observableArray<IMembershipListItem>();
        years = ko.observableArray<string>();
        selectedYear = ko.observable('');
        userCanEdit = ko.observable(false);
        yearFilter : any = null;
        membershipsMessage = ko.observable('');
        confirmDeleteText = ko.observable('');

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
            jQuery(function () {
                jQuery(".datepicker").datepicker(
                    {
                        changeYear: true,
                        dateFormat: 'yy-mm-dd',
                        yearRange: 'c-20:c+20'
                    }
                );
            });
            me.application.initialize(
                function() {
                    // do view model initializations here.
                    jQuery("#memberships-view-container").hide();
                    // do view model initializations here.
                    me.application.hideServiceMessages();
                    me.application.showWaiter('Getting membership list...');
                    me.peanut.executeService('membership\\InitMembershipList',null,
                        function(serviceResponse: IServiceResponse) {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                me.userCanEdit(serviceResponse.Value.canEdit);
                                me.setupLists(serviceResponse.Value);
                                me.membershipForm.membershiptypes(serviceResponse.Value.membershiptypes);


                                me.application.loadComponent('modal-confirm', successFunction);
                                jQuery("#memberships-view-container").show();
                            }
                        }
                    ).always(function() {
                        me.application.hideWaiter();
                    });

                }
            );
        }

        setMembershipsList(list : IMembershipListItem[]) {
            let me = this;
            me.memberships(list);
            let message = (list.length > 0) ? '' : 'No memberships received for ' + me.selectedYear();
            me.membershipsMessage(message);
        }

        setupLists = (response : IMembershipListResponse) => {
            let me = this;
            if (me.yearFilter) {
                me.yearFilter.dispose();
            }
            response.yearlist.push('All years');
            me.years(response.yearlist);
            if (response.year) {
                me.selectedYear(response.year);
            }
            else {
                me.selectedYear('All years');
            }

            me.setMembershipsList(response.memberships);

            me.yearFilter = me.selectedYear.subscribe(me.selectYear);
        };

        selectYear = (year: string) => {
            let me = this;
            me.membershipsMessage('');
            me.application.hideServiceMessages();
            me.application.showWaiter('Getting membership list for '+year+'...');
            me.peanut.executeService('membership\\GetMembershipList',{'year' : year},
                function(serviceResponse: IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.setMembershipsList(serviceResponse.Value);

                    }
                }
            ).always(function() {
                me.application.hideWaiter();
            });
        };

        showMembership = (membership : IMembershipListItem) => {
            let me = this;
            me.application.hideServiceMessages();
            me.application.showWaiter('Getting membership #'+membership.invoicenumber);
            me.peanut.executeService('membership\\GetMembership',{'membershipId' : membership.id},
                function(serviceResponse: IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.membershipForm.assign(serviceResponse.Value);
                        me.membershipForm.viewState('view');
                        jQuery("#membership-detail-modal").modal('show');
                    }
                }
            ).always(function() {
                me.application.hideWaiter();
            });

        };
        updateMembership = () => {
            let me = this;
            if (me.membershipForm.validate()) {
                let membership = me.membershipForm.getMembership();
                me.membershipsMessage('');
                let request = {
                    membership:  membership,
                    year: me.selectedYear()
                };

                me.application.hideServiceMessages();
                me.application.showWaiter(
                    membership.id ?
                        'Updating membership #'+membership.invoicenumber :
                        'Adding membership');

                me.peanut.executeService('membership\\UpdateMembership', request,
                    function(serviceResponse: IServiceResponse) {
                        if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                            me.setupLists(serviceResponse.Value);
                            me.membershipForm.viewState('view');
                        }
                    }
                ).always(function() {
                    me.application.hideWaiter();
                });

                me.membershipForm.viewState('view');
            }


        };
        createMembership = () => {
            let me = this;
            me.membershipForm.clear();
            me.editMode('Add');
            me.membershipForm.formTitle('New Membership');
            me.membershipForm.viewState('edit');
        };

        editMembership = () => {
            let me = this;
            let test = me.membershipForm.firstname();
            jQuery("#membership-detail-modal").modal('hide');
            me.editMode('Update');
            me.membershipForm.viewState('edit');
            me.membershipForm.activeTab('main');
        };
        cancelEdit = () => {
            let me = this;
            me.membershipForm.viewState('view');
        };

        confirmDeleteMembership  = (membership : IMembershipListItem) => {
            let me = this;
            if (membership) {
                me.membershipDeleteId = membership.id;
                me.confirmDeleteText('Delete membership renewed or started on on '+membership.reneweddate+' from '+membership.firstname+' '+membership.lastname+'?');
                jQuery("#confirm-delete-modal").modal('show');
            }
        };

        deleteMembership = () => {
            let me = this;
            jQuery("#confirm-delete-modal").modal('hide');
            let membershipId = me.membershipDeleteId;
            me.application.hideServiceMessages();
            me.application.showWaiter('Deleting membership '+ membershipId);
            me.peanut.executeService('membership\\DeleteMembership',{'membershipId' : membershipId, 'year' : me.selectedYear()},
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
