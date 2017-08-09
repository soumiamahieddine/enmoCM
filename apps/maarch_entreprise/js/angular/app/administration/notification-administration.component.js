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
var router_1 = require("@angular/router");
var NotificationAdministrationComponent = (function () {
    function NotificationAdministrationComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.notification = {};
        this.loading = false;
        this.lang = translate_component_1.LANG;
    }
    NotificationAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareNotifications();
        this.loading = true;
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.creationMode = true;
                _this.loading = false;
            }
        });
    };
    NotificationAdministrationComponent.prototype.prepareNotifications = function () {
        $j('#inner_content').remove();
    };
    NotificationAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/notifications\"' style='cursor: pointer'>notifications</a>";
        }
    };
    return NotificationAdministrationComponent;
}());
NotificationAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["notification-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, router_1.ActivatedRoute, router_1.Router])
], NotificationAdministrationComponent);
exports.NotificationAdministrationComponent = NotificationAdministrationComponent;
