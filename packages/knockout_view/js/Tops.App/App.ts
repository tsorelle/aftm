/**
 * Created by Terry on 2/19/2015.
 */
/// <reference path="TkoComponentLoader.ts" />
///<reference path='../Tops.Peanut/Peanut.d.ts' />
/// <reference path="../Tops.Peanut/Peanut.ts" />
/// <reference path="../../../../application/mvvm/typings/bootstrap/bootstrap.d.ts" />
/// <reference path="../../../../application/mvvm/typings/custom/head.load.d.ts" />
/// <reference path='../../../../application/mvvm/typings/underscore/underscore.d.ts' />

module Tops {

    // constants for C5 / AFTM version.
    const corePath  = '/packages/knockout_view/js/';
    const appRootPath = '/application/mvvm/';
    // see routing declarations in /application/app.php
    const serviceUrlDefault: string = '/tops/service/execute/{sid}/{arg}';

    export class mailBox {
        id:string;
        name:string;
        description:string;
        code:string ;
        email:string;
        state:number;
    }

    export class waitMessage {
        private static waitDialog : any = null;
        private static waiterType : string = 'spin-waiter';
        private static templates = Array<string>();
        private static visible = false;

        public static addTemplate(templateName: string, content: string) {
            waitMessage.templates[templateName] = content;
        }


        public static setWaiterType(waiterType: string) {
            waitMessage.waiterType = waiterType;
            waitMessage.waitDialog = jQuery(waitMessage.templates[waiterType]);
            return waitMessage.waitDialog;
        }

        public static show(message: string = 'Please wait ...', waiterType : string = 'spin-waiter') {
            if (waitMessage.visible) {
                waitMessage.setMessage(message);
            }
            else   {
                var div = waitMessage.setWaiterType(waiterType);
                var span = div.find('#wait-message');
                span.text(message);
                div.modal();
                waitMessage.visible = true;
            }
        }

        public static setMessage(message: string) {
            if (waitMessage.waitDialog) {
                var span = waitMessage.waitDialog.find('#wait-message');
                span.text(message);
            }
        }

        public static setProgress(count: number, showLabel: boolean = false) {
            if (waitMessage.waiterType == 'progress-waiter') {
                var bar = waitMessage.waitDialog.find('#wait-progress-bar');
                var percent = count + '%';
                bar.css('width', percent);
                if (showLabel) {
                    bar.text(percent);
                }
            }
        }

        public static hide() {
            if (waitMessage.visible && waitMessage.waitDialog) {
                waitMessage.waitDialog.modal('hide');
                waitMessage.visible = false;
            }
        }
    }

    class messageManager implements IMessageManager {
        static instance:messageManager;

        static errorClass: string = "service-message-error";
        static infoClass: string = "service-message-information";
        static warningClass: string = "service-message-warning";

        public errorMessages = ko.observableArray([]);
        public infoMessages = ko.observableArray([]);
        public warningMessages = ko.observableArray([]);


        public addMessage = (message:string, messageType:number):void => {
            switch (messageType) {
                case Tops.Peanut.errorMessageType :
                    this.errorMessages.push({type: messageManager.errorClass, text: message});
                    break;
                case Tops.Peanut.warningMessageType:
                    this.warningMessages.push({type: messageManager.warningClass, text: message});
                    break;
                default :
                    this.infoMessages.push({type: messageManager.infoClass, text: message});
                    break;
            }
        };

        public setMessage = (message:string, messageType:number):void => {

            switch (messageType) {
                case Tops.Peanut.errorMessageType :
                    this.errorMessages([{type: messageManager.errorClass, text: message}]);
                    break;
                case Tops.Peanut.warningMessageType:
                    this.warningMessages([{type: messageManager.warningClass, text: message}]);
                    break;
                default :
                    this.infoMessages([{type: messageManager.infoClass, text: message}]);
                    break;
            }
        };

        public clearMessages = (messageType:number = Tops.Peanut.allMessagesType):void => {
            if (messageType == Tops.Peanut.errorMessageType || messageType == Tops.Peanut.allMessagesType) {
                this.errorMessages([]);
            }
            if (messageType == Tops.Peanut.warningMessageType || messageType == Tops.Peanut.allMessagesType) {
                this.warningMessages([]);
            }
            if (messageType == Tops.Peanut.infoMessageType || messageType == Tops.Peanut.allMessagesType) {
                this.infoMessages([]);
            }
        };

