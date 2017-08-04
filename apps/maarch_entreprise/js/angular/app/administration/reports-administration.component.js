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
var translate_component_1 = require("../translate.component");
var ReportsAdministrationComponent = (function () {
    function ReportsAdministrationComponent(http) {
        this.http = http;
        this.lang = translate_component_1.LANG;
        this.groups = [];
        this.reports = [];
        this.selectedGroup = "";
        this.arrayArgsPut = [];
    }
    ReportsAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Etats et edition";
        }
    };
    ReportsAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + 'rest/groups')
            .subscribe(function (data) {
            _this.groups = data['groups'];
        });
    };
    ReportsAdministrationComponent.prototype.loadReports = function () {
        var _this = this;
        this.http.get(this.coreUrl + 'rest/reports/groups/' + this.selectedGroup)
            .subscribe(function (data) {
            _this.reports = data['reports'];
        }, function (err) {
            errorNotification(err.error.errors);
        });
    };
    ReportsAdministrationComponent.prototype.onSubmit = function () {
        this.http.put(this.coreUrl + 'rest/reports/groups/' + this.selectedGroup, this.reports)
            .subscribe(function (data) {
            successNotification(data['success']);
        }, function (err) {
            errorNotification(err.error.errors);
        });
    };
    return ReportsAdministrationComponent;
}());
ReportsAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["reports-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.HttpClient])
], ReportsAdministrationComponent);
exports.ReportsAdministrationComponent = ReportsAdministrationComponent;
