/**
 * Created by Terry on 2/21/2017.
 */
/**
 * Created by Terry on 3/17/2015.
 */

/// <reference path='../typings/jquery/jquery.d.ts' />
/// <reference path="../typings/knockout/knockout.d.ts" />
/// <reference path="../typings/custom/head.load.d.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.App/App.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.Peanut/Peanut.ts" />
/// <reference path="../../../packages/knockout_view/js/Tops.Peanut/Peanut.d.ts" />
/// <reference path="../typings/aftm/filelist.d.ts" />
module Tops {
    export class NewslettersViewModel implements IMainViewModel {
        static instance: Tops.NewslettersViewModel;
        private application: Tops.IPeanutClient;
        private peanut: Tops.Peanut;

        docUrl : KnockoutObservable<string> = ko.observable('');
        files : KnockoutObservableArray<IFileItem> = ko.observableArray([]);
        selectedDoc =  ko.observable();
        selectSubscription : any;

        // Constructor
        constructor() {

            var me = this;
            Tops.NewslettersViewModel.instance = me;
            me.application = new Tops.Application(me);
            me.peanut = me.application.peanut;
        }

        selectFile = (item: IFileItem) => {
            var me = this;
            if (item) {
                let src = 'https://docs.google.com/viewer?url=' + item.url + '&embedded=true';
                me.docUrl(src);
            }
        };

        selectDoc = () => {
            var me = this;
            me.selectFile(<IFileItem>me.selectedDoc());
        };

        getFileList(successFunction?: () => void) {

            var me  = this;

            let params = {'fileset' : 'Newsletters' };

            me.peanut.executeService('GetDocumentList',params, function(serviceResponse: Tops.IServiceResponse) {
                me.application.hideWaiter();
                me.docUrl('');
                if (serviceResponse.Result == Tops.Peanut.serviceResultSuccess) {
                    if (me.selectSubscription) {
                        me.selectSubscription.dispose();
                    }
                    me.files(serviceResponse.Value.reverse());
                    me.selectSubscription = me.selectedDoc.subscribe(me.selectFile);
                    if (successFunction) {
                        successFunction();
                    }
                }
            }).fail(function() {
                alert('Failed');
            });
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
                    me.getFileList(successFunction);
                }
            );
        }
    }
}

Tops.NewslettersViewModel.instance = new Tops.NewslettersViewModel();
(<any>window).NewslettersViewModel = Tops.NewslettersViewModel.instance;
