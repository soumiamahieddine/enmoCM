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
var BasketAdministrationComponent = (function () {
    function BasketAdministrationComponent(http, route, router, notify) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.loading = false;
        this.basket = {};
        this.basketGroups = [];
    }
    BasketAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/baskets\"' style='cursor: pointer'>Bannettes</a>";
        }
    };
    BasketAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.creationMode = true;
                _this.loading = false;
            }
            else {
                _this.creationMode = false;
                _this.id = params['id'];
                _this.http.get(_this.coreUrl + "rest/baskets/" + _this.id)
                    .subscribe(function (data) {
                    _this.basket = data.basket;
                    _this.basket.id = data.basket.basket_id;
                    _this.basket.name = data.basket.basket_name;
                    _this.basket.description = data.basket.basket_desc;
                    _this.basket.clause = data.basket.basket_clause;
                    _this.basket.isSearchBasket = data.basket.is_visible != "Y";
                    _this.basket.isFolderBasket = data.basket.is_folder_basket == "Y";
                    _this.basket.flagNotif = data.basket.flag_notif == "Y";
                    _this.http.get(_this.coreUrl + "rest/baskets/" + _this.id + "/groups")
                        .subscribe(function (data) {
                        _this.basketGroups = data.groups;
                        _this.loading = false;
                    }, function () {
                        location.href = "index.php";
                    });
                }, function () {
                    location.href = "index.php";
                });
            }
        });
    };
    BasketAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/baskets", this.basket)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.basketAdded);
                _this.router.navigate(["/administration/baskets"]);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/baskets/" + this.id, this.basket)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.basketUpdated);
                _this.router.navigate(["/administration/baskets"]);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    return BasketAdministrationComponent;
}());
BasketAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["basket-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css'],
        providers: [notification_service_1.NotificationService]
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService])
], BasketAdministrationComponent);
exports.BasketAdministrationComponent = BasketAdministrationComponent;
