"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var lang_en_1 = require("../lang/lang-en");
var lang_fr_1 = require("../lang/lang-fr");
var dictionnary = {};
if (angularGlobals.lang == "en") {
    dictionnary = lang_en_1.LANG_EN;
}
else if (angularGlobals.lang == "fr") {
    dictionnary = lang_fr_1.LANG_FR;
}
exports.LANG = dictionnary;
