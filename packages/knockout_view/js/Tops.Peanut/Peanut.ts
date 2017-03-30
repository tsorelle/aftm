/**
 * Created by Terry on 2/19/2015.
 */

///<reference path='../../../../application/mvvm/typings/knockout/knockout.d.ts' />
///<reference path='../../../../application/mvvm/typings/jquery/jquery.d.ts' />
///<reference path='Peanut.d.ts' />
///<reference path='Debugging.ts' />
module Tops {
    export class KeyValueDTO implements Tops.INameValuePair {
        public Name: string;
        public Value: string;
    }

    /**
     * Constants for scym entities editState
     */
    export class editState {
        public static unchanged : number = 0;
        public static created : number = 1;
        public static updated : number = 2;
        public static deleted : number = 3;
    }

    /**
     * Use for testing. Normally IServiceResponse is returned from a service
     */
    export class fakeServiceResponse implements IServiceResponse {
        constructor(returnValue: any) {
            var me=this;
            me.Value = returnValue;
            me.Data = returnValue;
        }

        Messages: IServiceMessage[] = [];
        Result: number = 0;
        Value: any;
        Data: any;
    }

    export class HttpRequestVars {
        private static instance : HttpRequestVars;
        private requestVars = [];

        constructor() {
            var me = this;
            // var href = window.location.href;
            var queryString = window.location.search;
            var params = queryString.slice(queryString.indexOf('?') + 1).split('&');
            for (var i = 0; i < params.length;i++) {
                var parts = params[i].split('=');
                var key = parts[0];
                me.requestVars.push(key);
                me.requestVars[key] = parts[1];
            }
        }

        public getValue(key: string) {
            var me = this;
            var value = me.requestVars[key];
            if (value) {
                return value;
            }
            return null;
        }

        public static Get(key : string, defaultValue : any = null) {
            if (!HttpRequestVars.instance) {
                HttpRequestVars.instance = new HttpRequestVars();
            }
            var result = HttpRequestVars.instance.getValue(key);
            return (result === null) ? defaultValue : result;
        }
    }


    export class Peanut {

        public static debugging() {
            if (Tops.Debugging) {
                return Tops.Debugging.isEnabled();
            }
            return false;
        }

        constructor(public clientApp: IPeanutClient) {
            var me = this;
            me.securityToken = me.readSecurityToken();
        }
        // private foo: any;
        // private serviceType: string = 'php';

        static allMessagesType: number = -1;
        static infoMessageType: number = 0;
        static errorMessageType: number = 1;
        static warningMessageType: number = 2;

        static serviceResultSuccess: number = 0;
        static serviceResultPending: number = 1;
        static serviceResultWarnings: number = 2;
        static serviceResultErrors: number = 3;
        static serviceResultServiceFailure: number = 4;
        static serviceResultServiceNotAvailable: number = 5;

        securityToken: string = '';

        errorInfo = '';

        readSecurityToken() {
            var cookie = document.cookie;
            if (cookie) {
                var match = cookie.match(new RegExp('peanutSecurity=([^;]+)'));
                if (match) {
                    return match[1];
                }
            }
            return '';
        }

        parseErrorResult(result: any): string {
            let me = this;
            var errorDetailLevel = 4; // verbosity control to be implemented later
            var responseText = "An unexpected system error occurred.";
            try {
                // WCF returns a big whopping HTML page.  Could add code later to parse it but for now, just status info.
                if (result.status) {
                    if (result.status == '404') {
                        return responseText + " The web service was not found.";
                    }
                    else {
                        responseText = responseText + " Status: " + result.status;
                        if (result.statusText)
                            responseText = responseText + " " + result.statusText
                    }
                }
            }
            catch (ex) {
                responseText = responseText + " Error handling failed: " + ex.toString;
            }
            return responseText;

        }

        setSecurityToken(token: string) {
            this.securityToken = token;
        }
        getInfoMessages(messages: IServiceMessage[]): string[]{
            var _peanut = this;
            // var me = this;
            var result = new Array<string>();

            var j = 0;
            for (var i = 0; i < messages.length; i++) {
                var message = messages[i];
                if (message.MessageType == Tops.Peanut.infoMessageType)
                    result[j++] = message.Text;
            }

            return result;
        }


