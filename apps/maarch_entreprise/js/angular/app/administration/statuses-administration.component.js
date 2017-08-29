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
var StatusesAdministrationComponent = (function () {
    function StatusesAdministrationComponent(http, notify) {
        this.http = http;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.data = [];
        this.loading = false;
    }
    StatusesAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareStatus();
        this.loading = true;
        this.http.get(this.coreUrl + 'rest/administration/status')
            .subscribe(function (data) {
            _this.statusList = data.statusList;
            _this.data = _this.statusList;
            setTimeout(function () {
                $j("[md2sortby='label_status']").click();
            }, 0);
            _this.updateBreadcrumb(angularGlobals.applicationName);
            _this.loading = false;
        }, function (err) {
            _this.notify.error(JSON.parse(err._body).errors);
        });
    };
    StatusesAdministrationComponent.prototype.prepareStatus = function () {
        $j('#inner_content').remove();
    };
    StatusesAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > " +
            "<a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " + this.lang.statuses;
    };
    StatusesAdministrationComponent.prototype.deleteStatus = function (status) {
        var _this = this;
        var resp = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + status.id + ' »');
        if (resp) {
            this.http.delete(this.coreUrl + 'rest/status/' + status.identifier)
                .subscribe(function (data) {
                _this.data = data.statuses;
                _this.notify.success(_this.lang.statusDeleted + ' « ' + status.id + ' »');
            }, function (err) {
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
    };
    return StatusesAdministrationComponent;
}());
StatusesAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals['statuses-administrationView'],
        styleUrls: [],
        providers: [notification_service_1.NotificationService]
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, notification_service_1.NotificationService])
], StatusesAdministrationComponent);
exports.StatusesAdministrationComponent = StatusesAdministrationComponent;
