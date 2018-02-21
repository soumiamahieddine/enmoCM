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
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var autocomplete_plugin_1 = require("../../plugins/autocomplete.plugin");
var EntitiesAdministrationComponent = /** @class */ (function (_super) {
    __extends(EntitiesAdministrationComponent, _super);
    function EntitiesAdministrationComponent(changeDetectorRef, media, http, notify) {
        var _this = _super.call(this, http, 'usersAndEntities') || this;
        _this.http = http;
        _this.notify = notify;
        _this.lang = translate_component_1.LANG;
        _this.entities = [];
        _this.entityTypeList = ['Direction', 'Service', 'Bureau'];
        _this.currentEntity = {};
        _this.loading = false;
        _this.creationMode = false;
        _this.listTeamOne = ['Muhammad Ali', 'George Foreman', 'Joe Frazier', 'Jake LaMotta', 'Joe Louis', 'Jack Dempsey', 'Rocky Marciano', 'Mike Tyson', 'Oscar De La Hoya'];
        _this.listTeamTwo = ['Sugar Ray Robinson'];
        $j("link[href='merged_css.php']").remove();
        _this.mobileQuery = media.matchMedia('(max-width: 768px)');
        _this._mobileQueryListener = function () { return changeDetectorRef.detectChanges(); };
        _this.mobileQuery.addListener(_this._mobileQueryListener);
        return _this;
    }
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
        this.http.get(this.coreUrl + "rest/entities")
            .subscribe(function (data) {
            _this.entities = data['entities'];
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
                        'data': _this.entities,
                        "check_callback": true
                    },
                    "plugins": ["search", "dnd", "contextmenu"],
                });
                $j('#jstree')
                    .on('select_node.jstree', function (e, data) {
                    if (_this.creationMode == true) {
                        _this.currentEntity.parent_entity_id = data.node.id;
                    }
                    else {
                        _this.loadEntity(data.node.id);
                    }
                }).on('move_node.jstree', function (e, data) {
                    _this.loadEntity(data.node.id);
                    _this.currentEntity.parent_entity_id = data.parent;
                    _this.moveEntity();
                })
                    .jstree();
            }, 0);
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
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    EntitiesAdministrationComponent.prototype.addElemListModel = function (element) {
        var _this = this;
        var inListModel = false;
        var newElemListModel = {
            "type": element.type,
            "id": element.id,
            "labelToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo,
        };
        this.currentEntity.roles.forEach(function (role) {
            if (role.available == true) {
                if (_this.currentEntity.listTemplate[role.id]) {
                    _this.currentEntity.listTemplate[role.id].forEach(function (listModel) {
                        console.log(listModel);
                        if (listModel.id == element.id) {
                            inListModel = true;
                        }
                    });
                }
            }
        });
        if (!inListModel) {
            this.currentEntity.listTemplate.cc.unshift(newElemListModel);
        }
    };
    EntitiesAdministrationComponent.prototype.saveEntity = function () {
        var _this = this;
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/entities", this.currentEntity)
                .subscribe(function (data) {
                _this.creationMode = false;
                _this.entities.push(_this.currentEntity);
                $j('#jstree').jstree("refresh");
                _this.notify.success(_this.lang.entityAdded);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
                .subscribe(function (data) {
                console.log(data);
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
        $j('#jstree').jstree('deselect_all');
        $j('#jstree').jstree('select_node', this.entities[0]);
    };
    EntitiesAdministrationComponent.prototype.removeEntity = function () {
        var _this = this;
        this.http.delete(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id)
            .subscribe(function (data) {
            _this.entities = data['entities'];
            $j('#jstree').jstree("refresh");
            _this.notify.success(_this.lang.entityDeleted);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    EntitiesAdministrationComponent.prototype.prepareEntityAdd = function () {
        $j('#jstree').jstree('deselect_all');
        this.creationMode = true;
        this.currentEntity = {};
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
    EntitiesAdministrationComponent.prototype.delete = function (entity) {
        var _this = this;
        this.http.delete(this.coreUrl + "rest/entities/" + entity['entity_id'])
            .subscribe(function (data) {
            _this.notify.success(_this.lang.entityDeleted);
            _this.entities = data['entities'];
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    EntitiesAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["entities-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [core_1.ChangeDetectorRef, layout_1.MediaMatcher, http_1.HttpClient, notification_service_1.NotificationService])
    ], EntitiesAdministrationComponent);
    return EntitiesAdministrationComponent;
}(autocomplete_plugin_1.AutoCompletePlugin));
exports.EntitiesAdministrationComponent = EntitiesAdministrationComponent;
