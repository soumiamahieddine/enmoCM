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
var statusDataTable;
var StatusListAdministrationComponent = (function () {
    function StatusListAdministrationComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.lang = "";
        this.resultInfo = "";
    }
    StatusListAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareStatus();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.http.get(this.coreUrl + 'rest/status')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.errors) {
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }
            else {
                _this.statusList = data.statusList;
                _this.lang = data.lang;
                _this.nbStatus = Object.keys(_this.statusList).length;
                _this.pageTitle = _this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i>" + _this.lang.status + "s : " + _this.nbStatus + " " + _this.lang.status + "(s)";
                $j('#pageTitle').html(_this.pageTitle);
                var test = _this.statusList;
                var tempLang = _this.lang;
                setTimeout(function () {
                    statusDataTable = $j('#paramsTable').DataTable({
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
    StatusListAdministrationComponent.prototype.prepareStatus = function () {
        $j('#inner_content').remove();
    };
    StatusListAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        $j('#ariane').html("<a href='index.php?reinit=true'>" + applicationName + "</a> ><a href='index.php?page=admin&reinit=true'> Administration</a> > Statuts");
    };
    StatusListAdministrationComponent.prototype.deleteStatus = function (statusId) {
        var _this = this;
        var resp = confirm(this.lang.deleteConfirm + ' ' + statusId + '?');
        if (resp) {
            this.http.delete(this.coreUrl + 'rest/status/' + statusId)
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
                    var list = _this.statusList;
                    for (var i = 0; i < list.length; i++) {
                        if (list[i].id == statusId) {
                            list.splice(i, 1);
                        }
                    }
                    statusDataTable.row($j("#" + statusId)).remove().draw();
                    _this.resultInfo = "Statut supprimé avec succès";
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                    _this.nbStatus = Object.keys(_this.statusList).length;
                    _this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i>" + _this.lang.parameter + "s : " + _this.nbStatus + " " + _this.lang.parameter + "(s)";
                    $j('#pageTitle').html(_this.pageTitle);
                }
            });
        }
    };
    return StatusListAdministrationComponent;
}());
StatusListAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals['status-list-administrationView'],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute, router_1.Router])
], StatusListAdministrationComponent);
exports.StatusListAdministrationComponent = StatusListAdministrationComponent;
