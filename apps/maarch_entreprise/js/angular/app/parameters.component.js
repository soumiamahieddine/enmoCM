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
var parametersDataTable;
var ParametersComponent = (function () {
    function ParametersComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.lang = "";
        this.resultInfo = "";
    }
    ParametersComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareParameter();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.http.get(this.coreUrl + 'rest/parameters')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.errors) {
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }
            else {
                _this.parametersList = data.parametersList;
                _this.lang = data.lang;
                var list = _this.parametersList;
                _this.nbParameters = Object.keys(_this.parametersList).length;
                _this.pageTitle = _this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i>" + _this.lang.parameter + "s : " + _this.nbParameters + " " + _this.lang.parameter + "(s)";
                $j('#pageTitle').html(_this.pageTitle);
                _this.parametersList;
                var tempLang = _this.lang;
                setTimeout(function () {
                    parametersDataTable = $j('#paramsTable').DataTable({
                        "language": {
                            "lengthMenu": tempLang.display + " _MENU_ " + tempLang.recordsPerPage,
                            "zeroRecords": tempLang.noRecords,
                            "info": tempLang.page + " _PAGE_ " + tempLang.outOf + " _PAGES_",
                            "infoEmpty": tempLang.noRecords + " " + tempLang.available,
                            "infoFiltered": "(" + tempLang.filteredFrom + " _MAX_ " + tempLang.records + ")",
                            "search": tempLang.search,
                            "paginate": {
                                "first": tempLang.first,
                                "last": tempLang.last,
                                "next": tempLang.next,
                                "previous": tempLang.previous
                            }
                        }
                    });
                }, 0);
            }
        });
    };
    ParametersComponent.prototype.goUrl = function () {
        location.href = 'index.php?admin=parameters&page=control_param_technic';
    };
    ParametersComponent.prototype.prepareParameter = function () {
        $j('#inner_content').remove();
    };
    ParametersComponent.prototype.updateBreadcrumb = function (applicationName) {
        $j('#ariane').html("<a href='index.php?reinit=true'>" + applicationName + "</a> ><a href='index.php?page=admin&reinit=true'> Administration</a> > Paramètres");
    };
    ParametersComponent.prototype.deleteParameter = function (paramId) {
        var _this = this;
        var resp = confirm(this.lang.deleteConfirm + ' ' + paramId + '?');
        if (resp) {
            this.http.delete(this.coreUrl + 'rest/parameters/' + paramId)
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
                    var list = _this.parametersList;
                    for (var i = 0; i < list.length; i++) {
                        if (list[i].id == paramId) {
                            list.splice(i, 1);
                        }
                    }
                    parametersDataTable.row($j("#" + paramId)).remove().draw();
                    _this.resultInfo = "Paramètre supprimé avec succès";
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                    _this.nbParameters = Object.keys(_this.parametersList).length;
                    _this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i>" + _this.lang.parameter + "s : " + _this.nbParameters + " " + _this.lang.parameter + "(s)";
                    $j('#pageTitle').html(_this.pageTitle);
                }
            });
        }
    };
    return ParametersComponent;
}());
ParametersComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals.parametersView,
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/parameter.component.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute, router_1.Router])
], ParametersComponent);
exports.ParametersComponent = ParametersComponent;
