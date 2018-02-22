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
var material_1 = require("@angular/material");
var GroupAdministrationComponent = /** @class */ (function () {
    function GroupAdministrationComponent(changeDetectorRef, media, http, route, router, notify) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.group = {
            security: {}
        };
        this.loading = false;
        this.displayedColumns = ['firstname', 'lastname'];
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = function () { return changeDetectorRef.detectChanges(); };
        this.mobileQuery.addListener(this._mobileQueryListener);
    }
    GroupAdministrationComponent.prototype.applyFilter = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    };
    GroupAdministrationComponent.prototype.ngOnDestroy = function () {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    };
    GroupAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/groups\"' style='cursor: pointer'>" + this.lang.groups + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.groupCreation;
        }
        else {
            breadCrumb += this.lang.groupModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    };
    GroupAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.creationMode = true;
                _this.loading = false;
                _this.updateBreadcrumb(angularGlobals.applicationName);
            }
            else {
                _this.creationMode = false;
                _this.http.get(_this.coreUrl + "rest/groups/" + params['id'] + "/details")
                    .subscribe(function (data) {
                    _this.updateBreadcrumb(angularGlobals.applicationName);
                    _this.group = data['group'];
                    _this.loading = false;
                    setTimeout(function () {
                        _this.dataSource = new material_1.MatTableDataSource(_this.group.users);
                        _this.dataSource.paginator = _this.paginator;
                        _this.dataSource.sort = _this.sort;
                    }, 0);
                }, function () {
                    location.href = "index.php";
                });
            }
        });
    };
    GroupAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/groups", this.group)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.groupAdded);
                _this.router.navigate(["/administration/groups/" + data.group]);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/groups/" + this.group['id'], { "description": this.group['group_desc'], "security": this.group['security'] })
                .subscribe(function (data) {
                _this.notify.success(_this.lang.groupUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    GroupAdministrationComponent.prototype.updateService = function (service) {
        var _this = this;
        this.http.put(this.coreUrl + "rest/groups/" + this.group['id'] + "/services/" + service['id'], service)
            .subscribe(function (data) {
            _this.notify.success(_this.lang.groupUpdated);
        }, function (err) {
            service.checked = !service.checked;
            _this.notify.error(err.error.errors);
        });
    };
    __decorate([
        core_1.ViewChild(material_1.MatPaginator),
        __metadata("design:type", material_1.MatPaginator)
    ], GroupAdministrationComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], GroupAdministrationComponent.prototype, "sort", void 0);
    GroupAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["group-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [core_1.ChangeDetectorRef, layout_1.MediaMatcher, http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService])
    ], GroupAdministrationComponent);
    return GroupAdministrationComponent;
}());
exports.GroupAdministrationComponent = GroupAdministrationComponent;
