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
var http_1 = require("@angular/common/http");
var HeaderComponent = (function () {
    function HeaderComponent(http) {
        this.http = http;
        this.applicationName = "";
        this.adminList = [];
        this.adminListModule = [];
        this.menuList = [];
        this.profilList = [];
        this.notifList = [];
    }
    HeaderComponent.prototype.prepareHeader = function () {
        $j('#maarch_content').remove();
    };
    HeaderComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.prepareHeader();
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + 'rest/administration')
            .subscribe(function (data) {
            _this.menuList = data.menu.menuList;
            _this.applicationName = data.menu.applicationName[0];
            _this.adminList = data.application;
            _this.adminListModule = data.modules;
        });
        this.profilList = [
            {
                label: 'Mon profil',
                link: '/profile',
                style: 'fa-user'
            },
            { label: 'Déconnexion',
                link: '/logout',
                style: 'fa-sign-out'
            }
        ];
    };
    return HeaderComponent;
}());
HeaderComponent = __decorate([
    core_1.Component({
        selector: 'menu-app',
        templateUrl: angularGlobals["headerView"],
        styleUrls: [
            'css/header.component.css',
        ]
    }),
    __metadata("design:paramtypes", [http_1.HttpClient])
], HeaderComponent);
exports.HeaderComponent = HeaderComponent;
