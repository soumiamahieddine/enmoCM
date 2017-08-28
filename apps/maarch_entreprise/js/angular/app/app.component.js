"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = require("@angular/core");
var AppComponent = (function () {
    function AppComponent() {
    }
    return AppComponent;
}());
AppComponent = __decorate([
    core_1.Component({
        selector: 'my-app',
        //template: `<menu-app></menu-app><router-outlet></router-outlet>`,
        template: "<router-outlet></router-outlet>",
        encapsulation: core_1.ViewEncapsulation.None,
        styleUrls: [
            '../../node_modules/bootstrap/dist/css/bootstrap.min.css',
            '../../node_modules/@angular/material/prebuilt-themes/indigo-pink.css',
            'css/engine.css',
            'css/jstree-custom.min.css' //treejs module
        ]
    })
], AppComponent);
exports.AppComponent = AppComponent;
