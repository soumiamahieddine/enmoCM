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
var http_1 = require("@angular/common/http");
var router_1 = require("@angular/router");
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var material_1 = require("@angular/material");
var autocomplete_plugin_1 = require("../../plugins/autocomplete.plugin");
var BasketAdministrationComponent = /** @class */ (function () {
    function BasketAdministrationComponent(http, route, router, notify, dialog) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.notify = notify;
        this.dialog = dialog;
        this.lang = translate_component_1.LANG;
        this.config = {};
        this.basket = {};
        this.basketGroups = [];
        this.allGroups = [];
        this.actionsList = [];
        this.resultPages = [];
        this.loading = false;
        this.displayedColumns = ['label_action', 'actions'];
    }
    BasketAdministrationComponent.prototype.applyFilter = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    };
    BasketAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/baskets\"' style='cursor: pointer'>" + this.lang.baskets + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.basketCreation;
        }
        else {
            breadCrumb += this.lang.basketModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    };
    BasketAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.creationMode = true;
                _this.updateBreadcrumb(angularGlobals.applicationName);
                _this.basketIdAvailable = false;
                _this.loading = false;
            }
            else {
                _this.creationMode = false;
                _this.updateBreadcrumb(angularGlobals.applicationName);
                _this.basketIdAvailable = true;
                _this.id = params['id'];
                _this.http.get(_this.coreUrl + "rest/baskets/" + _this.id)
                    .subscribe(function (data) {
                    _this.basket = data.basket;
                    _this.basket.id = data.basket.basket_id;
                    _this.basket.name = data.basket.basket_name;
                    _this.basket.description = data.basket.basket_desc;
                    _this.basket.clause = data.basket.basket_clause;
                    _this.basket.isSearchBasket = data.basket.is_visible != "Y";
                    _this.basket.isFolderBasket = data.basket.is_folder_basket == "Y";
                    _this.basket.flagNotif = data.basket.flag_notif == "Y";
                    _this.http.get(_this.coreUrl + "rest/baskets/" + _this.id + "/groups")
                        .subscribe(function (data) {
                        _this.allGroups = data.allGroups;
                        _this.allGroups.forEach(function (tmpAllGroup) {
                            tmpAllGroup.isUsed = false;
                            data.groups.forEach(function (tmpGroup) {
                                if (tmpAllGroup.group_id == tmpGroup.group_id) {
                                    tmpAllGroup.isUsed = true;
                                }
                            });
                        });
                        data.groups.forEach(function (tmpGroup) {
                            tmpGroup.groupActions.forEach(function (tmpAction) {
                                tmpAction.used_in_basketlist = tmpAction.used_in_basketlist == "Y";
                                tmpAction.used_in_action_page = tmpAction.used_in_action_page == "Y";
                                tmpAction.default_action_list = tmpAction.default_action_list == "Y";
                            });
                        });
                        _this.basketGroups = data.groups;
                        _this.resultPages = data.pages;
                        _this.loading = false;
                    }, function () {
                        location.href = "index.php";
                    });
                }, function () {
                    location.href = "index.php";
                });
            }
        });
    };
    BasketAdministrationComponent.prototype.openSettings = function (group, action) {
        var _this = this;
        this.config = { data: { group: group, action: action, pages: this.resultPages } };
        this.dialogRef = this.dialog.open(BasketAdministrationSettingsModalComponent, this.config);
        this.dialogRef.afterClosed().subscribe(function (result) {
            if (result) {
                _this.http.put(_this.coreUrl + "rest/baskets/" + _this.id + "/groups/" + result.group.group_id, { 'result_page': result.group.result_page, 'groupActions': result.group.groupActions })
                    .subscribe(function (data) {
                    //this.basketGroups.push(data);
                    _this.notify.success(_this.lang.basketUpdated);
                }, function (err) {
                    _this.notify.error(err.error.errors);
                });
            }
            _this.dialogRef = null;
        });
    };
    BasketAdministrationComponent.prototype.isAvailable = function () {
        var _this = this;
        if (this.basket.id) {
            this.http.get(this.coreUrl + "rest/baskets/" + this.basket.id)
                .subscribe(function () {
                _this.basketIdAvailable = false;
            }, function (err) {
                _this.basketIdAvailable = false;
                if (err.error.errors == "Basket not found") {
                    _this.basketIdAvailable = true;
                }
            });
        }
        else {
            this.basketIdAvailable = false;
        }
    };
    BasketAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/baskets", this.basket)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.basketAdded);
                _this.router.navigate(["/administration/baskets"]);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/baskets/" + this.id, this.basket)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.basketUpdated);
                _this.router.navigate(["/administration/baskets"]);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    BasketAdministrationComponent.prototype.toggleKeywordHelp = function () {
        $j('#keywordHelp').toggle("slow");
    };
    BasketAdministrationComponent.prototype.initAction = function (groupIndex) {
        this.dataSource = new material_1.MatTableDataSource(this.basketGroups[groupIndex].groupActions);
        this.dataSource.sort = this.sort;
    };
    BasketAdministrationComponent.prototype.setDefaultAction = function (group, action) {
        group.groupActions.forEach(function (tmpAction) {
            if (action.id == tmpAction.id) {
                tmpAction.default_action_list = true;
            }
            else {
                tmpAction.default_action_list = false;
            }
        });
    };
    BasketAdministrationComponent.prototype.unlinkGroup = function (groupIndex) {
        var _this = this;
        var r = confirm(this.lang.unlinkGroup + ' ?');
        if (r) {
            this.http.delete(this.coreUrl + "rest/baskets/" + this.id + "/groups/" + this.basketGroups[groupIndex].group_id)
                .subscribe(function (data) {
                _this.allGroups.forEach(function (tmpGroup) {
                    if (tmpGroup.group_id == _this.basketGroups[groupIndex].group_id) {
                        tmpGroup.isUsed = false;
                    }
                });
                _this.basketGroups.splice(groupIndex, 1);
                _this.notify.success(_this.lang.basketUpdated);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    BasketAdministrationComponent.prototype.linkGroup = function () {
        var _this = this;
        this.config = { data: { basketId: this.basket.basket_id, groups: this.allGroups, linkedGroups: this.basketGroups } };
        this.dialogRef = this.dialog.open(BasketAdministrationGroupListModalComponent, this.config);
        this.dialogRef.afterClosed().subscribe(function (result) {
            if (result) {
                _this.http.post(_this.coreUrl + "rest/baskets/" + _this.id + "/groups", result)
                    .subscribe(function (data) {
                    _this.basketGroups.push(result);
                    _this.allGroups.forEach(function (tmpGroup) {
                        if (tmpGroup.group_id == result.group_id) {
                            tmpGroup.isUsed = true;
                        }
                    });
                    _this.notify.success(_this.lang.basketUpdated);
                }, function (err) {
                    _this.notify.error(err.error.errors);
                });
            }
            _this.dialogRef = null;
        });
    };
    BasketAdministrationComponent.prototype.addAction = function (group) {
        var _this = this;
        this.http.put(this.coreUrl + "rest/baskets/" + this.id + "/groups/" + group.group_id, { 'result_page': group.result_page, 'groupActions': group.groupActions })
            .subscribe(function (data) {
            //this.basketGroups.push(data);
            _this.notify.success(_this.lang.basketUpdated);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    __decorate([
        core_1.ViewChild(material_1.MatPaginator),
        __metadata("design:type", material_1.MatPaginator)
    ], BasketAdministrationComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], BasketAdministrationComponent.prototype, "sort", void 0);
    BasketAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["basket-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService, material_1.MatDialog])
    ], BasketAdministrationComponent);
    return BasketAdministrationComponent;
}());
exports.BasketAdministrationComponent = BasketAdministrationComponent;
var BasketAdministrationSettingsModalComponent = /** @class */ (function (_super) {
    __extends(BasketAdministrationSettingsModalComponent, _super);
    function BasketAdministrationSettingsModalComponent(http, data, dialogRef) {
        var _this = _super.call(this, http, 'users') || this;
        _this.http = http;
        _this.data = data;
        _this.dialogRef = dialogRef;
        _this.lang = translate_component_1.LANG;
        _this.allEntities = [];
        return _this;
    }
    BasketAdministrationSettingsModalComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.http.get(this.coreUrl + "rest/entities")
            .subscribe(function (entities) {
            var keywordEntities = [{
                    id: 'ALL_ENTITIES',
                    keyword: 'ALL_ENTITIES',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: 'Toute les entités'
                }, {
                    id: 'ENTITIES_JUST_BELOW',
                    keyword: 'ENTITIES_JUST_BELOW',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Immédiatement inférieur à mon entité primaire"
                }, {
                    id: 'ENTITIES_BELOW',
                    keyword: 'ENTITIES_BELOW',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Inférieur à toutes mes entités"
                }, {
                    id: 'ALL_ENTITIES_BELOW',
                    keyword: 'ALL_ENTITIES_BELOW',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Inférieur à mon entité primaire"
                }, {
                    id: 'MY_ENTITIES',
                    keyword: 'MY_ENTITIES',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Mes entités"
                }, {
                    id: 'MY_PRIMARY_ENTITY',
                    keyword: 'MY_PRIMARY_ENTITY',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Mon entité primaire"
                }, {
                    id: 'SAME_LEVEL_ENTITIES',
                    keyword: 'SAME_LEVEL_ENTITIES',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Même niveau de mon entité primaire"
                }];
            keywordEntities.forEach(function (keyword) {
                _this.allEntities.push(keyword);
            });
            entities.entities.forEach(function (entity) {
                _this.allEntities.push(entity);
            });
        }, function () {
            location.href = "index.php";
        });
        this.http.get(this.coreUrl + 'rest/statuses')
            .subscribe(function (data) {
            _this.statuses = data.statuses;
        });
    };
    BasketAdministrationSettingsModalComponent.prototype.initService = function () {
        var _this = this;
        this.allEntities.forEach(function (entity) {
            entity.state = { "opened": false, "selected": false };
            _this.data.action.redirects.forEach(function (keyword) {
                if (entity.id == keyword.keyword && keyword.redirect_mode == 'ENTITY') {
                    entity.state = { "opened": true, "selected": true };
                }
            });
        });
        $j('#jstree').jstree({
            "checkbox": {
                "three_state": false //no cascade selection
            },
            'core': {
                'themes': {
                    'name': 'proton',
                    'responsive': true
                },
                'data': this.allEntities
            },
            "plugins": ["checkbox", "search"]
        });
        $j('#jstree')
            .on('select_node.jstree', function (e, data) {
            if (data.node.original.keyword) {
                _this.data.action.redirects.push({ action_id: _this.data.action.id, entity_id: '', keyword: data.node.id, redirect_mode: 'ENTITY' });
            }
            else {
                _this.data.action.redirects.push({ action_id: _this.data.action.id, entity_id: data.node.id, keyword: '', redirect_mode: 'ENTITY' });
            }
        }).on('deselect_node.jstree', function (e, data) {
            _this.data.action.redirects.forEach(function (redirect) {
                if (data.node.original.keyword) {
                    if (redirect.keyword == data.node.original.keyword) {
                        var index = _this.data.action.redirects.indexOf(redirect);
                        _this.data.action.redirects.splice(index, 1);
                    }
                }
                else {
                    if (redirect.entity_id == data.node.id) {
                        var index = _this.data.action.redirects.indexOf(redirect);
                        _this.data.action.redirects.splice(index, 1);
                    }
                }
            });
        })
            .jstree();
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
    };
    BasketAdministrationSettingsModalComponent.prototype.initService2 = function () {
        var _this = this;
        this.allEntities.forEach(function (entity) {
            entity.state = { "opened": false, "selected": false };
            _this.data.action.redirects.forEach(function (keyword) {
                if (entity.id == keyword.keyword && keyword.redirect_mode == 'USERS') {
                    entity.state = { "opened": true, "selected": true };
                }
            });
        });
        $j('#jstree2').jstree({
            "checkbox": {
                "three_state": false //no cascade selection
            },
            'core': {
                'themes': {
                    'name': 'proton',
                    'responsive': true
                },
                'data': this.allEntities
            },
            "plugins": ["checkbox", "search"]
        });
        $j('#jstree2')
            .on('select_node.jstree', function (e, data) {
            if (data.node.original.keyword) {
                _this.data.action.redirects.push({ action_id: _this.data.action.id, entity_id: '', keyword: data.node.id, redirect_mode: 'USERS' });
            }
            else {
                _this.data.action.redirects.push({ action_id: _this.data.action.id, entity_id: data.node.id, keyword: '', redirect_mode: 'USERS' });
            }
        }).on('deselect_node.jstree', function (e, data) {
            _this.data.action.redirects.forEach(function (redirect) {
                if (data.node.original.keyword) {
                    if (redirect.keyword == data.node.original.keyword) {
                        var index = _this.data.action.redirects.indexOf(redirect);
                        _this.data.action.redirects.splice(index, 1);
                    }
                }
                else {
                    if (redirect.entity_id == data.node.id) {
                        var index = _this.data.action.redirects.indexOf(redirect);
                        _this.data.action.redirects.splice(index, 1);
                    }
                }
            });
        })
            .jstree();
        var to = false;
        $j('#jstree_search2').keyup(function () {
            if (to) {
                clearTimeout(to);
            }
            to = setTimeout(function () {
                var v = $j('#jstree_search2').val();
                $j('#jstree2').jstree(true).search(v);
            }, 250);
        });
    };
    BasketAdministrationSettingsModalComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["basket-administration-settings-modalView"],
            styles: [".mat-dialog-content{height: 65vh;}"]
        }),
        __param(1, core_1.Inject(material_1.MAT_DIALOG_DATA)),
        __metadata("design:paramtypes", [http_1.HttpClient, Object, material_1.MatDialogRef])
    ], BasketAdministrationSettingsModalComponent);
    return BasketAdministrationSettingsModalComponent;
}(autocomplete_plugin_1.AutoCompletePlugin));
exports.BasketAdministrationSettingsModalComponent = BasketAdministrationSettingsModalComponent;
var forms_1 = require("@angular/forms");
var BasketAdministrationGroupListModalComponent = /** @class */ (function () {
    function BasketAdministrationGroupListModalComponent(http, data, dialogRef, _formBuilder) {
        this.http = http;
        this.data = data;
        this.dialogRef = dialogRef;
        this._formBuilder = _formBuilder;
        this.lang = translate_component_1.LANG;
        this.displayedColumns = ['label_action'];
        this.actionAll = [];
        this.newBasketGroup = {};
    }
    BasketAdministrationGroupListModalComponent.prototype.applyFilter = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    };
    BasketAdministrationGroupListModalComponent.prototype.applyFilter2 = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource2.filter = filterValue;
    };
    BasketAdministrationGroupListModalComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + "rest/actions")
            .subscribe(function (data) {
            data.actions.forEach(function (tmpAction) {
                tmpAction.where_clause = "";
                tmpAction.used_in_basketlist = false;
                tmpAction.default_action_list = false;
                tmpAction.used_in_action_page = true;
                tmpAction.statuses = [];
                tmpAction.redirects = [];
                tmpAction.checked = false;
                _this.actionAll.push(tmpAction);
            });
            _this.dataSource = new material_1.MatTableDataSource(_this.actionAll);
            _this.dataSource.sort = _this.sort;
            _this.dataSource.paginator = _this.paginator;
            _this.dataSource2 = new material_1.MatTableDataSource(_this.actionAll);
            _this.dataSource2.sort = _this.sort;
            _this.dataSource2.paginator = _this.paginator2;
        }, function (err) {
            location.href = "index.php";
        });
        this.firstFormGroup = this._formBuilder.group({
            firstCtrl: ['', forms_1.Validators.required]
        });
        this.secondFormGroup = this._formBuilder.group({
            secondCtrl: ['', forms_1.Validators.required]
        });
        this.data.groups.forEach(function (tmpGroup) {
            _this.data.linkedGroups.forEach(function (tmpLinkedGroup) {
                if (tmpGroup.group_id == tmpLinkedGroup.group_id) {
                    var index = _this.data.groups.indexOf(tmpGroup);
                    _this.data.groups.splice(index, 1);
                }
            });
        });
    };
    BasketAdministrationGroupListModalComponent.prototype.initAction = function (actionType) {
        this.dataSource.filter = actionType.value;
    };
    BasketAdministrationGroupListModalComponent.prototype.selectDefaultAction = function (action) {
        this.actionAll.forEach(function (tmpAction) {
            if (action.id == tmpAction.id) {
                tmpAction.checked = true;
                tmpAction.default_action_list = true;
            }
            else {
                tmpAction.checked = false;
                tmpAction.default_action_list = false;
            }
        });
    };
    BasketAdministrationGroupListModalComponent.prototype.selectAction = function (e, action) {
        action.checked = e.checked;
    };
    BasketAdministrationGroupListModalComponent.prototype.validateForm = function () {
        this.newBasketGroup.group_id = this.groupId;
        this.newBasketGroup.basket_id = this.data.basketId;
        this.newBasketGroup.result_page = 'list_with_attachments';
        this.newBasketGroup.groupActions = this.actionAll;
        this.dialogRef.close(this.newBasketGroup);
    };
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], BasketAdministrationGroupListModalComponent.prototype, "sort", void 0);
    __decorate([
        core_1.ViewChild('paginator'),
        __metadata("design:type", material_1.MatPaginator)
    ], BasketAdministrationGroupListModalComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild('paginator2'),
        __metadata("design:type", material_1.MatPaginator)
    ], BasketAdministrationGroupListModalComponent.prototype, "paginator2", void 0);
    BasketAdministrationGroupListModalComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["basket-administration-groupList-modalView"],
            styles: [".mat-dialog-content{height: 65vh;}"]
        }),
        __param(1, core_1.Inject(material_1.MAT_DIALOG_DATA)),
        __metadata("design:paramtypes", [http_1.HttpClient, Object, material_1.MatDialogRef, forms_1.FormBuilder])
    ], BasketAdministrationGroupListModalComponent);
    return BasketAdministrationGroupListModalComponent;
}());
exports.BasketAdministrationGroupListModalComponent = BasketAdministrationGroupListModalComponent;
