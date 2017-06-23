"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __metadata = (this && this.__metadata) || function (k, v) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(k, v);
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = require("@angular/core");
var http_1 = require("@angular/http");
require("rxjs/add/operator/map");
var StateEditionComponent = (function () {
    function StateEditionComponent(http, zone) {
        var _this = this;
        this.http = http;
        this.zone = zone;
        this.user = {
            lang: {}
        };
        this.passwordModel = {
            currentPassword: "",
            newPassword: "",
            reNewPassword: "",
        };
        this.signatureModel = {
            base64: "",
            base64ForJs: "",
            name: "",
            type: "",
            size: 0,
            label: "",
        };
        this.mailSignatureModel = {
            selected: 0,
            htmlBody: "",
            title: "",
        };
        this.showPassword = false;
        this.selectedSignature = -1;
        this.selectedSignatureLabel = "";
        this.resultInfo = "";
        this.loading = false;
        window['angularProfileComponent'] = {
            componentAfterUpload: function (base64Content) { return _this.processAfterUpload(base64Content); },
        };
    }
    return StateEditionComponent;
}());
StateEditionComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals.profileView,
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'js/angular/app/Css/profile.component.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, core_1.NgZone])
], StateEditionComponent);
exports.StateEditionComponent = StateEditionComponent;
