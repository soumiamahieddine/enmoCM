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
var router_1 = require("@angular/router");
var AdministrationComponent = (function () {
    function AdministrationComponent(http, router) {
        this.http = http;
        this.router = router;
        this.applicationServices = [];
        this.modulesServices = [];
        this.loading = false;
    }
    AdministrationComponent.prototype.prepareAdministration = function () {
        $j('#inner_content').remove();
        $j('#menunav').hide();
        $j('#divList').remove();
        $j('#magicContactsTable').remove();
        $j('#manageBasketsOrderTable').remove();
        $j('#controlParamTechnicTable').remove();
        $j('#container').width("99%");
        if ($j('#content h1')[0] && $j('#content h1')[0] != $j('my-app h1')[0]) {
            $j('#content h1')[0].remove();
        }
    };
    AdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > Administration";
        }
    };
    AdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.prepareAdministration();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get(this.coreUrl + 'rest/administration')
            .subscribe(function (data) {
            _this.applicationServices = data.application;
            _this.modulesServices = data.modules;
            _this.loading = false;
        });
    };
    AdministrationComponent.prototype.goToSpecifiedAdministration = function (service) {
        if (service.angular == "true") {
            this.router.navigate([service.servicepage]);
        }
        else {
            window.location.assign(service.servicepage);
        }
    };
    return AdministrationComponent;
}());
AdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals.administrationView,
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, router_1.Router])
], AdministrationComponent);
exports.AdministrationComponent = AdministrationComponent;
