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
var http_1 = require("@angular/http");
require("rxjs/add/operator/map");
var router_1 = require("@angular/router");
var ActionComponent = (function () {
    function ActionComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.actions = {
            id: "",
            history: "",
            id_status: "",
            action_page: "",
            keyword: "",
            is_folder_action: "",
            label_action: "",
            category_id: [],
            coll_categories: [],
        };
        this.user = {
            lang: {}
        };
        this.statuts = [];
        this.tabAction_page = {
            module: "",
            action: [],
        };
        this.lang = [];
        this.keywords = [];
        this.leftCategories = [];
        this.rightCategories = [];
    }
    ActionComponent.prototype.prepareActions = function () {
        var _this = this;
        $j('#inner_content').remove();
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.initAction();
            }
            else {
                _this.getAction(params['id']);
            }
        });
    };
    ActionComponent.prototype.ngOnInit = function () {
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareActions();
    };
    ActionComponent.prototype.getAction = function (id) {
        var _this = this;
        $j('#create-action').remove();
        this.http.get(this.coreUrl + 'rest/administration/actions/' + id)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            var tab = data;
            _this.lang = data.lang;
            _this.actions.id = id;
            _this.actions.label_action = tab.label_action;
            _this.actions.keyword = tab.keyword;
            _this.actions.id_status = tab.id_status;
            _this.actions.is_folder_action = tab.is_folder_action;
            _this.actions.action_page = tab.action_page;
            _this.actions.history = tab.history;
            _this.actions.category_id = tab['category_id'];
            _this.actions.coll_categories = tab['coll_categories'];
            _this.statuts = tab['statuts'];
            //Add in right select
            var ind = 0;
            for (var i = 0; i < tab['coll_categories'].length; i++) {
                for (var j = 0; j < tab['category_id'].length; j++) {
                    if (tab['category_id'][j].category_id == tab['coll_categories'][i].id) {
                        _this.rightCategories[ind] = tab['coll_categories'][i];
                        ind++;
                    }
                }
            }
            //Add in left select
            ind = 0;
            var bool = false;
            for (var i = 0; i < tab['coll_categories'].length; i++) {
                for (var j = 0; j < _this.rightCategories.length; j++) {
                    if (_this.rightCategories[j].id == tab['coll_categories'][i].id) {
                        bool = true;
                    }
                }
                if (!bool) {
                    _this.leftCategories[ind] = tab['coll_categories'][i];
                    ind++;
                }
                bool = false;
            }
            _this.tabAction_page.module = tab['tab_action_page']['modules'];
            _this.tabAction_page.actions = tab['tab_action_page']['actions'];
            _this.keywords = tab['keywords'];
        });
    };
    ActionComponent.prototype.updateAction = function () {
        var _this = this;
        this.actions.category_id = this.rightCategories;
        this.http.put(this.coreUrl + 'rest/actions/' + this.actions.id, this.actions)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.errors) {
            }
            else {
                _this.router.navigate(['/administration/actions']);
                setTimeout(function () {
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").text(_this.lang.action_modified);
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                }, 0);
            }
        }, function (errors) {
            $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
            $j("#resultInfo").text(JSON.parse(errors._body).errors);
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        });
    };
    ActionComponent.prototype.createAction = function () {
        var _this = this;
        this.actions.category_id = this.rightCategories;
        delete this.actions.id;
        delete this.actions.coll_categories;
        this.http.post(this.coreUrl + 'rest/actions', this.actions)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.errors) {
            }
            else {
                _this.router.navigate(['/administration/actions']);
                _this.resultInfo = _this.lang.action_added;
                setTimeout(function () {
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").text(_this.lang.action_added);
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                }, 0);
            }
        }, function (errors) {
            $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
            $j("#resultInfo").text(JSON.parse(errors._body).errors);
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        });
    };
    ActionComponent.prototype.move = function (id, mode) {
        if (mode == "add") {
            var catClick = $j('#' + id).val();
            for (var i = 0; i < this.leftCategories.length; i++) {
                for (var j = 0; j < catClick.length; j++) {
                    if (this.leftCategories[i].id == catClick[j]) {
                        this.rightCategories.push(this.leftCategories[i]);
                        this.leftCategories.splice(i, 1);
                    }
                }
            }
        }
        if (mode == "remove") {
            var catClick = $j('#' + id).val();
            for (var i = 0; i < this.rightCategories.length; i++) {
                for (var j = 0; j < catClick.length; j++) {
                    if (this.rightCategories[i].id == catClick[j]) {
                        this.leftCategories.push(this.rightCategories[i]);
                        this.rightCategories.splice(i, 1);
                    }
                }
            }
        }
    };
    ActionComponent.prototype.moveClick = function (id, mode) {
        var find = false;
        if (mode == "add") {
            var catClick = $j('#' + id).val();
            var i = 0;
            while (i < this.leftCategories.length && !find) {
                if (this.leftCategories[i].id == catClick[0]) {
                    this.rightCategories.push(this.leftCategories[i]);
                    this.leftCategories.splice(i, 1);
                    find = true;
                }
                i++;
            }
        }
        if (mode == "remove") {
            var catClick = $j('#' + id).val();
            var i = 0;
            while (i < this.rightCategories.length && !find) {
                if (this.rightCategories[i].id == catClick[0]) {
                    this.leftCategories.push(this.rightCategories[i]);
                    this.rightCategories.splice(i, 1);
                    find = true;
                }
                i++;
            }
        }
    };
    ActionComponent.prototype.initAction = function () {
        var _this = this;
        $j('#update-action').remove();
        this.http.get(this.coreUrl + 'rest/initAction')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            var tab = data;
            _this.lang = data.lang;
            _this.leftCategories = tab['coll_categories'];
            _this.actions.id_status = tab['statuts'][0].id;
            _this.statuts = tab['statuts'];
            _this.tabAction_page.module = tab['tab_action_page']['modules'];
            _this.tabAction_page.actions = tab['tab_action_page']['actions'];
            _this.actions.action_page = tab['tab_action_page']['actions'][0].name;
            _this.actions.history = tab.history;
            _this.actions.is_folder_action = tab.is_folder_action;
            _this.actions.keyword = tab['keywords'][0].value;
            _this.keywords = tab['keywords'];
        });
    };
    return ActionComponent;
}());
ActionComponent = __decorate([
    core_1.Component({
        templateUrl: 'Views/action.component.html',
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'js/angular/app/Css/actions.component.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute, router_1.Router])
], ActionComponent);
exports.ActionComponent = ActionComponent;
