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
var PriorityAdministrationComponent = /** @class */ (function () {
    function PriorityAdministrationComponent(http, route, router, notify) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.loading = false;
        this.priority = {
            useDoctypeDelay: false,
            color: "#135f7f",
            delays: "0",
            working_days: "false"
        };
    }
    PriorityAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/priorities\"' style='cursor: pointer'>" + this.lang.priorities + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.priorityCreation;
        }
        else {
            breadCrumb += this.lang.priorityModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    };
    PriorityAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.creationMode = true;
                _this.updateBreadcrumb(angularGlobals.applicationName);
                _this.loading = false;
            }
            else {
                _this.creationMode = false;
                _this.updateBreadcrumb(angularGlobals.applicationName);
                _this.id = params['id'];
                _this.http.get(_this.coreUrl + "rest/priorities/" + _this.id)
                    .subscribe(function (data) {
                    _this.priority = data.priority;
                    if (_this.priority.delays == '*') {
                        _this.priority.useDoctypeDelay = false;
                    }
                    else {
                        _this.priority.useDoctypeDelay = true;
                    }
                    if (_this.priority.working_days === true) {
                        _this.priority.working_days = "true";
                    }
                    else {
                        _this.priority.working_days = "false";
                    }
                    _this.loading = false;
                }, function () {
                    location.href = "index.php";
                });
            }
        });
    };
    PriorityAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        if (this.priority.useDoctypeDelay == false) {
            this.priority.delays = '*';
        }
        if (this.priority.working_days == "true") {
            this.priority.working_days = true;
        }
        else {
            this.priority.working_days = false;
        }
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/priorities", this.priority)
                .subscribe(function () {
                _this.notify.success(_this.lang.priorityAdded);
                _this.router.navigate(["/administration/priorities"]);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/priorities/" + this.id, this.priority)
                .subscribe(function () {
                _this.notify.success(_this.lang.priorityUpdated);
                _this.router.navigate(["/administration/priorities"]);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    PriorityAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["priority-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService])
    ], PriorityAdministrationComponent);
    return PriorityAdministrationComponent;
}());
exports.PriorityAdministrationComponent = PriorityAdministrationComponent;
