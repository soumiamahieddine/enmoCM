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
var StatusAdministrationComponent = (function () {
    function StatusAdministrationComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.pageTitle = "";
        this.mode = null;
        this.status = {
            id: null,
            description: null,
            can_be_searched: null,
            can_be_modified: null,
            is_folder_status: null,
            img_related: null
        };
        this.lang = "";
        this.resultInfo = "";
    }
    StatusAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareStatus();
        this.route.params.subscribe(function (params) {
            if (_this.route.toString().includes('update')) {
                _this.mode = 'update';
                _this.statusId = params['id'];
                _this.getStatusInfos(_this.statusId);
            }
            else if (_this.route.toString().includes('create')) {
                _this.http.get(_this.coreUrl + 'rest/status/lang')
                    .map(function (res) { return res.json(); })
                    .subscribe(function (data) {
                    _this.lang = data;
                    _this.mode = 'create';
                    _this.pageTitle = _this.lang.newStatus;
                });
            }
        });
    };
    StatusAdministrationComponent.prototype.prepareStatus = function () {
        $j('#inner_content').remove();
    };
    StatusAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        $j('#ariane').html("<a href='index.php?reinit=true'>" + applicationName + "</a> ><a href='index.php?page=admin&reinit=true'> Administration des statuts</a>");
    };
    StatusAdministrationComponent.prototype.getStatusInfos = function (statusId) {
        var _this = this;
        this.http.get(this.coreUrl + 'rest/status/' + statusId)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.errors) {
                _this.resultInfo = data.errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }
            else {
                _this.status = data['status'][0];
                _this.lang = data['lang'];
                _this.pageTitle = _this.lang.modify_status + ' : ' + _this.status.id;
            }
        });
    };
    StatusAdministrationComponent.prototype.submitStatus = function () {
        var _this = this;
        if (this.mode == 'create') {
            this.http.post(this.coreUrl + 'rest/status', this.status)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                if (data.errors) {
                    _this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                }
                else {
                    _this.resultInfo = _this.lang.paramCreatedSuccess;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                    _this.router.navigate(['administration/status']);
                }
            });
        }
        else if (this.mode == "update") {
            this.http.put(this.coreUrl + 'rest/status/' + this.statusId, this.status)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                if (data.errors) {
                    _this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                }
                else {
                    _this.resultInfo = _this.lang.paramUpdatedSuccess;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                    _this.router.navigate(['administration/status']);
                }
            });
        }
    };
    return StatusAdministrationComponent;
}());
StatusAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals['status-administrationView'],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute, router_1.Router])
], StatusAdministrationComponent);
exports.StatusAdministrationComponent = StatusAdministrationComponent;
