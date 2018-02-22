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
var layout_1 = require("@angular/cdk/layout");
var http_1 = require("@angular/common/http");
var router_1 = require("@angular/router");
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var NotificationsScheduleAdministrationComponent = /** @class */ (function () {
    function NotificationsScheduleAdministrationComponent(changeDetectorRef, media, http, router, notify) {
        this.http = http;
        this.router = router;
        this.notify = notify;
        this.crontab = [];
        this.authorizedNotification = [];
        this.loading = false;
        this.lang = translate_component_1.LANG;
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = function () { return changeDetectorRef.detectChanges(); };
        this.mobileQuery.addListener(this._mobileQueryListener);
    }
    NotificationsScheduleAdministrationComponent.prototype.ngOnDestroy = function () {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    };
    NotificationsScheduleAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get(this.coreUrl + 'rest/notifications/schedule')
            .subscribe(function (data) {
            _this.crontab = data.crontab;
            _this.authorizedNotification = data.authorizedNotification;
            _this.loading = false;
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    NotificationsScheduleAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName +
                "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration +
                "</a> > <a onclick='location.hash = \"/administration/notifications\"' style='cursor: pointer'>" + this.lang.notifications +
                "</a> > " + this.lang.notificationsSchedule;
        }
    };
    NotificationsScheduleAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        this.http.post(this.coreUrl + 'rest/notifications/schedule', this.crontab)
            .subscribe(function (data) {
            _this.router.navigate(['/administration/notifications']);
            _this.notify.success(_this.lang.NotificationScheduleUpdated);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    NotificationsScheduleAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["notifications-schedule-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [core_1.ChangeDetectorRef, layout_1.MediaMatcher, http_1.HttpClient, router_1.Router, notification_service_1.NotificationService])
    ], NotificationsScheduleAdministrationComponent);
    return NotificationsScheduleAdministrationComponent;
}());
exports.NotificationsScheduleAdministrationComponent = NotificationsScheduleAdministrationComponent;
