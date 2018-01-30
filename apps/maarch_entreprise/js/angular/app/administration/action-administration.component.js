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
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var ActionAdministrationComponent = /** @class */ (function () {
    function ActionAdministrationComponent(http, route, router, notify) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.action = {};
        this.statuses = [];
        this.actionPagesList = [];
        this.categoriesList = [];
        this.keywordsList = [];
        this.loading = false;
    }
    ActionAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/actions\"' style='cursor: pointer'>" + this.lang.actions + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.actionCreation;
        }
        else {
            breadCrumb += this.lang.actionModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    };
    ActionAdministrationComponent.prototype.prepareActions = function () {
        $j('#inner_content').remove();
    };
    ActionAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.prepareActions();
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.creationMode = true;
                _this.http.get(_this.coreUrl + 'rest/initAction')
                    .subscribe(function (data) {
                    _this.action = data.action;
                    _this.categoriesList = data.categoriesList;
                    _this.statuses = data.statuses;
                    _this.actionPagesList = data.action_pagesList;
                    _this.keywordsList = data.keywordsList;
                    _this.loading = false;
                });
            }
            else {
                _this.creationMode = false;
                _this.http.get(_this.coreUrl + 'rest/actions/' + params['id'])
                    .subscribe(function (data) {
                    _this.action = data.action;
                    _this.categoriesList = data.categoriesList;
                    _this.statuses = data.statuses;
                    _this.actionPagesList = data.action_pagesList;
                    _this.keywordsList = data.keywordsList;
                    _this.loading = false;
                });
            }
        });
    };
    ActionAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        if (this.creationMode) {
            this.http.post(this.coreUrl + 'rest/actions', this.action)
                .subscribe(function (data) {
                _this.router.navigate(['/administration/actions']);
                _this.notify.success(_this.lang.actionAdded);
            }, function (err) {
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
        else {
            this.http.put(this.coreUrl + 'rest/actions/' + this.action.id, this.action)
                .subscribe(function (data) {
                _this.router.navigate(['/administration/actions']);
                _this.notify.success(_this.lang.actionUpdated);
            }, function (err) {
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
    };
    ActionAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["action-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService])
    ], ActionAdministrationComponent);
    return ActionAdministrationComponent;
}());
exports.ActionAdministrationComponent = ActionAdministrationComponent;
