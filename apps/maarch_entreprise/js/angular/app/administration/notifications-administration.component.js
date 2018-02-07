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
var material_1 = require("@angular/material");
var NotificationsAdministrationComponent = /** @class */ (function () {
    function NotificationsAdministrationComponent(http, notify) {
        this.http = http;
        this.notify = notify;
        this.notifications = [];
        this.loading = false;
        this.lang = translate_component_1.LANG;
        this.displayedColumns = ['notification_id', 'description', 'is_enabled', 'notifications'];
        this.dataSource = new material_1.MatTableDataSource(this.notifications);
    }
    NotificationsAdministrationComponent.prototype.applyFilter = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    };
    NotificationsAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get(this.coreUrl + 'rest/notifications')
            .subscribe(function (data) {
            _this.notifications = data.notifications;
            _this.loading = false;
            setTimeout(function () {
                _this.dataSource = new material_1.MatTableDataSource(_this.notifications);
                _this.dataSource.paginator = _this.paginator;
                _this.dataSource.sort = _this.sort;
            }, 0);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    NotificationsAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " + this.lang.notifications;
        }
    };
    NotificationsAdministrationComponent.prototype.deleteNotification = function (notification) {
        var _this = this;
        var r = confirm(this.lang.deleteMsg);
        if (r) {
            this.http.delete(this.coreUrl + 'rest/notifications/' + notification.notification_sid)
                .subscribe(function (data) {
                _this.notifications = data.notifications;
                setTimeout(function () {
                    _this.dataSource = new material_1.MatTableDataSource(_this.notifications);
                    _this.dataSource.paginator = _this.paginator;
                    _this.dataSource.sort = _this.sort;
                }, 0);
                _this.notify.success(_this.lang.notificationDeleted);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    __decorate([
        core_1.ViewChild(material_1.MatPaginator),
        __metadata("design:type", material_1.MatPaginator)
    ], NotificationsAdministrationComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], NotificationsAdministrationComponent.prototype, "sort", void 0);
    NotificationsAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["notifications-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [http_1.HttpClient, notification_service_1.NotificationService])
    ], NotificationsAdministrationComponent);
    return NotificationsAdministrationComponent;
}());
exports.NotificationsAdministrationComponent = NotificationsAdministrationComponent;