        public clearInfoMessages = () : void => {
            this.infoMessages([]);
        };

        public clearErrorMessages = () : void => {
            this.errorMessages([]);
        };
        public clearWarningMessages = () : void => {
            this.warningMessages([]);
        };

        public setServiceMessages = (messages:Tops.IServiceMessage[]):void => {
            var count = messages.length;
            var errorArray = [];
            var warningArray = [];
            var infoArray = [];
            for (var i = 0; i < count; i++) {
                var message = messages[i];
                switch (message.MessageType) {
                    case Tops.Peanut.errorMessageType :
                        errorArray.push({type: messageManager.errorClass, text: message.Text});
                        break;
                    case Tops.Peanut.warningMessageType:
                        warningArray.push({type: messageManager.warningClass, text: message.Text});
                        break;
                    default :
                        infoArray.push({type: messageManager.infoClass, text: message.Text});
                        break;
                }
            }
            this.errorMessages(errorArray);
            this.warningMessages(warningArray);
            this.infoMessages(infoArray);
        };
    }

    // Class
    export class Application implements Tops.IPeanutClient {

        static versionNumber = "1.0";
        constructor(currentViewModel: any) {
            var me = this;
            me.viewModel = currentViewModel;
            me.peanut = new Tops.Peanut(me);
            Application.current = me;
        }

        static current: Application;
        siteUrl :  string = '';
        applicationPath:  string = '';
        peanut: Tops.Peanut;
        viewModel: any;
        componentLoader: TkoComponentLoader = null;

        serviceUrl: string = serviceUrlDefault;

        public getHtmlTemplate(name: string, successFunction: (htmlSource: string) => void) {
            var parts = name.split('-');
            var fileName = parts[0] + parts[1].charAt(0).toUpperCase() + parts[1].substring(1);
            var htmlSource =  this.applicationPath +
                    'templates/' + fileName + '.html'
                    + '?tv=' + Application.versionNumber
                ;
            jQuery.get(htmlSource, successFunction);
        }

        private expandFileName(fileName: string ) {
            if (!fileName) {
                return '';
            }
            var fileExtension = fileName.substr((fileName.lastIndexOf('.') + 1));
            if (fileExtension) {
                switch (fileExtension.toLowerCase()) {
                    case 'css' :
                        return this.applicationPath + 'css/' + fileName
                            + '?tv=' + Application.versionNumber
                            ;
                    case 'js' :
                        return this.applicationPath + 'components/' + fileName
                            + '?tv=' + Application.versionNumber
                            ;
                }
            }
            return fileName;

        }
        public loadResources(names: any, successFunction?: () => void) {
            var me = this;
            var params : any = null;
            if (_.isArray(names)) {
                params = [];
                for(var i = 0; i < names.length; i++) {
                    var path = me.expandFileName(names[i]);
                    params.push(path);
                }
            }
            else {
                params = me.expandFileName(names);
            }
            head.load(params, successFunction);
        }

        public loadJS(names: any, successFunction?: () => void) {
            var params: any = null;
            if (_.isArray(names)) {
                params = [];
                for(var i = 0; i < names.length; i++) {
                    params.push(this.applicationPath + 'components/' + names[i]
                        + "?tv=" + Application.versionNumber
                    );
                }
            }
            else {
                params = names;
            }
            head.load(params, successFunction);
        }


        public loadCSS(name: string, successFunction?: () => void) {
            head.load(this.applicationPath + 'css/' + name
                + "?tv=" + Application.versionNumber
                , successFunction);
        }

        public usingComponentLoader(afterLoad: () => void) {
            var me = this;
            if (me.componentLoader) {
                afterLoad();
            }
            else
            {
                let url = me.siteUrl + corePath + 'Tops.App/TkoComponentLoader.js?tv=' + Application.versionNumber;
                head.load(url, function() {
                        me.componentLoader = new TkoComponentLoader(me.applicationPath);
                        TkoComponentLoader.versionNumber = Application.versionNumber;
                        afterLoad();
                    }
                );
            }

        }

