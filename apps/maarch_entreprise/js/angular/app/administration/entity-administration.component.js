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
var EntityAdministrationComponent = /** @class */ (function () {
    function EntityAdministrationComponent(http, route, router, notify) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.entity = {};
        this.loading = false;
    }
    EntityAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/entities\"' style='cursor: pointer'>Entités</a>";
        }
    };
    EntityAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.creationMode = true;
                _this.entityIdAvailable = false;
                _this.loading = false;
            }
            else {
                _this.creationMode = false;
                _this.entityIdAvailable = true;
                _this.id = params['id'];
                _this.http.get(_this.coreUrl + "rest/entities/" + _this.id + "/details")
                    .subscribe(function (data) {
                    _this.entity = data.basket;
                    _this.loading = false;
                }, function () {
                    location.href = "index.php";
                });
            }
        });
    };
    EntityAdministrationComponent.prototype.isAvailable = function () {
        var _this = this;
        if (this.entity.id) {
            this.http.get(this.coreUrl + "rest/entities/" + this.entity.id)
                .subscribe(function () {
                _this.entityIdAvailable = false;
            }, function (err) {
                _this.entityIdAvailable = false;
                if (err.error.errors == "Entity not found") {
                    _this.entityIdAvailable = true;
                }
            });
        }
        else {
            this.entityIdAvailable = false;
        }
    };
    EntityAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/entities", this.entity)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.entityAdded);
                _this.router.navigate(["/administration/entities"]);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/entities/" + this.id, this.entity)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.entityUpdated);
                _this.router.navigate(["/administration/entities"]);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    EntityAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["entity-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService])
    ], EntityAdministrationComponent);
    return EntityAdministrationComponent;
}());
exports.EntityAdministrationComponent = EntityAdministrationComponent;
