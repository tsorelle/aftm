/**
 * Created by Terry on 3/21/2015.
 */
var Tops;
(function (Tops) {
    var Debugging = (function () {
        function Debugging() {
        }
        Debugging.isEnabled = function () {
            return Debugging.isOn;
        };
        Debugging.Switch = function (value) {
            Debugging.isOn = value;
        };
        return Debugging;
    }());
    Debugging.isOn = true;
    Tops.Debugging = Debugging;
})(Tops || (Tops = {}));
//# sourceMappingURL=Debugging.js.map