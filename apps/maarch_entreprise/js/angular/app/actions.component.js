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
var ActionsComponent = (function () {
    function ActionsComponent(http) {
        this.http = http;
        this.actions = [];
        this.titles = [];
        this.lang = [];
        this.loading = false;
        this.coreUrl = angularGlobals.coreUrl;
    }
    ActionsComponent.prototype.prepareActions = function () {
        var _this = this;
        $j('#inner_content').remove();
        this.http.get(this.coreUrl + 'rest/administration/actions')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.actions = data['actions'];
            _this.titles = data['titles'];
            _this.lang = data['lang'];
            setTimeout(function () {
                _this.actionsDataTables = $j('#actionsTable').DataTable({
                    "language": {
                        "lengthMenu": _this.lang.display + " _MENU_ " + _this.lang.recordsPerPage,
                        "zeroRecords": _this.lang.noRecords,
                        "info": _this.lang.page + " _PAGE_ " + _this.lang.outOf + " _PAGES_",
                        "infoEmpty": _this.lang.noRecords + " " + _this.lang.available,
                        "infoFiltered": "(" + _this.lang.filteredFrom + " _MAX_ " + _this.lang.records + ")",
                        "search": _this.lang.search,
                        "paginate": {
                            "first": _this.lang.first,
                            "last": _this.lang.last,
                            "next": _this.lang.next,
                            "previous": _this.lang.previous
                        }
                    }
                });
            }, 0);
            _this.loading = false;
        });
    };
    ActionsComponent.prototype.ngOnInit = function () {
        this.loading = true;
        this.prepareActions();
    };
    ActionsComponent.prototype.deleteAction = function (id) {
        var _this = this;
        this.http.delete(this.coreUrl + 'rest/actions/' + id, this.actions)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.errors) {
            }
            else {
                var list = _this.actions;
                for (var i = 0; i < list.length; i++) {
                    if (list[i].id == id) {
                        list.splice(i, 1);
                    }
                }
                _this.actionsDataTables.row($j("#" + id)).remove().draw();
                _this.resultInfo = _this.lang.delete_action;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }
        });
    };
    return ActionsComponent;
}());
ActionsComponent = __decorate([
    core_1.Component({
        templateUrl: 'Views/actions.component.html',
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.Http])
], ActionsComponent);
exports.ActionsComponent = ActionsComponent;
