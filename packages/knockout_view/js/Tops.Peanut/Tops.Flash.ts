 ///<reference path="../../../../application/mvvm/typings/jquery/jquery.d.ts"/>
/**
 * Created by Terry on 3/8/2017.
 */
module Tops {
    export class Flash {
        private static swfobject = null;
        public static getSwfobject() {

        }
        public static getVersion() {

        }
        public static IsEnabled() : boolean {
            return false;
        }

        /*
        private static Version : string = null;



        private static formatDescription(d: any) {
            d = d.match(/[\d]+/g);
            d.length = 3;
            return d.join(".")
        }

        public static getVersion() {
            if (Tops.Flash.Version !== null) {
                if (navigator.plugins && navigator.plugins.length) {
                    var e = navigator.plugins["Shockwave Flash"];
                    e && (a = !0, e.description && (b = Tops.Flash.formatDescription(e.description)));
                    navigator.plugins["Shockwave Flash 2.0"] && (a = !0, b = "2.0.0.11")
                } else {
                    if (navigator.mimeTypes && navigator.mimeTypes.length) {
                        var f = navigator.mimeTypes["application/x-shockwave-flash"];
                        (a = f && f.enabledPlugin) && (b = Tops.Flash.formatDescription(f.enabledPlugin.description))
                    } else {
                        try {
                            var g = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7"),
                                a = !0,
                                b = Tops.Flash.formatDescription(g.GetVariable("$version"))
                        } catch (h) {
                            try {
                                g = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6"), a = !0, b = "6.0.21"
                            } catch (i) {
                                try {
                                    g = new ActiveXObject("ShockwaveFlash.ShockwaveFlash"), a = !0, b = Tops.Flash.formatDescription(g.GetVariable("$version"))
                                } catch (j) {}
                            }
                        }
                    }
                }
                Tops.Flash.Version = a ? '' : b;
            }
            return Tops.Flash.Version;
        }

        public static IsEnabled() : boolean {
            let v = Tops.Flash.getVersion();
            return (v !== '');
        }
         */

    }
}

if (Tops.Flash.IsEnabled()) {
    jQuery('.needs-flash').show();
    jQuery('.no-flash').hide();
}
else {
    jQuery('.needs-flash').hide();
    jQuery('.no-flash').show();
}

