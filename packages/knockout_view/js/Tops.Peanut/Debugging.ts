/**
 * Created by Terry on 3/21/2015.
 */
module Tops {
    export class Debugging {
        private static isOn = true;
        public static isEnabled() {
            return Debugging.isOn;
        }
        public static Switch(value : boolean) {
            Debugging.isOn = value;
        }
    }
}