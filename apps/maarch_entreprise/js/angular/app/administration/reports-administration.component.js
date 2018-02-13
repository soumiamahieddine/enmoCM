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
var notification_service_1 = require("../notification.service");
var ReportsAdministrationComponent = /** @class */ (function () {
    function ReportsAdministrationComponent(http, notify) {
        this.http = http;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.groups = [];
        this.reports = [];
        this.selectedGroup = "";
        this.loading = false;
        this.loadingOptions = false;
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
        this.loading = true;
        this.http.get(this.coreUrl + 'rest/reports/groups')
            .subscribe(function (data) {
            _this.groups = data['groups'];
            _this.loadReports(0);
            _this.loading = false;
        }, function () {
            location.href = "index.php";
        });
    };
    ReportsAdministrationComponent.prototype.loadReports = function (index) {
        var _this = this;
        this.selectedGroup = this.groups[index].group_id;
        this.loadingOptions = true;
        this.http.get(this.coreUrl + 'rest/reports/groups/' + this.groups[index].group_id)
            .subscribe(function (data) {
            _this.reports = data['reports'];
            _this.loadingOptions = false;
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    ReportsAdministrationComponent.prototype.saveReport = function () {
        var _this = this;
        this.http.put(this.coreUrl + 'rest/reports/groups/' + this.selectedGroup, this.reports)
            .subscribe(function () {
            _this.notify.success(_this.lang.modificationSaved);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    ReportsAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["reports-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [http_1.HttpClient, notification_service_1.NotificationService])
    ], ReportsAdministrationComponent);
    return ReportsAdministrationComponent;
}());
exports.ReportsAdministrationComponent = ReportsAdministrationComponent;
