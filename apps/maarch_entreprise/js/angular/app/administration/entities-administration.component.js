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
var autocomplete_plugin_1 = require("../../plugins/autocomplete.plugin");
var EntitiesAdministrationComponent = /** @class */ (function (_super) {
    __extends(EntitiesAdministrationComponent, _super);
    function EntitiesAdministrationComponent(changeDetectorRef, media, http, notify, dialog) {
        var _this = _super.call(this, http, ['usersAndEntities', 'visaUsers']) || this;
        _this.http = http;
        _this.notify = notify;
        _this.dialog = dialog;
        _this.lang = translate_component_1.LANG;
        _this.isDraggable = true;
        _this.entities = [];
        _this.currentEntity = {};
        _this.config = {};
        _this.loading = false;
        _this.creationMode = false;
        _this.displayedColumns = ['firstname', 'lastname'];
        _this.dataSource = new material_1.MatTableDataSource(_this.currentEntity.users);
        $j("link[href='merged_css.php']").remove();
        _this.mobileQuery = media.matchMedia('(max-width: 768px)');
        _this._mobileQueryListener = function () { return changeDetectorRef.detectChanges(); };
        _this.mobileQuery.addListener(_this._mobileQueryListener);
        return _this;
    }
    EntitiesAdministrationComponent.prototype.applyFilter = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    };
    EntitiesAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Entités";
        }
    };
    EntitiesAdministrationComponent.prototype.ngOnDestroy = function () {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    };
    EntitiesAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get(this.coreUrl + "rest/entityTypes")
            .subscribe(function (data) {
            _this.entityTypeList = data['types'];
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
        this.http.get(this.coreUrl + "rest/entities")
            .subscribe(function (data) {
            _this.entities = data['entities'];
            setTimeout(function () {
                $j('#jstree').jstree({
                    "checkbox": {
                        'deselect_all': true,
                        "three_state": false //no cascade selection
                    },
                    'core': {
                        'themes': {
                            'name': 'proton',
                            'responsive': true
                        },
                        'multiple': false,
                        'data': _this.entities,
                        "check_callback": function (operation, node, node_parent, node_position, more) {
                            if (operation == 'move_node') {
                                if (!node_parent.original.allowed) {
                                    return false;
                                }
                                else
                                    return true;
                            }
                        }
                    },
                    "dnd": {
                        is_draggable: function (nodes) {
                            var i = 0, j = nodes.length;
                            for (; i < j; i++) {
                                if (!nodes[i].original.allowed) {
                                    return false;
                                }
                            }
                            return true;
                        }
                    },
                    "plugins": ["checkbox", "search", "dnd", "sort"]
                });
                $j('#jstree').jstree('select_node', _this.entities[0]);
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
                    if (_this.sidenav.opened == false) {
                        _this.sidenav.open();
                    }
                    if (_this.creationMode == true) {
                        _this.currentEntity.parent_entity_id = data.node.id;
                    }
                    else {
                        _this.loadEntity(data.node.id);
                    }
                }).on('deselect_node.jstree', function (e, data) {
                    _this.sidenav.close();
                }).on('move_node.jstree', function (e, data) {
                    if (_this.currentEntity.parent_entity_id != _this.currentEntity.entity_id) {
                        _this.currentEntity.parent_entity_id = data.parent;
                    }
                    _this.moveEntity();
                })
                    .jstree();
                $j(document).on('dnd_start.vakata', function (e, data) {
                    $j('#jstree').jstree('deselect_all');
                    $j('#jstree').jstree('select_node', data.data.nodes[0]);
                });
            }, 0);
            _this.loading = false;
        }, function () {
            location.href = "index.php";
        });
    };
    EntitiesAdministrationComponent.prototype.loadEntity = function (entity_id) {
        var _this = this;
        this.http.get(this.coreUrl + "rest/entities/" + entity_id + '/details')
            .subscribe(function (data) {
            _this.currentEntity = data['entity'];
            if (_this.currentEntity.visaTemplate[0]) {
                _this.idCircuitVisa = _this.currentEntity.visaTemplate[0].id;
            }
            _this.dataSource = new material_1.MatTableDataSource(_this.currentEntity.users);
            _this.dataSource.paginator = _this.paginator;
            _this.dataSource.sort = _this.sort;
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    EntitiesAdministrationComponent.prototype.addElemListModel = function (element) {
        var _this = this;
        /* create listDiff struct */
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "entity_id",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        };
        var inListModel = false;
        var newElemListModel = {};
        this.currentEntity.roles.forEach(function (role) {
            if (role.available == true) {
                if (_this.currentEntity.listTemplate[role.id]) {
                    _this.currentEntity.listTemplate[role.id].forEach(function (listModel) {
                        /* insert current diff list */
                        newDiffList.items.push({
                            "item_type": listModel.item_type,
                            "item_mode": role.id,
                            "item_id": listModel.item_id,
                            "sequence": listModel.sequence
                        });
                        if (listModel.item_id == element.id) {
                            inListModel = true;
                        }
                    });
                }
            }
        });
        if (!inListModel) {
            if (this.currentEntity.listTemplate.dest.length == 0 && element.type == 'user') {
                newElemListModel = {
                    "item_type": "user_id",
                    "item_id": element.id,
                    "labelToDisplay": element.idToDisplay,
                    "descriptionToDisplay": element.otherInfo,
                    "item_mode": "dest",
                    "sequence": 0
                };
                this.currentEntity.listTemplate.dest.unshift(newElemListModel);
                newDiffList.items.push({
                    "item_type": "user_id",
                    "item_mode": "dest",
                    "item_id": newElemListModel.item_id,
                    "sequence": newElemListModel.sequence
                });
            }
            else {
                var itemType = '';
                if (element.type == 'user') {
                    itemType = 'user_id';
                }
                else {
                    itemType = 'entity_id';
                }
                newElemListModel = {
                    "item_type": itemType,
                    "item_id": element.id,
                    "labelToDisplay": element.idToDisplay,
                    "descriptionToDisplay": element.otherInfo,
                    "item_mode": "cc",
                    "sequence": 0
                };
                this.currentEntity.listTemplate.cc.unshift(newElemListModel);
                newDiffList.items.push({
                    "item_type": newElemListModel.item_type,
                    "item_mode": "cc",
                    "item_id": newElemListModel.item_id,
                    "sequence": 0
                });
            }
            if (this.currentEntity.listTemplate.id) {
                this.http.put(this.coreUrl + "rest/listTemplates/" + this.currentEntity.listTemplate.id, newDiffList)
                    .subscribe(function (data) {
                    _this.currentEntity.listTemplate.id = data.id;
                    _this.notify.success(_this.lang.entityUpdated);
                }, function (err) {
                    _this.notify.error(err.error.errors);
                });
            }
            else {
                this.http.post(this.coreUrl + "rest/listTemplates", newDiffList)
                    .subscribe(function (data) {
                    _this.currentEntity.listTemplate.id = data.id;
                    _this.notify.success(_this.lang.entityUpdated);
                }, function (err) {
                    _this.notify.error(err.error.errors);
                });
            }
        }
        this.elementCtrl.setValue('');
    };
    EntitiesAdministrationComponent.prototype.addElemListModelVisa = function (element) {
        var _this = this;
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "VISA_CIRCUIT",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        };
        var itemMode = '';
        var newElemListModel = {
            "id": '',
            "item_type": 'user_id',
            "item_mode": "sign",
            "item_id": element.id,
            "sequence": this.currentEntity.visaTemplate.length,
            "idToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo
        };
        this.currentEntity.visaTemplate.forEach(function (listModel, i) {
            listModel.sequence = i;
            listModel.item_mode = "visa";
            newDiffList.items.push({
                "id": listModel.id,
                "item_id": listModel.item_id,
                "item_type": "user_id",
                "item_mode": listModel.item_mode,
                "sequence": listModel.sequence
            });
        });
        newDiffList.items.push(newElemListModel);
        if (this.currentEntity.visaTemplate.length > 0) {
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa, newDiffList)
                .subscribe(function (data) {
                _this.idCircuitVisa = data.id;
                _this.currentEntity.visaTemplate.push(newElemListModel);
                _this.notify.success(_this.lang.entityUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.post(this.coreUrl + "rest/listTemplates", newDiffList)
                .subscribe(function (data) {
                _this.idCircuitVisa = data.id;
                _this.currentEntity.visaTemplate.push(newElemListModel);
                _this.notify.success(_this.lang.entityUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        this.userCtrl.setValue('');
    };
    EntitiesAdministrationComponent.prototype.saveEntity = function () {
        var _this = this;
        if (this.currentEntity.parent_entity_id == '#') {
            this.currentEntity.parent_entity_id = '';
        }
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/entities", this.currentEntity)
                .subscribe(function (data) {
                _this.entities = data['entities'];
                $j('#jstree').jstree(true).settings.core.data = _this.entities;
                $j('#jstree').jstree("refresh");
                _this.notify.success(_this.lang.entityAdded);
                _this.creationMode = false;
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
                .subscribe(function (data) {
                _this.entities = data['entities'];
                $j('#jstree').jstree(true).settings.core.data = _this.entities;
                $j('#jstree').jstree("refresh");
                _this.notify.success(_this.lang.entityUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    EntitiesAdministrationComponent.prototype.moveEntity = function () {
        var _this = this;
        this.http.put(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
            .subscribe(function (data) {
            _this.notify.success(_this.lang.entityUpdated);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    EntitiesAdministrationComponent.prototype.readMode = function () {
        this.creationMode = false;
        this.isDraggable = true;
        $j('#jstree').jstree('deselect_all');
        for (var i = 0; i < this.entities.length; i++) {
            if (this.entities[i].entity_id == this.currentEntity.parent_entity_id) {
                $j('#jstree').jstree('select_node', this.entities[i]);
                break;
            }
        }
    };
    EntitiesAdministrationComponent.prototype.removeEntity = function () {
        var _this = this;
        if (this.currentEntity.documents > 0 || this.currentEntity.redirects > 0 || this.currentEntity.instances > 0 || this.currentEntity.users.length > 0) {
            this.config = { data: { entity: this.currentEntity } };
            this.dialogRef = this.dialog.open(EntitiesAdministrationRedirectModalComponent, this.config);
            this.dialogRef.afterClosed().subscribe(function (result) {
                console.log(result);
                if (result) {
                    _this.http.put(_this.coreUrl + "rest/entities/" + result.entity_id + "/reassign/" + result.redirectEntity, {})
                        .subscribe(function (data) {
                        _this.entities = data['entities'];
                        $j('#jstree').jstree(true).settings.core.data = _this.entities;
                        $j('#jstree').jstree("refresh");
                        _this.notify.success(_this.lang.entityDeleted);
                        for (var i = 0; i < _this.entities.length; i++) {
                            if (_this.entities[i].allowed == true) {
                                $j('#jstree').jstree('select_node', _this.entities[i]);
                                break;
                            }
                        }
                    }, function (err) {
                        _this.notify.error(err.error.errors);
                    });
                }
                _this.dialogRef = null;
            });
        }
        else {
            var r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + this.currentEntity.entity_label + ' »');
            if (r) {
                this.http.delete(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id)
                    .subscribe(function (data) {
                    _this.entities = data['entities'];
                    $j('#jstree').jstree(true).settings.core.data = _this.entities;
                    $j('#jstree').jstree("refresh");
                    _this.notify.success(_this.lang.entityDeleted);
                    for (var i = 0; i < _this.entities.length; i++) {
                        if (_this.entities[i].allowed == true) {
                            $j('#jstree').jstree('select_node', _this.entities[i]);
                            break;
                        }
                    }
                }, function (err) {
                    _this.notify.error(err.error.errors);
                });
            }
        }
    };
    EntitiesAdministrationComponent.prototype.prepareEntityAdd = function () {
        this.creationMode = true;
        this.isDraggable = false;
        console.log(this.currentEntity.entity_id);
        if (this.currentEntity.entity_id) {
            for (var i = 0; i < this.entities.length; i++) {
                if (this.entities[i].entity_id == this.currentEntity.entity_id) {
                    this.currentEntity = { "entity_type": this.entityTypeList[0].id };
                    this.currentEntity.parent_entity_id = this.entities[i].entity_id;
                    break;
                }
            }
        }
        else {
            this.currentEntity = { "entity_type": this.entityTypeList[0].id };
            $j('#jstree').jstree('deselect_all');
            for (var i = 0; i < this.entities.length; i++) {
                if (this.entities[i].allowed == true) {
                    $j('#jstree').jstree('select_node', this.entities[i]);
                    break;
                }
            }
        }
    };
    EntitiesAdministrationComponent.prototype.updateStatus = function (entity, method) {
        var _this = this;
        this.http.put(this.coreUrl + "rest/entities/" + entity['entity_id'] + "/status", { "method": method })
            .subscribe(function (data) {
            _this.notify.success("");
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    EntitiesAdministrationComponent.prototype.updateDiffList = function (template, role) {
        var _this = this;
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "entity_id",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        };
        if (role == 'dest' && this.currentEntity.listTemplate.dest.length > 0) {
            this.currentEntity.listTemplate.dest.forEach(function (listModel) {
                if (listModel.item_id != template.item_id) {
                    _this.currentEntity.listTemplate.cc.push(listModel);
                }
            });
            this.currentEntity.listTemplate.dest = [template];
        }
        this.currentEntity.roles.forEach(function (role) {
            if (role.available == true) {
                if (_this.currentEntity.listTemplate[role.id]) {
                    _this.currentEntity.listTemplate[role.id].forEach(function (listModel) {
                        newDiffList.items.push({
                            "item_id": listModel.item_id,
                            "item_type": listModel.item_type,
                            "item_mode": role.id,
                            "sequence": listModel.sequence
                        });
                    });
                }
            }
        });
        if (this.currentEntity.listTemplate.id) {
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.currentEntity.listTemplate.id, newDiffList)
                .subscribe(function (data) {
                _this.currentEntity.listTemplate.id = data.id;
                _this.notify.success(_this.lang.entityUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.post(this.coreUrl + "rest/listTemplates", newDiffList)
                .subscribe(function (data) {
                _this.currentEntity.listTemplate.id = data.id;
                _this.notify.success(_this.lang.entityUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    EntitiesAdministrationComponent.prototype.updateDiffListVisa = function (template) {
        var _this = this;
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "VISA_CIRCUIT",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        };
        this.currentEntity.visaTemplate.forEach(function (listModel, i) {
            listModel.sequence = i;
            if (i == (_this.currentEntity.visaTemplate.length - 1)) {
                listModel.item_mode = "sign";
            }
            else {
                listModel.item_mode = "visa";
            }
            newDiffList.items.push({
                "id": listModel.id,
                "item_id": listModel.item_id,
                "item_type": "user_id",
                "item_mode": listModel.item_mode,
                "sequence": listModel.sequence
            });
        });
        this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa, newDiffList)
            .subscribe(function (data) {
            _this.idCircuitVisa = data.id;
            _this.notify.success(_this.lang.entityUpdated);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    EntitiesAdministrationComponent.prototype.removeDiffList = function (i, role) {
        var _this = this;
        this.currentEntity.listTemplate[role].splice(i, 1);
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "entity_id",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        };
        this.currentEntity.roles.forEach(function (role) {
            if (role.available == true) {
                if (_this.currentEntity.listTemplate[role.id]) {
                    _this.currentEntity.listTemplate[role.id].forEach(function (listModel) {
                        newDiffList.items.push({
                            "item_id": listModel.item_id,
                            "item_type": listModel.item_type,
                            "item_mode": role.id,
                            "sequence": listModel.sequence
                        });
                    });
                }
            }
        });
        this.http.put(this.coreUrl + "rest/listTemplates/" + this.currentEntity.listTemplate.id, newDiffList)
            .subscribe(function (data) {
            _this.currentEntity.listTemplate.id = data.id;
            _this.notify.success(_this.lang.entityUpdated);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    EntitiesAdministrationComponent.prototype.removeDiffListVisa = function (template, i) {
        var _this = this;
        this.currentEntity.visaTemplate.splice(i, 1);
        if (this.currentEntity.visaTemplate.length > 0) {
            var newDiffList = {
                "object_id": this.currentEntity.entity_id,
                "object_type": "VISA_CIRCUIT",
                "title": this.currentEntity.entity_id,
                "description": this.currentEntity.entity_id,
                "items": Array()
            };
            this.currentEntity.visaTemplate.forEach(function (listModel, i) {
                listModel.sequence = i;
                if (i == (_this.currentEntity.visaTemplate.length - 1)) {
                    listModel.item_mode = "sign";
                }
                else {
                    listModel.item_mode = "visa";
                }
                newDiffList.items.push({
                    "item_id": listModel.item_id,
                    "item_type": "user_id",
                    "item_mode": listModel.item_mode,
                    "sequence": listModel.sequence
                });
            });
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa, newDiffList)
                .subscribe(function (data) {
                _this.idCircuitVisa = data.id;
                _this.notify.success(_this.lang.entityUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.delete(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa)
                .subscribe(function (data) {
                _this.idCircuitVisa = null;
                _this.notify.success(_this.lang.entityUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    __decorate([
        core_1.ViewChild('snav2'),
        __metadata("design:type", material_1.MatSidenav)
    ], EntitiesAdministrationComponent.prototype, "sidenav", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatPaginator),
        __metadata("design:type", material_1.MatPaginator)
    ], EntitiesAdministrationComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], EntitiesAdministrationComponent.prototype, "sort", void 0);
    EntitiesAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["entities-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [core_1.ChangeDetectorRef, layout_1.MediaMatcher, http_1.HttpClient, notification_service_1.NotificationService, material_1.MatDialog])
    ], EntitiesAdministrationComponent);
    return EntitiesAdministrationComponent;
}(autocomplete_plugin_1.AutoCompletePlugin));
exports.EntitiesAdministrationComponent = EntitiesAdministrationComponent;
var EntitiesAdministrationRedirectModalComponent = /** @class */ (function (_super) {
    __extends(EntitiesAdministrationRedirectModalComponent, _super);
    function EntitiesAdministrationRedirectModalComponent(http, data, dialogRef) {
        var _this = _super.call(this, http, ['entities']) || this;
        _this.http = http;
        _this.data = data;
        _this.dialogRef = dialogRef;
        _this.lang = translate_component_1.LANG;
        return _this;
    }
    EntitiesAdministrationRedirectModalComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["entities-administration-redirect-modalView"],
        }),
        __param(1, core_1.Inject(material_1.MAT_DIALOG_DATA)),
        __metadata("design:paramtypes", [http_1.HttpClient, Object, material_1.MatDialogRef])
    ], EntitiesAdministrationRedirectModalComponent);
    return EntitiesAdministrationRedirectModalComponent;
}(autocomplete_plugin_1.AutoCompletePlugin));
exports.EntitiesAdministrationRedirectModalComponent = EntitiesAdministrationRedirectModalComponent;
