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
var ParametersAdministrationComponent = (function () {
    function ParametersAdministrationComponent(http) {
        this.http = http;
        this.lang = "";
        this.resultInfo = "";
        this.loading = false;
    }
    ParametersAdministrationComponent.prototype.prepareParameter = function () {
        $j('#inner_content').remove();
    };
    ParametersAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Paramètres";
    };
    ParametersAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareParameter();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.http.get(this.coreUrl + 'rest/administration/parameters')
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
                setTimeout(function () {
                    _this.table = $j('#paramsTable').DataTable({
                        "dom": '<"datatablesLeft"l><"datatablesRight"f><"datatablesCenter"p>rt<"datatablesCenter"i><"clear">',
                        "lengthMenu": [10, 25, 50, 75, 100],
                        "oLanguage": {
                            "sLengthMenu": "<i class='fa fa-bars'></i> _MENU_",
                            "sZeroRecords": _this.lang.noResult,
                            "sInfo": "_START_ - _END_ / _TOTAL_ " + _this.lang.record,
                            "sSearch": "",
                            "oPaginate": {
                                "sFirst": "<<",
                                "sLast": ">>",
                                "sNext": _this.lang.next + " <i class='fa fa-caret-right'></i>",
                                "sPrevious": "<i class='fa fa-caret-left'></i> " + _this.lang.previous
                            },
                            "sInfoEmpty": _this.lang.noRecord,
                            "sInfoFiltered": "(filtré de _MAX_ " + _this.lang.record + ")"
                        },
                        "order": [[0, "asc"]],
                        "columnDefs": [
                            { "orderable": false, "targets": 3 }
                        ],
                        "fnInitComplete": function () {
                            $j('#paramsTable').show();
                        },
                        stateSave: true
                    });
                    $j('.dataTables_filter input').attr("placeholder", _this.lang.search);
                    $j('dataTables_filter input').addClass('form-control');
                    $j(".datatablesLeft").css({ "float": "left" });
                    $j(".datatablesCenter").css({ "text-align": "center" });
                    $j(".datatablesRight").css({ "float": "right" });
                }, 0);
                _this.loading = false;
            }
        });
    };
    ParametersAdministrationComponent.prototype.goUrl = function () {
        location.href = 'index.php?admin=parameters&page=control_param_technic';
    };
    ParametersAdministrationComponent.prototype.deleteParameter = function (paramId) {
        var _this = this;
        var resp = confirm(this.lang.deleteConfirm + ' ' + paramId + '?');
        if (resp) {
            this.http.delete(this.coreUrl + 'rest/parameters/' + paramId)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                for (var i = 0; i < _this.parametersList.length; i++) {
                    if (_this.parametersList[i].id == paramId) {
                        _this.parametersList.splice(i, 1);
                    }
                }
                _this.table.row($j("#" + paramId)).remove().draw();
                successNotification(data.success);
            }, function (err) {
                errorNotification(JSON.parse(err._body).errors);
            });
        }
    };
    return ParametersAdministrationComponent;
}());
ParametersAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["parameters-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/parameters-administration.component.css']
    }),
    __metadata("design:paramtypes", [http_1.Http])
], ParametersAdministrationComponent);
exports.ParametersAdministrationComponent = ParametersAdministrationComponent;
