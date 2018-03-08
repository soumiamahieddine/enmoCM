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
        _this.itemTypeList = [];
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
                _this.itemTypeList = [{ "id": "VISA_CIRCUIT", "label": _this.lang.visa }, { "id": "AVIS_CIRCUIT", "label": _this.lang.avis }];
            }
            else {
                _this.creationMode = false;
                _this.http.get(_this.coreUrl + "rest/listTemplates/" + params['id'])
                    .subscribe(function (data) {
                    _this.updateBreadcrumb(angularGlobals.applicationName);
                    _this.diffusionModel = data['listTemplate'];
                    if (_this.diffusionModel.diffusionList[0]) {
                        _this.idCircuit = _this.diffusionModel.diffusionList[0].id;
                    }
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
    DiffusionModelAdministrationComponent.prototype.addElemListModel = function (element) {
        var _this = this;
        var newDiffList = {
            "object_id": this.diffusionModel.entity_id,
            "object_type": this.diffusionModel.object_type,
            "title": this.diffusionModel.title,
            "description": this.diffusionModel.description,
            "items": Array()
        };
        if (this.diffusionModel.object_type == 'VISA_CIRCUIT') {
            var itemMode = 'sign';
        }
        else {
            var itemMode = 'avis';
        }
        var newElemListModel = {
            "id": '',
            "item_type": 'user_id',
            "item_mode": itemMode,
            "item_id": element.id,
            "sequence": this.diffusionModel.diffusionList.length,
            "idToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo
        };
        this.diffusionModel.diffusionList.forEach(function (listModel, i) {
            listModel.sequence = i;
            if (_this.diffusionModel.object_type == 'VISA_CIRCUIT') {
                listModel.item_mode = "visa";
            }
            else {
                listModel.item_mode = "avis";
            }
            newDiffList.items.push({
                "id": listModel.id,
                "item_id": listModel.item_id,
                "item_type": "user_id",
                "item_mode": listModel.item_mode,
                "sequence": listModel.sequence
            });
        });
        newDiffList.items.push(newElemListModel);
        if (this.diffusionModel.diffusionList.length > 0) {
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuit, newDiffList)
                .subscribe(function (data) {
                _this.idCircuit = data.id;
                _this.diffusionModel.diffusionList.push(newElemListModel);
                _this.notify.success(_this.lang.diffusionModelUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.post(this.coreUrl + "rest/listTemplates", newDiffList)
                .subscribe(function (data) {
                _this.idCircuit = data.id;
                _this.diffusionModel.diffusionList.push(newElemListModel);
                _this.notify.success(_this.lang.diffusionModelUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        this.userCtrl.setValue('');
    };
    DiffusionModelAdministrationComponent.prototype.updateDiffList = function () {
        var _this = this;
        var newDiffList = {
            "object_id": this.diffusionModel.entity_id,
            "object_type": this.diffusionModel.object_type,
            "title": this.diffusionModel.title,
            "description": this.diffusionModel.description,
            "items": Array()
        };
        this.diffusionModel.diffusionList.forEach(function (listModel, i) {
            listModel.sequence = i;
            if (_this.diffusionModel.object_type == 'VISA_CIRCUIT') {
                if (i == (_this.diffusionModel.diffusionList.length - 1)) {
                    listModel.item_mode = "sign";
                }
                else {
                    listModel.item_mode = "visa";
                }
            }
            else {
                listModel.item_mode = "avis";
            }
            newDiffList.items.push({
                "id": listModel.id,
                "item_id": listModel.item_id,
                "item_type": "user_id",
                "item_mode": listModel.item_mode,
                "sequence": listModel.sequence
            });
        });
        this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuit, newDiffList)
            .subscribe(function (data) {
            _this.idCircuit = data.id;
            _this.notify.success(_this.lang.diffusionModelUpdated);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    DiffusionModelAdministrationComponent.prototype.removeDiffList = function (template, i) {
        var _this = this;
        this.diffusionModel.diffusionList.splice(i, 1);
        if (this.diffusionModel.diffusionList.length > 0) {
            var newDiffList = {
                "object_id": this.diffusionModel.entity_id,
                "object_type": this.diffusionModel.object_type,
                "title": this.diffusionModel.title,
                "description": this.diffusionModel.description,
                "items": Array()
            };
            this.diffusionModel.diffusionList.forEach(function (listModel, i) {
                listModel.sequence = i;
                if (_this.diffusionModel.object_type == 'VISA_CIRCUIT') {
                    if (i == (_this.diffusionModel.diffusionList.length - 1)) {
                        listModel.item_mode = "sign";
                    }
                    else {
                        listModel.item_mode = "visa";
                    }
                }
                else {
                    listModel.item_mode = "avis";
                }
                newDiffList.items.push({
                    "item_id": listModel.item_id,
                    "item_type": "user_id",
                    "item_mode": listModel.item_mode,
                    "sequence": listModel.sequence
                });
            });
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuit, newDiffList)
                .subscribe(function (data) {
                _this.idCircuit = data.id;
                _this.notify.success(_this.lang.diffusionModelUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.delete(this.coreUrl + "rest/listTemplates/" + this.idCircuit)
                .subscribe(function (data) {
                _this.idCircuit = null;
                _this.notify.success(_this.lang.diffusionModelUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
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
