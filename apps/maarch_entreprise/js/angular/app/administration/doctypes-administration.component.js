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
var __param = (this && this.__param) || function (paramIndex, decorator) {
    return function (target, key) { decorator(target, key, paramIndex); }
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = require("@angular/core");
var layout_1 = require("@angular/cdk/layout");
var http_1 = require("@angular/common/http");
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var material_1 = require("@angular/material");
var DoctypesAdministrationComponent = /** @class */ (function () {
    function DoctypesAdministrationComponent(changeDetectorRef, media, http, notify, dialog) {
        this.http = http;
        this.notify = notify;
        this.dialog = dialog;
        this.config = {};
        this.lang = translate_component_1.LANG;
        this.doctypes = [];
        this.currentType = false;
        this.currentSecondLevel = false;
        this.currentFirstLevel = false;
        this.firstLevels = false;
        this.folderTypes = false;
        this.types = false;
        this.secondLevels = false;
        this.processModes = false;
        this.models = false;
        this.loading = false;
        this.creationMode = false;
        this.newSecondLevel = false;
        this.displayedColumns = ['label', 'use', 'mandatory', 'column'];
        this.dataSource = new material_1.MatTableDataSource(this.currentType.indexes);
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = function () { return changeDetectorRef.detectChanges(); };
        this.mobileQuery.addListener(this._mobileQueryListener);
    }
    DoctypesAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Typologie documentaire";
        }
    };
    DoctypesAdministrationComponent.prototype.ngOnDestroy = function () {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    };
    DoctypesAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get(this.coreUrl + "rest/doctypes")
            .subscribe(function (data) {
            _this.doctypes = data['structure'];
            setTimeout(function () {
                $j('#jstree').jstree({
                    "checkbox": {
                        "three_state": false //no cascade selection
                    },
                    'core': {
                        'themes': {
                            'name': 'proton',
                            'responsive': true
                        },
                        'data': _this.doctypes,
                        "check_callback": true
                    },
                    "plugins": ["search", "dnd", "contextmenu"],
                });
                var to = false;
                $j('#jstree_search').keyup(function () {
                    if (to) {
                        clearTimeout(to);
                    }
                    to = setTimeout(function () {
                        var v = $j('#jstree_search').val();
                        $j('#jstree').jstree(true).search(v);
                    }, 250);
                });
                $j('#jstree')
                    .on('select_node.jstree', function (e, data) {
                    _this.loadDoctype(data, false);
                }).on('move_node.jstree', function (e, data) {
                    _this.loadDoctype(data, true);
                })
                    .jstree();
            }, 0);
            $j('#jstree').jstree('select_node', _this.doctypes[0]);
            _this.loading = false;
        }, function () {
            location.href = "index.php";
        });
    };
    DoctypesAdministrationComponent.prototype.loadDoctype = function (data, move) {
        var _this = this;
        this.creationMode = false;
        // Doctype
        if (data.node.original.type_id) {
            this.currentFirstLevel = false;
            this.currentSecondLevel = false;
            this.http.get(this.coreUrl + "rest/doctypes/types/" + data.node.original.type_id)
                .subscribe(function (dataValue) {
                _this.currentType = dataValue['doctype'];
                _this.secondLevels = dataValue['secondLevel'];
                _this.processModes = dataValue['processModes'];
                _this.models = dataValue['models'];
                _this.loadIndexesTable();
                if (move) {
                    if (_this.currentType) {
                        _this.newSecondLevel = data.parent.replace("secondlevel_", "");
                        // Is integer
                        if (!isNaN(parseFloat(_this.newSecondLevel)) && isFinite(_this.newSecondLevel)) {
                            if (_this.currentType.doctypes_second_level_id != _this.newSecondLevel) {
                                _this.currentType.doctypes_second_level_id = _this.newSecondLevel;
                                _this.saveType();
                            }
                        }
                        else {
                            alert(_this.lang.cantMoveDoctype);
                        }
                    }
                    else {
                        alert(_this.lang.noDoctypeSelected);
                    }
                }
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
            // Second level
        }
        else if (data.node.original.doctypes_second_level_id) {
            this.currentFirstLevel = false;
            this.currentType = false;
            this.http.get(this.coreUrl + "rest/doctypes/secondLevel/" + data.node.original.doctypes_second_level_id)
                .subscribe(function (data) {
                _this.currentSecondLevel = data['secondLevel'];
                _this.firstLevels = data['firstLevel'];
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
            // First level
        }
        else {
            this.currentSecondLevel = false;
            this.currentType = false;
            this.http.get(this.coreUrl + "rest/doctypes/firstLevel/" + data.node.original.doctypes_first_level_id)
                .subscribe(function (data) {
                _this.currentFirstLevel = data['firstLevel'];
                _this.folderTypes = data['folderTypes'];
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    DoctypesAdministrationComponent.prototype.loadIndexesTable = function () {
        this.dataSource = new material_1.MatTableDataSource(this.currentType.indexes);
        this.dataSource.paginator = this.paginator;
        this.dataSource.sort = this.sort;
    };
    DoctypesAdministrationComponent.prototype.resetDatas = function () {
        this.currentFirstLevel = false;
        this.currentSecondLevel = false;
        this.currentType = false;
    };
    DoctypesAdministrationComponent.prototype.refreshTree = function () {
        $j('#jstree').jstree(true).settings.core.data = this.doctypes;
        $j('#jstree').jstree("refresh");
    };
    DoctypesAdministrationComponent.prototype.saveFirstLevel = function () {
        var _this = this;
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/doctypes/firstLevel", this.currentFirstLevel)
                .subscribe(function (data) {
                _this.resetDatas();
                _this.readMode();
                _this.doctypes = data['doctypeTree'];
                _this.refreshTree();
                _this.notify.success(_this.lang.firstLevelAdded);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/doctypes/firstLevel/" + this.currentFirstLevel.doctypes_first_level_id, this.currentFirstLevel)
                .subscribe(function (data) {
                _this.doctypes = data['doctypeTree'];
                _this.refreshTree();
                _this.notify.success(_this.lang.firstLevelUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    DoctypesAdministrationComponent.prototype.saveSecondLevel = function () {
        var _this = this;
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/doctypes/secondLevel", this.currentSecondLevel)
                .subscribe(function (data) {
                _this.resetDatas();
                _this.readMode();
                _this.doctypes = data['doctypeTree'];
                _this.refreshTree();
                _this.notify.success(_this.lang.secondLevelAdded);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/doctypes/secondLevel/" + this.currentSecondLevel.doctypes_second_level_id, this.currentSecondLevel)
                .subscribe(function (data) {
                _this.doctypes = data['doctypeTree'];
                _this.refreshTree();
                _this.notify.success(_this.lang.secondLevelUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    DoctypesAdministrationComponent.prototype.saveType = function () {
        var _this = this;
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/doctypes/types", this.currentType)
                .subscribe(function (data) {
                _this.resetDatas();
                _this.readMode();
                _this.doctypes = data['doctypeTree'];
                _this.refreshTree();
                _this.notify.success(_this.lang.documentTypeAdded);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/doctypes/types/" + this.currentType.type_id, this.currentType)
                .subscribe(function (data) {
                _this.doctypes = data['doctypeTree'];
                _this.refreshTree();
                _this.notify.success(_this.lang.documentTypeUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    DoctypesAdministrationComponent.prototype.readMode = function () {
        this.creationMode = false;
        $j('#jstree').jstree('deselect_all');
        $j('#jstree').jstree('select_node', this.doctypes[0]);
    };
    DoctypesAdministrationComponent.prototype.removeFirstLevel = function () {
        var _this = this;
        var r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + this.currentFirstLevel.doctypes_first_level_label + ' »');
        if (r) {
            this.http.delete(this.coreUrl + "rest/doctypes/firstLevel/" + this.currentFirstLevel.doctypes_first_level_id)
                .subscribe(function (data) {
                _this.resetDatas();
                _this.readMode();
                _this.doctypes = data['doctypeTree'];
                _this.refreshTree();
                $j('#jstree').jstree('select_node', _this.doctypes[0]);
                _this.notify.success(_this.lang.firstLevelDeleted);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    DoctypesAdministrationComponent.prototype.removeSecondLevel = function () {
        var _this = this;
        var r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + this.currentSecondLevel.doctypes_second_level_label + ' »');
        if (r) {
            this.http.delete(this.coreUrl + "rest/doctypes/secondLevel/" + this.currentSecondLevel.doctypes_second_level_id)
                .subscribe(function (data) {
                _this.resetDatas();
                _this.readMode();
                _this.doctypes = data['doctypeTree'];
                _this.refreshTree();
                $j('#jstree').jstree('select_node', _this.doctypes[0]);
                _this.notify.success(_this.lang.secondLevelDeleted);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    DoctypesAdministrationComponent.prototype.removeType = function () {
        var _this = this;
        var r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + this.currentType.description + ' »');
        if (r) {
            this.http.delete(this.coreUrl + "rest/doctypes/types/" + this.currentType.type_id)
                .subscribe(function (data) {
                if (data.deleted == 0) {
                    _this.resetDatas();
                    _this.readMode();
                    _this.doctypes = data['doctypeTree'];
                    _this.refreshTree();
                    $j('#jstree').jstree('select_node', _this.doctypes[0]);
                    _this.notify.success(_this.lang.documentTypeDeleted);
                }
                else {
                    _this.config = { data: { count: data.deleted, types: data.doctypes } };
                    _this.dialogRef = _this.dialog.open(DoctypesAdministrationRedirectModalComponent, _this.config);
                    _this.dialogRef.afterClosed().subscribe(function (result) {
                        if (result) {
                            _this.http.put(_this.coreUrl + "rest/doctypes/types/" + _this.currentType.type_id + "/redirect", result)
                                .subscribe(function (data) {
                                _this.resetDatas();
                                _this.readMode();
                                _this.doctypes = data['doctypeTree'];
                                _this.refreshTree();
                                $j('#jstree').jstree('select_node', _this.doctypes[0]);
                                _this.notify.success(_this.lang.documentTypeDeleted);
                            }, function (err) {
                                _this.notify.error(err.error.errors);
                            });
                        }
                        _this.dialogRef = null;
                    });
                }
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    DoctypesAdministrationComponent.prototype.prepareDoctypeAdd = function () {
        var _this = this;
        this.currentFirstLevel = {};
        this.currentSecondLevel = {};
        this.currentType = {};
        $j('#jstree').jstree('deselect_all');
        this.http.get(this.coreUrl + "rest/administration/doctypes/new")
            .subscribe(function (data) {
            _this.folderTypes = data['folderTypes'];
            _this.firstLevels = data['firstLevel'];
            _this.secondLevels = data['secondLevel'];
            _this.processModes = data['processModes'];
            _this.models = data['models'];
            _this.currentType.indexes = data['indexes'];
            _this.loadIndexesTable();
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
        this.creationMode = true;
    };
    DoctypesAdministrationComponent.prototype.selectIndexesUse = function (e, index) {
        this.currentType.indexes[index].use = e.checked;
    };
    DoctypesAdministrationComponent.prototype.selectIndexesMandatory = function (e, index) {
        this.currentType.indexes[index].mandatory = e.checked;
    };
    __decorate([
        core_1.ViewChild(material_1.MatPaginator),
        __metadata("design:type", material_1.MatPaginator)
    ], DoctypesAdministrationComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], DoctypesAdministrationComponent.prototype, "sort", void 0);
    DoctypesAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["doctypes-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [core_1.ChangeDetectorRef, layout_1.MediaMatcher, http_1.HttpClient, notification_service_1.NotificationService, material_1.MatDialog])
    ], DoctypesAdministrationComponent);
    return DoctypesAdministrationComponent;
}());
exports.DoctypesAdministrationComponent = DoctypesAdministrationComponent;
var DoctypesAdministrationRedirectModalComponent = /** @class */ (function () {
    function DoctypesAdministrationRedirectModalComponent(http, data, dialogRef) {
        this.http = http;
        this.data = data;
        this.dialogRef = dialogRef;
        this.lang = translate_component_1.LANG;
    }
    DoctypesAdministrationRedirectModalComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["doctypes-administration-redirect-modalView"],
        }),
        __param(1, core_1.Inject(material_1.MAT_DIALOG_DATA)),
        __metadata("design:paramtypes", [http_1.HttpClient, Object, material_1.MatDialogRef])
    ], DoctypesAdministrationRedirectModalComponent);
    return DoctypesAdministrationRedirectModalComponent;
}());
exports.DoctypesAdministrationRedirectModalComponent = DoctypesAdministrationRedirectModalComponent;