        public componentIsLoaded(name : string) {
            var me = this;
            me.usingComponentLoader(function() {
                return me.componentLoader
            });

        }

        /**
         * load component source and template and register.
         * Use when multiple instances are reuqired.
         *
         * @param name
         * @param successFunction
         */
        public loadComponent(name: string, successFunction?: () => void) {
            var me = this;
            me.usingComponentLoader(function() {
                me.componentLoader.load(name, successFunction);
            });
        }

        /**
         * load component template, register to component name and vm instance
         * overrides naming conventions.
         *
         * @param componentName
         * @param htmlFileName
         * @param vmInstance
         * @param finalFunction
         */
        public loadComponentTemplate(componentName: string, htmlFileName: string, vmInstance: any, finalFunction: () => void  = null) {
            var me = this;
            me.usingComponentLoader(function() {
                me.componentLoader.loadComponentTemplate(componentName, htmlFileName, vmInstance, finalFunction)
            });
        }


        /**
         * load template and register instance. Instance argumnent may be a function returning an instance or the instance itself.
         *
         * @param name
         * @param vmInstance
         * @param finalFunction
         */
        public loadComponentInstance(name: string,
                                     vmInstance : any, // instance of VM or function returning the instance.
                                     finalFunction?: () => void) {
            var me = this;
            me.usingComponentLoader(function() {
                me.componentLoader.loadComponentInstance(name, vmInstance, finalFunction);
            });
        }

        /**
         * KnockoutJS databinding against single element
         *
         * @param containerName
         * @param context
         */
        public bindNode(containerName: string, context : any) {
            var me = this;
            if (context == null) {
                context = me.viewModel; // calling context
            }
            var container = document.getElementById(containerName); // messages-component-container
            ko.applyBindingsToNode(container,null,context);
        };


        /**
         * KnockoutJS databind against a DIV or other element, including descendants.
         *
         * @param containerName
         * @param context
         */
        public bindSection(containerName: string, context : any) {
            var me = this;
            if (context == null) {
                context = me.viewModel; // calling context
            }
            var container = document.getElementById(containerName); // messages-component-container
            if (container==null) {
                if (containerName) {
                    alert("Error: Container element '" + containerName + "' for section binding not found.");
                }
                else {
                    alert("Error: not container name for section binding.");
                }
                return;
            }
            ko.applyBindings(context,container);
            jQuery("#"+containerName).show();
        };

        /**
         * load template and register instance. Instance argumnent may be a function returning an instance or the instance itself.
         * Bind assuming outer div with id of "[component name]-container"
         *
         * @param name
         * @param vmInstance
         * @param finalFunction
         */
        public bindComponent = (name: string,
                                vmInstance : any, // instance of VM or function returning the instance.
                                finalFunction?: () => void) => {
            var me = this;
            me.usingComponentLoader(function() {
                if (me.componentLoader.alreadyLoaded(name)) {
                    if (finalFunction) {
                        finalFunction();
                    }
                }
                else {
                    me.componentLoader.loadComponentInstance(name, vmInstance, function(vm: any) {
                        me.bindSection(name + '-container', vm);
                        if (finalFunction) {
                            finalFunction();
                        }
                    });
                }
            });
        };

        public loadWaitMessageTemplate(templateName: string, successFunction: () => void) {
            this.getHtmlTemplate(templateName, function (htmlSource: string) {
                waitMessage.addTemplate(templateName, htmlSource);
                successFunction();
            });
        }

        static defaultSectionId : string = 'tops-view-section';

        public showDefaultSection() {
            var container = document.getElementById(Tops.Application.defaultSectionId); // messages-component-container
            jQuery("#"+Tops.Application.defaultSectionId).show();
        }
        public bindDefaultSection() {
            var me = this;
            me.bindSection(Tops.Application.defaultSectionId,me.viewModel);
        }

        /**
         * load template, create instance and register.  Assumes source already loaded.
         *
         * @param componentName
         * @param vm
         * @param finalFunction
         */
        public registerComponent(componentName: string, vm: any, finalFunction?: () => void) {
            var me = this;
            me.usingComponentLoader(function() {
                me.componentLoader.registerComponent(componentName, vm,finalFunction);
            });
        }

