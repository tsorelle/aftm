/**
 * Created by Terry on 2/19/2015.
 */
///<reference path='../../../../application/mvvm/typings/knockout/knockout.d.ts' />
///<reference path='../../../../application/mvvm/typings/jquery/jquery.d.ts' />
///<reference path='Peanut.d.ts' />
///<reference path='Debugging.ts' />
var Tops;
(function (Tops) {
    var KeyValueDTO = (function () {
        function KeyValueDTO() {
        }
        return KeyValueDTO;
    }());
    Tops.KeyValueDTO = KeyValueDTO;
    /**
     * Constants for scym entities editState
     */
    var editState = (function () {
        function editState() {
        }
        editState.unchanged = 0;
        editState.created = 1;
        editState.updated = 2;
        editState.deleted = 3;
        return editState;
    }());
    Tops.editState = editState;
    /**
     * Use for testing. Normally IServiceResponse is returned from a service
     */
    var fakeServiceResponse = (function () {
        function fakeServiceResponse(returnValue) {
            this.Messages = [];
            this.Result = 0;
            var me = this;
            me.Value = returnValue;
            me.Data = returnValue;
        }
        return fakeServiceResponse;
    }());
    Tops.fakeServiceResponse = fakeServiceResponse;
    var HttpRequestVars = (function () {
        function HttpRequestVars() {
            this.requestVars = [];
            var me = this;
            // var href = window.location.href;
            var queryString = window.location.search;
            var params = queryString.slice(queryString.indexOf('?') + 1).split('&');
            for (var i = 0; i < params.length; i++) {
                var parts = params[i].split('=');
                var key = parts[0];
                me.requestVars.push(key);
                me.requestVars[key] = parts[1];
            }
        }
        HttpRequestVars.prototype.getValue = function (key) {
            var me = this;
            var value = me.requestVars[key];
            if (value) {
                return value;
            }
            return null;
        };
        HttpRequestVars.Get = function (key, defaultValue) {
            if (defaultValue === void 0) { defaultValue = null; }
            if (!HttpRequestVars.instance) {
                HttpRequestVars.instance = new HttpRequestVars();
            }
            var result = HttpRequestVars.instance.getValue(key);
            return (result === null) ? defaultValue : result;
        };
        return HttpRequestVars;
    }());
    Tops.HttpRequestVars = HttpRequestVars;
    var Peanut = (function () {
        function Peanut(clientApp) {
            this.clientApp = clientApp;
            this.securityToken = '';
            this.errorInfo = '';
            var me = this;
            me.securityToken = me.readSecurityToken();
        }
        Peanut.debugging = function () {
            if (Tops.Debugging) {
                return Tops.Debugging.isEnabled();
            }
            return false;
        };
        Peanut.prototype.readSecurityToken = function () {
            var cookie = document.cookie;
            if (cookie) {
                var match = cookie.match(new RegExp('peanutSecurity=([^;]+)'));
                if (match) {
                    return match[1];
                }
            }
            return '';
        };
        Peanut.prototype.parseErrorResult = function (result) {
            var me = this;
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
                            responseText = responseText + " " + result.statusText;
                    }
                }
            }
            catch (ex) {
                responseText = responseText + " Error handling failed: " + ex.toString;
            }
            return responseText;
        };
        Peanut.prototype.setSecurityToken = function (token) {
            this.securityToken = token;
        };
        Peanut.prototype.getInfoMessages = function (messages) {
            var _peanut = this;
            // var me = this;
            var result = new Array();
            var j = 0;
            for (var i = 0; i < messages.length; i++) {
                var message = messages[i];
                if (message.MessageType == Tops.Peanut.infoMessageType)
                    result[j++] = message.Text;
            }
            return result;
        };
        Peanut.prototype.getNonErrorMessages = function (messages) {
            var _peanut = this;
            var me = this;
            var result = new Array();
            var j = 0;
            for (var i = 0; i < messages.length; i++) {
                var message = messages[i];
                if (message.MessageType != Tops.Peanut.errorMessageType)
                    result[j++] = message.Text;
            }
            return result;
        };
        Peanut.prototype.getErrorMessages = function (messages) {
            var _peanut = this;
            var result = new Array();
            var j = 0;
            for (var i = 0; i < messages.length; i++) {
                var message = messages[i];
                if (message.MessageType == Tops.Peanut.errorMessageType)
                    result[j++] = message.Text;
            }
            return result;
        };
        Peanut.prototype.getMessagesText = function (messages) {
            var result = new Array();
            var j = 0;
            for (var i = 0; i < messages.length; i++) {
                var message = messages[i];
                result[j++] = message.Text;
            }
            return result;
        };
        Peanut.prototype.hideServiceMessages = function () {
            var _peanut = this;
            if (_peanut.clientApp.viewModel) {
                var viewModel = _peanut.clientApp.viewModel;
                if (typeof (viewModel.hideServiceMessages) !== undefined && viewModel.hideServiceMessages != null) {
                    viewModel.hideServiceMessages();
                    return;
                }
            }
            _peanut.clientApp.hideServiceMessages();
        };
        Peanut.prototype.showServiceMessages = function (serviceResponse) {
            var _peanut = this;
            if (serviceResponse == null || serviceResponse.Messages == null || serviceResponse.Messages.length == 0)
                return;
            // var vm = _peanut.getCurrentViewModel();
            if (_peanut.clientApp.viewModel) {
                var viewModel = _peanut.clientApp.viewModel;
                if (typeof (viewModel.showServiceMessages) !== undefined && viewModel.showServiceMessages != null) {
                    viewModel.showServiceMessages(serviceResponse.Messages);
                    return;
                }
            }
            _peanut.clientApp.showServiceMessages(serviceResponse.Messages);
        };
        Peanut.prototype.handleServiceResponse = function (serviceResponse) {
            var _peanut = this;
            _peanut.showServiceMessages(serviceResponse);
            return true;
        };
        Peanut.prototype.showExceptionMessage = function (errorResult) {
            var _peanut = this;
            var errorMessage = _peanut.parseErrorResult(errorResult);
            _peanut.clientApp.showError(errorMessage);
            return errorMessage;
        };
        Peanut.prototype.executeRPC = function (requestMethod, serviceName, parameters, successFunction, errorFunction) {
            if (parameters === void 0) { parameters = ""; }
            var _peanut = this;
            _peanut.errorInfo = '';
            // peanut controller requires parameter as a string.
            if (!parameters)
                parameters = "";
            else {
                parameters = JSON.stringify(parameters);
            }
            //todo: implement security token
            var serviceRequest = { "serviceCode": serviceName,
                // "topsSecurityToken": _peanut.securityToken,
                "request": parameters };
            var serviceUrl = _peanut.clientApp.serviceUrl; // Drupal 8/7: tops/service, Drupal 6 or PHP: 'topsService.php';
            var result = jQuery.ajax({
                type: requestMethod,
                data: serviceRequest,
                dataType: "json",
                cache: false,
                url: serviceUrl
            })
                .done(function (serviceResponse) {
                _peanut.showServiceMessages(serviceResponse);
                if (successFunction) {
                    successFunction(serviceResponse);
                }
            })
                .fail(function (jqXHR, textStatus) {
                var errorMessage = _peanut.showExceptionMessage(jqXHR);
                _peanut.errorInfo = (jqXHR) ? jqXHR.responseText : '';
                if (errorFunction) {
                    errorFunction(errorMessage);
                }
            });
            return result;
        };
        // Execute a peanut service and handle Service Response.
        Peanut.prototype.executeService = function (serviceName, parameters, successFunction, errorFunction) {
            if (parameters === void 0) { parameters = ""; }
            var _peanut = this;
            return _peanut.executeRPC("POST", serviceName, parameters, successFunction, errorFunction);
        };
        // GET is no longer supported. This method is for backward compatibility but is identical to execute service
        Peanut.prototype.getFromService = function (serviceName, parameters, successFunction, errorFunction) {
            if (parameters === void 0) { parameters = ""; }
            var _peanut = this;
            return _peanut.executeRPC("POST", serviceName, parameters, successFunction, errorFunction);
        };
        /*
         * Utility routines
         */
        Peanut.prototype.getRequestParam = function (name) {
            if (name = (new RegExp('[?&]' + encodeURIComponent(name) + '=([^&]*)')).exec(location.search))
                return decodeURIComponent(name[1]);
            return null;
        };
        Peanut.ValidateEmail = function (email) {
            if (!email || email.trim() == '') {
                return false;
            }
            return /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email);
        };
        Peanut.validatePositiveWholeNumber = function (text, maxValue, emptyOk) {
            if (maxValue === void 0) { maxValue = null; }
            if (emptyOk === void 0) { emptyOk = true; }
            return Peanut.validateWholeNumber(text, maxValue, 0, emptyOk);
        };
        Peanut.validateWholeNumber = function (numberText, maxValue, minValue, emptyOk) {
            if (maxValue === void 0) { maxValue = null; }
            if (minValue === void 0) { minValue = null; }
            if (emptyOk === void 0) { emptyOk = true; }
            if (numberText == null) {
                numberText = '';
            }
            numberText = numberText + ' '; // convert to string to ensure .trim() works.
            var result = {
                errorMessage: '',
                text: numberText.trim(),
                value: 0
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
                    result.errorMessage = 'A number is required.';
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
                        result.errorMessage = 'Must be greater than ' + minValue;
                    }
                }
                if (maxValue != null && result.value > maxValue) {
                    if (result.errorMessage) {
                        result.errorMessage += ' and less than ' + maxValue;
                    }
                    else {
                        result.errorMessage = 'Must be less than ' + maxValue;
                    }
                }
            }
            return result;
        };
        Peanut.validateCurrency = function (value) {
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
        ;
        // private foo: any;
        // private serviceType: string = 'php';
        Peanut.allMessagesType = -1;
        Peanut.infoMessageType = 0;
        Peanut.errorMessageType = 1;
        Peanut.warningMessageType = 2;
        Peanut.serviceResultSuccess = 0;
        Peanut.serviceResultPending = 1;
        Peanut.serviceResultWarnings = 2;
        Peanut.serviceResultErrors = 3;
        Peanut.serviceResultServiceFailure = 4;
        Peanut.serviceResultServiceNotAvailable = 5;
        return Peanut;
    }());
    Tops.Peanut = Peanut;
})(Tops || (Tops = {}));
//# sourceMappingURL=Peanut.js.map