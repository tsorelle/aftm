/**
 * Created by Terry on 10/29/2015.
 */
/// <reference path='../../../../application/mvvm/typings/jquery/jquery.d.ts' />
/// <reference path="../../../../application/mvvm/typings/knockout/knockout.d.ts" />
/// <reference path="../../../../application/mvvm/typings/custom/head.load.d.ts" />
var Tops;
(function (Tops) {
    var TkoComponentLoader = (function () {
        function TkoComponentLoader(applicationPath) {
            if (applicationPath === void 0) { applicationPath = ''; }
            this.applicationPath = applicationPath;
            this.components = [];
            TkoComponentLoader.instance = this;
        }
        TkoComponentLoader.addVM = function (componentName, vm) {
            TkoComponentLoader.instance.components[componentName] = vm;
        };
        TkoComponentLoader.prototype.getVM = function (componentName) {
            var me = this;
            return (componentName in me.components) ? me.components[componentName] : null;
        };
        TkoComponentLoader.prototype.nameToFileName = function (componentName) {
            var me = this;
            var parts = componentName.split('-');
            var fileName = parts[0];
            if (parts.length > 1) {
                fileName += parts[1].charAt(0).toUpperCase() + parts[1].substring(1);
            }
            // fileName += 'Component';
            return fileName;
        };
        TkoComponentLoader.prototype.alreadyLoaded = function (componentName) {
            var me = this;
            return (componentName in me.components);
        };
        // load component template, register to component name and vm instance
        TkoComponentLoader.prototype.loadComponentTemplate = function (componentName, htmlFileName, vmInstance, finalFunction) {
            var me = this;
            var htmlPath = me.applicationPath + 'templates/' + htmlFileName + '.html?tv=' + TkoComponentLoader.versionNumber;
            jQuery.get(htmlPath, function (htmlSource) {
                ko.components.register(componentName, {
                    viewModel: vmInstance,
                    template: htmlSource
                });
                if (finalFunction) {
                    finalFunction();
                }
            });
        };
        // load component source and template, create instance and register
        TkoComponentLoader.prototype.load = function (componentName, finalFunction) {
            var me = this;
            if (componentName in me.components) {
                // don't double load.
                if (finalFunction) {
                    finalFunction();
                }
                return;
            }
            var fileName = me.nameToFileName(componentName);
            var htmlPath = me.applicationPath + 'templates/' + fileName + '.html?tv=' +
                TkoComponentLoader.versionNumber;
            jQuery.get(htmlPath, function (htmlSource) {
                var src = me.applicationPath + 'components/' + fileName + 'Component.js?tv='
                    + TkoComponentLoader.versionNumber;
                head.load(src, function () {
                    var vm = me.getVM(componentName);
                    if (vm) {
                        ko.components.register(componentName, {
                            viewModel: vm,
                            template: htmlSource
                        });
                    }
                    if (finalFunction) {
                        finalFunction();
                    }
                });
            });
        };
        // load template, create instance and register.  Assumes source already load.
        TkoComponentLoader.prototype.registerComponent = function (componentName, vm, finalFunction) {
            var me = this;
            var fileName = me.nameToFileName(componentName);
            var htmlPath = me.applicationPath + 'templates/' + fileName + '.html?tv=' + TkoComponentLoader.versionNumber;
            jQuery.get(htmlPath, function (htmlSource) {
                // vmInstance can be a function returning the instance or the instance itself
                if (vm) {
                    ko.components.register(componentName, {
                        viewModel: { instance: vm },
                        template: htmlSource
                    });
                }
                if (finalFunction) {
                    finalFunction(vm);
                }
            });
        };
        // load template and register instance. Instance argumnet may be a function returning an instance or the instance itself.
        TkoComponentLoader.prototype.loadComponentInstance = function (name, vmInstance, //  getVmInstance : () => any,
            finalFunction) {
            var me = this;
            var fileName = me.nameToFileName(name);
            var htmlPath = me.applicationPath + 'templates/' + fileName + '.html?tv=' + TkoComponentLoader.versionNumber;
            jQuery.get(htmlPath, function (htmlSource) {
                var src = me.applicationPath + 'components/' + fileName + 'Component.js?tv=' + TkoComponentLoader.versionNumber;
                head.load(src, function () {
                    // vmInstance can be a function returning the instance or the instance itself
                    var vm = (jQuery.isFunction(vmInstance)) ? vmInstance() : vmInstance;
                    if (vm) {
                        ko.components.register(name, {
                            viewModel: { instance: vm },
                            template: htmlSource
                        });
                    }
                    if (finalFunction) {
                        finalFunction(vm);
                    }
                });
            });
        };
        return TkoComponentLoader;
    }());
    TkoComponentLoader.versionNumber = '0.0';
    Tops.TkoComponentLoader = TkoComponentLoader;
})(Tops || (Tops = {}));
// Tops.TkoComponentLoader.instance = new Tops.TkoComponentLoader();
// (<any>window).TkoComponents = Tops.TkoComponentLoader.instance; 
//# sourceMappingURL=TkoComponentLoader.js.map