        getNonErrorMessages(messages: IServiceMessage[]): string[] {
            var _peanut = this;
            var me = this;
            var result = new Array<string>();

            var j = 0;
            for (var i = 0; i < messages.length; i++) {
                var message = messages[i];
                if (message.MessageType != Tops.Peanut.errorMessageType)
                    result[j++] = message.Text;
            }

            return result;
        }


        getErrorMessages(messages: IServiceMessage[]): string[] {
            var _peanut = this;
            var result = new Array<string>()

            var j = 0;
            for (var i = 0; i < messages.length; i++) {
                var message = messages[i];
                if (message.MessageType == Tops.Peanut.errorMessageType)
                    result[j++] = message.Text;
            }

            return result;
        }


        getMessagesText(messages: IServiceMessage[]): string[] {
            var result = new Array<string>();
            var j = 0;
            for (var i = 0; i < messages.length; i++) {
                var message = messages[i];
                result[j++] = message.Text;
            }
            return result;
        }


        hideServiceMessages(): void {
            var _peanut = this;
            if (_peanut.clientApp.viewModel) {
                var viewModel: any = _peanut.clientApp.viewModel;
                if (typeof (viewModel.hideServiceMessages) !== undefined && viewModel.hideServiceMessages != null) {
                    viewModel.hideServiceMessages();
                    return;
                }
            }

            _peanut.clientApp.hideServiceMessages();
        }

        showServiceMessages(serviceResponse: IServiceResponse): void {
            var _peanut = this;
            if (serviceResponse == null || serviceResponse.Messages == null || serviceResponse.Messages.length == 0)
                return;

            // var vm = _peanut.getCurrentViewModel();
            if (_peanut.clientApp.viewModel) {
                var viewModel: any = _peanut.clientApp.viewModel;

                if (typeof (viewModel.showServiceMessages) !== undefined && viewModel.showServiceMessages != null) {
                    viewModel.showServiceMessages(serviceResponse.Messages);
                    return;
                }
            }

            _peanut.clientApp.showServiceMessages(serviceResponse.Messages);
        }

        handleServiceResponse(serviceResponse: IServiceResponse): boolean {
            var _peanut = this;
            _peanut.showServiceMessages(serviceResponse);
            return true;
        }

        showExceptionMessage(errorResult: any): string {
            var _peanut = this;
            var errorMessage = _peanut.parseErrorResult(errorResult);
            _peanut.clientApp.showError(errorMessage);
            return errorMessage;
        }


        getSecurityToken(successFunction?: (serviceResponse: IServiceResponse) => void) {
            var _peanut = this;
            var serviceRequest = { "serviceCode" : 'getxsstoken'};
            var serviceUrl =  _peanut.clientApp.serviceUrl; // Drupal 8/7: tops/service, Drupal 6 or PHP: 'topsService.php';
            var result =
                jQuery.ajax({
                    type: "POST",
                    data: serviceRequest,
                    dataType: "json",
                    cache: false,
                    url: serviceUrl
                })
                .done(
                    successFunction
                );

            return result;
        }

        executeRPC(requestMethod: string, serviceName: string, parameters: any = "",
                       successFunction?: (serviceResponse: IServiceResponse) => void,
                       errorFunction?: (errorMessage: string) => void) : JQueryPromise<any> {
            var _peanut = this;

            _peanut.errorInfo = '';

            // peanut controller requires parameter as a string.
            if (!parameters)
                parameters = "";
            else  {
                parameters = JSON.stringify(parameters);
            }

            //todo: implement security token
            var serviceRequest = { "serviceCode" : serviceName,
                "topsSecurityToken": _peanut.securityToken,
                "request" : parameters};

            var serviceUrl =  _peanut.clientApp.serviceUrl; // Drupal 8/7: tops/service, Drupal 6 or PHP: 'topsService.php';

            var result =
                jQuery.ajax({
                    type: requestMethod, // "POST",
                    data: serviceRequest,
                    dataType: "json",
                    cache: false,
                    url: serviceUrl
                })
                    .done(
                    function(serviceResponse) {
                        _peanut.showServiceMessages(serviceResponse);
                        if (successFunction) {
                            successFunction(serviceResponse);
                        }
                    }
                )
                    .fail(
                    function(jqXHR, textStatus ) {
                        var errorMessage = _peanut.showExceptionMessage(jqXHR);
                        _peanut.errorInfo = (jqXHR) ? jqXHR.responseText : '';
                        if (errorFunction) {
                            errorFunction(errorMessage);
                        }
                    });


            return result;
        }


