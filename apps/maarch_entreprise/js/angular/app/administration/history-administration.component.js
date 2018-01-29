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
var HistoryAdministrationComponent = /** @class */ (function () {
    function HistoryAdministrationComponent(http, notify) {
        this.http = http;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.loading = false;
        this.data = [];
        this.CurrentYear = new Date().getFullYear();
        this.currentMonth = new Date().getMonth() + 1;
        this.minDate = new Date();
        this.displayedColumns = ['event_date', 'event_type', 'user_id', 'info', 'remote_ip'];
        this.dataSource = new material_1.MatTableDataSource(this.data);
    }
    HistoryAdministrationComponent.prototype.applyFilter = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    };
    HistoryAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " + this.lang.history;
        }
    };
    HistoryAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.updateBreadcrumb(angularGlobals.applicationName);
        $j('#inner_content').remove();
        this.minDate = new Date(this.CurrentYear + '-' + this.currentMonth + '-01');
        this.http.get(this.coreUrl + 'rest/administration/history/eventDate/' + this.minDate.toJSON())
            .subscribe(function (data) {
            _this.data = data.historyList;
            _this.loading = false;
            setTimeout(function () {
                _this.dataSource = new material_1.MatTableDataSource(_this.data);
                _this.dataSource.paginator = _this.paginator;
                _this.dataSource.sort = _this.sort;
            }, 0);
        }, function (err) {
            console.log(err);
            location.href = "index.php";
        });
    };
    HistoryAdministrationComponent.prototype.refreshHistory = function (event) {
        var _this = this;
        this.http.get(this.coreUrl + 'rest/administration/history/eventDate/' + this.minDate.toJSON())
            .subscribe(function (data) {
            _this.data = data.historyList;
            _this.dataSource = new material_1.MatTableDataSource(_this.data);
            _this.dataSource.paginator = _this.paginator;
            _this.dataSource.sort = _this.sort;
        }, function (err) {
            console.log(err);
            location.href = "index.php";
        });
    };
    __decorate([
        core_1.ViewChild(material_1.MatPaginator),
        __metadata("design:type", material_1.MatPaginator)
    ], HistoryAdministrationComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], HistoryAdministrationComponent.prototype, "sort", void 0);
    HistoryAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["history-administrationView"],
            styleUrls: [],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [http_1.HttpClient, notification_service_1.NotificationService])
    ], HistoryAdministrationComponent);
    return HistoryAdministrationComponent;
}());
exports.HistoryAdministrationComponent = HistoryAdministrationComponent;
