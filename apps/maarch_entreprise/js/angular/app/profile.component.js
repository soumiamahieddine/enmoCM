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
var ProfileComponent = (function () {
    function ProfileComponent(http) {
        this.http = http;
        this.user = {};
        this.loading = false;
    }
    ProfileComponent.prototype.prepareProfile = function () {
        $j('#inner_content').remove();
        $j('#menunav').hide();
        $j('#container').width("99%");
    };
    ProfileComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.prepareProfile();
        this.loading = true;
        this.http.get('index.php?display=true&page=initializeJsGlobalConfig')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.coreUrl = data.coreurl;
            _this.http.get(_this.coreUrl + 'rest/user/profile')
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.user = data;
                _this.loading = false;
            });
        });
    };
    return ProfileComponent;
}());
ProfileComponent = __decorate([
    core_1.Component({
        templateUrl: 'js/angular/app/Views/profile.html',
    }),
    __metadata("design:paramtypes", [http_1.Http])
], ProfileComponent);
exports.ProfileComponent = ProfileComponent;
