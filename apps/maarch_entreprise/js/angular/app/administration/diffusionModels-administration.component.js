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
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var material_1 = require("@angular/material");
var DiffusionModelsAdministrationComponent = /** @class */ (function () {
    function DiffusionModelsAdministrationComponent(changeDetectorRef, media, http, notify, dialog) {
        this.http = http;
        this.notify = notify;
        this.dialog = dialog;
        this.config = {};
        this.lang = translate_component_1.LANG;
        this.listTemplates = [];
        this.listTemplatesForAssign = [];
        this.loading = false;
        this.displayedColumns = ['title', 'description', 'object_type', 'actions'];
        this.dataSource = new material_1.MatTableDataSource(this.listTemplates);
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = function () { return changeDetectorRef.detectChanges(); };
        this.mobileQuery.addListener(this._mobileQueryListener);
    }
    DiffusionModelsAdministrationComponent.prototype.applyFilter = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    };
    DiffusionModelsAdministrationComponent.prototype.ngOnDestroy = function () {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    };
    DiffusionModelsAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Groupes";
        }
    };
    DiffusionModelsAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get(this.coreUrl + "rest/listTemplates")
            .subscribe(function (data) {
            _this.listTemplates = data['listTemplates'];
            _this.loading = false;
            setTimeout(function () {
                _this.dataSource = new material_1.MatTableDataSource(_this.listTemplates);
                _this.dataSource.paginator = _this.paginator;
                _this.dataSource.sort = _this.sort;
            }, 0);
        }, function () {
            location.href = "index.php";
        });
    };
    DiffusionModelsAdministrationComponent.prototype.delete = function (listTemplate) {
        var _this = this;
        this.http.delete(this.coreUrl + "rest/listTemplates/" + listTemplate['id'])
            .subscribe(function (data) {
            setTimeout(function () {
                _this.listTemplates = data['listTemplates'];
                _this.dataSource = new material_1.MatTableDataSource(_this.listTemplates);
                _this.dataSource.paginator = _this.paginator;
                _this.dataSource.sort = _this.sort;
            }, 0);
            _this.notify.success(_this.lang.groupDeleted);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    __decorate([
        core_1.ViewChild(material_1.MatPaginator),
        __metadata("design:type", material_1.MatPaginator)
    ], DiffusionModelsAdministrationComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], DiffusionModelsAdministrationComponent.prototype, "sort", void 0);
    DiffusionModelsAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["diffusionModels-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [core_1.ChangeDetectorRef, layout_1.MediaMatcher, http_1.HttpClient, notification_service_1.NotificationService, material_1.MatDialog])
    ], DiffusionModelsAdministrationComponent);
    return DiffusionModelsAdministrationComponent;
}());
exports.DiffusionModelsAdministrationComponent = DiffusionModelsAdministrationComponent;
