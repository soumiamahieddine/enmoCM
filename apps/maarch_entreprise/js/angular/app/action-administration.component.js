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
var ActionAdministrationComponent = (function () {
    function ActionAdministrationComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.mode = null;
        this.action = {};
        this.statusList = [];
        this.actionPagesList = [];
        this.lang = {};
        this.categoriesList = [];
        this.keywordsList = [];
        this.loading = false;
    }
    ActionAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/actions\"' style='cursor: pointer'>Actions</a> > Modification";
        }
    };
    ActionAdministrationComponent.prototype.prepareActions = function () {
        $j('#inner_content').remove();
    };
    ActionAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.prepareActions();
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.mode = 'create';
                _this.http.get(_this.coreUrl + 'rest/initAction')
                    .map(function (res) { return res.json(); })
                    .subscribe(function (data) {
                    _this.action = data.action;
                    _this.lang = data.lang;
                    _this.lang.pageTitle = _this.lang.modify_action + ' : ' + _this.action.action_id;
                    _this.categoriesList = data.categoriesList;
                    _this.statusList = data.statusList;
                    _this.actionPagesList = data.action_pagesList;
                    _this.keywordsList = data.keywordsList;
                    _this.loading = false;
                    setTimeout(function () {
                        $j("select").chosen({ width: "100%", disable_search_threshold: 10, search_contains: true });
                    }, 0);
                });
            }
            else {
                _this.mode = 'update';
                _this.http.get(_this.coreUrl + 'rest/administration/actions/' + params['id'])
                    .map(function (res) { return res.json(); })
                    .subscribe(function (data) {
                    _this.lang = data.lang;
                    _this.lang.pageTitle = _this.lang.add + ' ' + _this.lang.action;
                    _this.action = data.action;
                    _this.categoriesList = data.categoriesList;
                    _this.statusList = data.statusList;
                    _this.actionPagesList = data.action_pagesList;
                    _this.keywordsList = data.keywordsList;
                    _this.loading = false;
                    setTimeout(function () {
                        $j("select").chosen({ width: "100%", disable_search_threshold: 10, search_contains: true });
                    }, 0);
                });
            }
        });
    };
    ActionAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        //affect value of select
        this.action.actionCategories = $j("#categorieslist").chosen().val();
        this.action.id_status = $j("#status").chosen().val();
        this.action.keyword = $j("#keyword").chosen().val();
        this.action.action_page = $j("#action_page").chosen().val();
        if (this.mode == 'create') {
            this.http.post(this.coreUrl + 'rest/actions', this.action)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.router.navigate(['/administration/actions']);
                //TO_DO_NOTIF
            }, function (errors) {
                console.log(errors);
                //TO_DO_NOTIF_ERRORS
            });
        }
        else if (this.mode == 'update') {
            this.http.put(this.coreUrl + 'rest/actions/' + this.action.id, this.action)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.router.navigate(['/administration/actions']);
                //TO_DO_NOTIF
            }, function (errors) {
                console.log(errors);
                //TO_DO_NOTIF_ERRORS
            });
        }
    };
    ActionAdministrationComponent.prototype.selectAll = function (event) {
        var target = event.target.getAttribute("data-target");
        $j('#' + target + ' option').prop('selected', true);
        $j('#' + target).trigger('chosen:updated');
    };
    ActionAdministrationComponent.prototype.unselectAll = function (event) {
        var target = event.target.getAttribute("data-target");
        $j('#' + target + ' option').prop('selected', false);
        $j('#' + target).trigger('chosen:updated');
    };
    return ActionAdministrationComponent;
}());
ActionAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["action-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/action-administration.component.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute, router_1.Router])
], ActionAdministrationComponent);
exports.ActionAdministrationComponent = ActionAdministrationComponent;