        /**
         *
         * @param componentName
         * @param vm
         * @param finalFunction
         *
         * Assumes vm source already loaded
         * load template, create instance and register.  Assumes source already load.
         * Bind assuming outer div with id of "[component name]-container"
         */
        public registerAndBindComponent(componentName: string, vm: any, finalFunction?: () => void) {
            var me = this;
            me.usingComponentLoader(function() {
                me.componentLoader.registerComponent(componentName, vm,
                    function () {
                        me.bindSection(componentName+'-container', messageManager.instance);
                        if (finalFunction) {
                            finalFunction();
                        }
                    });
            });
        }

        public initialize( successFunction?: () => void) {
            var me = this;
            me.setApplicationPath();
            me.serviceUrl = me.applicationPath +  me.serviceUrl;
            messageManager.instance = new messageManager();
            me.registerAndBindComponent('service-messages',messageManager.instance, function() {
                me.loadWaitMessageTemplate('spin-waiter', function () {
                    me.loadWaitMessageTemplate('progress-waiter', function () {
                        if (successFunction) {
                            successFunction();
                        }
                    })
                });
            });
        }

        setApplicationPath(): void {
            let me = this;
            let port = location.port;
            if ((!port) || port == '80') {
                port = '';
            }
            else {
                port = ':' + port;
            }
            me.siteUrl = location.protocol + '//' + location.hostname + port;
            me.applicationPath = me.siteUrl + appRootPath;
        }

        showServiceMessages(messages: Tops.IServiceMessage[]): void {
            messageManager.instance.setServiceMessages(messages);
        }

        hideServiceMessages(): void {
            messageManager.instance.clearMessages();
        }

        showError(errorMessage: string): void {
            // peanut uses this to display exceptions
            if (errorMessage) {
                messageManager.instance.addMessage(errorMessage,Peanut.errorMessageType);
            }
            else {
                messageManager.instance.clearMessages(Peanut.errorMessageType);
            }
        }

        showMessage(messageText: string): void {
            if (messageText) {
                messageManager.instance.addMessage(messageText,Peanut.infoMessageType);
            }
            else {
                messageManager.instance.clearMessages(Peanut.infoMessageType);
            }
        }

        showWarning(messageText: string): void {
            if (messageText) {
                messageManager.instance.addMessage(messageText,Peanut.warningMessageType);
            }
            else {
                messageManager.instance.clearMessages(Peanut.warningMessageType);
            }
        }

        // Application level message display functions
        setErrorMessage(messageText: string): void {
            if (messageText) {
                messageManager.instance.setMessage(messageText,Peanut.errorMessageType);
            }
            else {
                messageManager.instance.clearMessages(Peanut.errorMessageType);
            }
        }

        setInfoMessage(messageText: string): void {
            if (messageText) {
                messageManager.instance.setMessage(messageText,Peanut.infoMessageType);
            }
            else {
                messageManager.instance.clearMessages(Peanut.infoMessageType);
            }
        }

        setWarningMessage(messageText: string): void {
            if (messageText) {
                messageManager.instance.setMessage(messageText,Peanut.warningMessageType);
            }
            else {
                messageManager.instance.clearMessages(Peanut.infoMessageType);
            }
        }


        public showWaiter(message: string = "Please wait . . .") {
            waitMessage.show(message);
        }

        public hideWaiter() {
            waitMessage.hide();
        }

        public showProgress(message: string = "Please wait . . .") {
            waitMessage.show(message, 'progress-waiter');
        }

        public setProgress(count: number) {
            waitMessage.setProgress(count);
        }

        /*
         // static wait message example

         public showWaiter(message: string = "Please wait . . .") {
         $("#load-window-text").text(message);
         //Get the window height and width
         var winH = $(window).height();
         var winW = $(window).width();
         var dialogId = "#load-window";
         //Set the popup window to center
         $(dialogId).css('top', winH / 2 - $(dialogId).height() / 2);
         $(dialogId).css('left', winW / 2 - $(dialogId).width() / 2);

         $("#load-message").show();
         $("#modal-mask").show();
         $(dialogId).show();
         }

         public hideWaiter(timeout: number = 0) {
         $('#load-window').hide();
         $("#modal-mask").hide();
         $("#load-message").hide();
         }
         */
    }


}
