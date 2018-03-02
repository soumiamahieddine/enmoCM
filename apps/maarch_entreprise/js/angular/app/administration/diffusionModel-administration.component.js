"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
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
var autocomplete_plugin_1 = require("../../plugins/autocomplete.plugin");
var DiffusionModelAdministrationComponent = /** @class */ (function (_super) {
    __extends(DiffusionModelAdministrationComponent, _super);
    function DiffusionModelAdministrationComponent(changeDetectorRef, media, http, route, router, notify) {
        var _this = _super.call(this, http, ['users']) || this;
        _this.http = http;
        _this.route = route;
        _this.router = router;
        _this.notify = notify;
        _this.lang = translate_component_1.LANG;
        _this.diffusionModel = {};
        _this.loading = false;
        _this.displayedColumns = ['firstname', 'lastname'];
        $j("link[href='merged_css.php']").remove();
        _this.mobileQuery = media.matchMedia('(max-width: 768px)');
        _this._mobileQueryListener = function () { return changeDetectorRef.detectChanges(); };
        _this.mobileQuery.addListener(_this._mobileQueryListener);
        return _this;
    }
    DiffusionModelAdministrationComponent.prototype.applyFilter = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    };
    DiffusionModelAdministrationComponent.prototype.ngOnDestroy = function () {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    };
    DiffusionModelAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/groups\"' style='cursor: pointer'>" + this.lang.groups + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.groupCreation;
        }
        else {
            breadCrumb += this.lang.groupModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    };
    DiffusionModelAdministrationComponent.prototype.ngOnInit = function () {
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
                _this.http.get(_this.coreUrl + "rest/listTemplates/" + params['id'])
                    .subscribe(function (data) {
                    _this.updateBreadcrumb(angularGlobals.applicationName);
                    _this.diffusionModel = data['listTemplate'];
                    _this.diffusionModel.roles = [{
                            "id": "avis",
                            "label": "avis"
                        }];
                    _this.loading = false;
                    setTimeout(function () {
                        _this.dataSource = new material_1.MatTableDataSource(_this.diffusionModel);
                        _this.dataSource.paginator = _this.paginator;
                        _this.dataSource.sort = _this.sort;
                    }, 0);
                }, function () {
                    location.href = "index.php";
                });
            }
        });
    };
    __decorate([
        core_1.ViewChild(material_1.MatPaginator),
        __metadata("design:type", material_1.MatPaginator)
    ], DiffusionModelAdministrationComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], DiffusionModelAdministrationComponent.prototype, "sort", void 0);
    DiffusionModelAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["diffusionModel-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [core_1.ChangeDetectorRef, layout_1.MediaMatcher, http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService])
    ], DiffusionModelAdministrationComponent);
    return DiffusionModelAdministrationComponent;
}(autocomplete_plugin_1.AutoCompletePlugin));
exports.DiffusionModelAdministrationComponent = DiffusionModelAdministrationComponent;
