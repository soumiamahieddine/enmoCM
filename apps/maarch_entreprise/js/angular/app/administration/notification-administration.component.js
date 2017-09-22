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
var notification_service_1 = require("../notification.service");
var NotificationAdministrationComponent = (function () {
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
    NotificationAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.prepareNotifications();
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.route.params.subscribe(function (params) {
            console.log(params['identifier']);
            if (typeof params['identifier'] == "undefined") {
                //if(params['identifier']== "new"){
                _this.creationMode = true;
                _this.http.get(_this.coreUrl + 'rest/administration/notifications/new')
                    .subscribe(function (data) {
                    _this.notification = data.notification;
                    _this.loading = false;
                    // setTimeout(() => {
                    //             $j("select").chosen({width:"100%",disable_search_threshold: 10, search_contains: true});     
                    //         }, 0);
                }, function (err) {
                    errorNotification(JSON.parse(err._body).errors);
                });
            }
            else {
                _this.creationMode = false;
                _this.http.get(_this.coreUrl + 'rest/administration/notifications/' + params['identifier'])
                    .subscribe(function (data) {
                    _this.notification = data.notification;
                    console.log(data.notification);
                    _this.loading = false;
                    setTimeout(function () {
                        $j("select").chosen({ width: "100%", disable_search_threshold: 10, search_contains: true });
                    }, 0);
                });
            }
        });
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
        //affect value of select
        if ($j("#groupslist").chosen().val()) {
            this.notification.diffusion_properties = $j("#groupslist").chosen().val();
        }
        else if ($j("#entitieslist").chosen().val()) {
            this.notification.diffusion_properties = $j("#entitieslist").chosen().val();
        }
        else if ($j("#statuseslist").chosen().val()) {
            this.notification.diffusion_properties = $j("#statuseslist").chosen().val();
        }
        else if ($j("#userslist").chosen().val()) {
            this.notification.diffusion_properties = $j("#userslist").chosen().val();
        }
        if ($j("#groupslistJd").chosen().val()) {
            this.notification.attachfor_properties = $j("#groupslistJd").chosen().val();
        }
        else if ($j("#entitieslistJd").chosen().val()) {
            this.notification.attachfor_properties = $j("#entitieslistJd").chosen().val();
        }
        else if ($j("#statuseslistJd").chosen().val()) {
            this.notification.attachfor_properties = $j("#statuseslistJd").chosen().val();
        }
        else if ($j("#userslistJd").chosen().val()) {
            this.notification.attachfor_properties = $j("#userslistJd").chosen().val();
        }
        if (this.creationMode) {
            this.http.post(this.coreUrl + 'rest/notifications', this.notification)
                .subscribe(function (data) {
                _this.router.navigate(['/administration/notifications']);
                _this.notify.success(_this.lang.newNotificationAdded + ' « ' + _this.notification.notification_id + ' »');
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + 'rest/notifications/' + this.notification.notification_sid, this.notification)
                .subscribe(function (data) {
                _this.router.navigate(['/administration/notifications']);
                _this.notify.success(_this.lang.notificationUpdated + ' « ' + _this.notification.notification_id + ' »');
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
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
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css'],
        providers: [notification_service_1.NotificationService]
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService])
], NotificationAdministrationComponent);
exports.NotificationAdministrationComponent = NotificationAdministrationComponent;
