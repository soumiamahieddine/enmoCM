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
var NotificationAdministrationComponent = /** @class */ (function () {
    function NotificationAdministrationComponent(http, route, router, notify) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.notify = notify;
        this.notification = {
            diffusionType_label: null
        };
        this.loading = false;
        this.lang = translate_component_1.LANG;
    }
    NotificationAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/notifications\"' style='cursor: pointer'>" + this.lang.notifications + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.notificationCreation;
        }
        else {
            breadCrumb += this.lang.notificationModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    };
    NotificationAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.route.params.subscribe(function (params) {
            if (typeof params['identifier'] == "undefined") {
                _this.creationMode = true;
                _this.http.get(_this.coreUrl + 'rest/administration/notifications/new')
                    .subscribe(function (data) {
                    _this.notification = data.notification;
                    _this.loading = false;
                }, function (err) {
                    _this.notify.error(err.error.errors);
                });
            }
            else {
                _this.creationMode = false;
                _this.http.get(_this.coreUrl + 'rest/notifications/' + params['identifier'])
                    .subscribe(function (data) {
                    _this.notification = data.notification;
                    _this.loading = false;
                }, function (err) {
                    _this.notify.error(err.error.errors);
                });
            }
        });
        this.updateBreadcrumb(angularGlobals.applicationName);
    };
    NotificationAdministrationComponent.prototype.selectAll = function (event) {
        var target = event.target.getAttribute("data-target");
        $j('#' + target + ' option').prop('selected', true);
        $j('#' + target).trigger('chosen:updated');
    };
    NotificationAdministrationComponent.prototype.unselectAll = function (event) {
        var target = event.target.getAttribute("data-target");
        $j('#' + target + ' option').prop('selected', false);
        $j('#' + target).trigger('chosen:updated');
    };
    NotificationAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        if ($j("#groupslist").val()) {
            this.notification.diffusion_properties = $j("#groupslist").val();
        }
        else if ($j("#entitieslist").val()) {
            this.notification.diffusion_properties = $j("#entitieslist").val();
        }
        else if ($j("#statuseslist").val()) {
            this.notification.diffusion_properties = $j("#statuseslist").val();
        }
        else if ($j("#userslist").val()) {
            this.notification.diffusion_properties = $j("#userslist").val();
        }
        if ($j("#joinDocJd").val() == null) {
            this.notification.attachfor_properties = '';
        }
        else if ($j("#groupslistJd").val()) {
            this.notification.attachfor_properties = $j("#groupslistJd").val();
        }
        else if ($j("#entitieslistJd").val()) {
            this.notification.attachfor_properties = $j("#entitieslistJd").val();
        }
        else if ($j("#statuseslistJd").val()) {
            this.notification.attachfor_properties = $j("#statuseslistJd").val();
        }
        else if ($j("#userslistJd").val()) {
            this.notification.attachfor_properties = $j("#userslistJd").val();
        }
        if (this.creationMode) {
            this.http.post(this.coreUrl + 'rest/notifications', this.notification)
                .subscribe(function (data) {
                _this.router.navigate(['/administration/notifications']);
                _this.notify.success(_this.lang.NotificationAdded);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + 'rest/notifications/' + this.notification.notification_sid, this.notification)
                .subscribe(function (data) {
                _this.router.navigate(['/administration/notifications']);
                _this.notify.success(_this.lang.notificationUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    NotificationAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["notification-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService])
    ], NotificationAdministrationComponent);
    return NotificationAdministrationComponent;
}());
exports.NotificationAdministrationComponent = NotificationAdministrationComponent;
