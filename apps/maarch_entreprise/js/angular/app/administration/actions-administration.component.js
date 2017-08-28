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
var ActionsAdministrationComponent = (function () {
    function ActionsAdministrationComponent(http, notify) {
        this.http = http;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.search = null;
        this.actions = [];
        this.titles = [];
        this.loading = false;
        this.data = [];
    }
    ActionsAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Actions";
        }
    };
    ActionsAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.updateBreadcrumb(angularGlobals.applicationName);
        $j('#inner_content').remove();
        this.http.get(this.coreUrl + 'rest/administration/actions')
            .subscribe(function (data) {
            _this.actions = data['actions'];
            _this.data = _this.actions;
            _this.titles = data['titles'];
            _this.loading = false;
            setTimeout(function () {
                $j("[md2sortby='id']").click();
            }, 0);
        }, function (err) {
            console.log(err);
            location.href = "index.php";
        });
    };
    ActionsAdministrationComponent.prototype.deleteAction = function (id) {
        var _this = this;
        var r = confirm(this.lang.deleteMsg + ' ?');
        if (r) {
            this.http.delete(this.coreUrl + 'rest/actions/' + id)
                .subscribe(function (data) {
                _this.data = data.action;
                _this.notify.success(data.success);
            }, function (err) {
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
    };
    return ActionsAdministrationComponent;
}());
ActionsAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["actions-administrationView"],
        styleUrls: [],
        providers: [notification_service_1.NotificationService]
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, notification_service_1.NotificationService])
], ActionsAdministrationComponent);
exports.ActionsAdministrationComponent = ActionsAdministrationComponent;