        // Execute a peanut service and handle Service Response.
        executeService(serviceName: string, parameters: any = "",
                       successFunction?: (serviceResponse: IServiceResponse) => void,
                       errorFunction?: (errorMessage: string) => void) : JQueryPromise<any> {
            var _peanut = this;
            return _peanut.executeRPC("POST", serviceName, parameters, successFunction, errorFunction);
        }

        // GET is no longer supported. This method is for backward compatibility but is identical to execute service
        getFromService(serviceName: string, parameters: any = "",
                       successFunction?: (serviceResponse: IServiceResponse) => void,
                       errorFunction?: (errorMessage: string) => void) : JQueryPromise<any> {
            var _peanut = this;
            return _peanut.executeRPC("POST", serviceName, parameters, successFunction, errorFunction);
        }

        /*
         * Utility routines
         */

        getRequestParam(name){
            if(name=(new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search))
                return decodeURIComponent(name[1]);
            return null;
        }

        public static ValidateEmail(email: string) {
            if (!email || email.trim() == '') {
                return false;
            }
            return /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email);
        }

        public static validatePositiveWholeNumber(text: string,maxValue = null, emptyOk: boolean = true) {
            return Peanut.validateWholeNumber(text,maxValue,0,emptyOk);
        }

        public static validateWholeNumber(numberText: string, maxValue = null, minValue = null, emptyOk: boolean = true) {
            if (numberText == null) {
                numberText = '';
            }

            numberText = numberText + ' '; // convert to string to ensure .trim() works.
            var result = {
                errorMessage: '',
                text: numberText.trim(),
                value: 0,
            };

            var parts = result.text.split('.');
            if (parts.length > 1) {
                var fraction = parseInt(parts[1].trim());
                if (fraction != 0) {
                    result.errorMessage = 'Must be a whole number.';
                    return result;
                }
                else {
                    result.text = parts[0].trim();
                }
            }

            if (result.text == '') {
                if (!emptyOk) {
                    result.errorMessage = 'A number is required.'
                }
                return result;
            }

            result.value = parseInt(result.text);
            if (isNaN(result.value)) {
                result.errorMessage = 'Must be a valid whole number.';
            }
            else {
                if (minValue != null && result.value < minValue) {
                    if (minValue == 0) {
                        result.errorMessage = 'Must be a positive number';
                    }
                    else {
                        result.errorMessage =  'Must be greater than ' + minValue;
                    }
                }
                if (maxValue != null && result.value > maxValue) {
                    if (result.errorMessage) {
                        result.errorMessage += ' and less than ' + maxValue;
                    }
                    else {
                        result.errorMessage =  'Must be less than ' + maxValue;
                    }
                }
            }
            return result;
        }

        public static validateCurrency(value:any): any {
            if (!value) {
                return false;
            }
            if (typeof value == 'string') {
                value = value.replace(/\s+/g, '');
                value = value.replace(',', '');
                value = value.replace('$', '');
            }
            else {
                value = value.toString();
            }
            if (!value) {
                return false;
            }
            var parts = value.split('.');
            if (parts.length > 2) {
                return false;
            }
            if (!jQuery.isNumeric(parts[0])) {
                return false;
            }
            if (parts.length == 1) {
                return parts[0] + '.00';
            }

            if (!jQuery.isNumeric(parts[1])) {
                return false;
            }

            var result = Number(parts[0] + '.' + parts[1].substring(0, 2));
            if (isNaN(result)) {
                return false;
            }
        };

    }
}
