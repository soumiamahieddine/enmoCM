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
var http_1 = require("@angular/common/http");
var StatusListAdministrationComponent = (function () {
    function StatusListAdministrationComponent(http) {
        this.http = http;
        this.lang = "";
        this.resultInfo = "";
        this.loading = false;
    }
    StatusListAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareStatus();
        this.loading = true;
        this.http.get(this.coreUrl + 'rest/administration/status')
            .subscribe(function (data) {
            _this.statusList = data.statusList;
            _this.lang = data.lang;
            _this.nbStatus = Object.keys(_this.statusList).length;
            setTimeout(function () {
                _this.table = $j('#statusTable').DataTable({
                    "dom": '<"datatablesLeft"p><"datatablesRight"f><"datatablesCenter"l>rt<"datatablesCenter"i><"clear">',
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
                    "order": [[2, "asc"]],
                    "columnDefs": [
                        { "orderable": false, "targets": [0, 3] }
                    ],
                    "stateSave": true
                });
                $j('.dataTables_filter input').attr("placeholder", _this.lang.search);
                $j('dataTables_filter input').addClass('form-control');
                $j(".datatablesLeft").css({ "float": "left" });
                $j(".datatablesCenter").css({ "text-align": "center" });
                $j(".datatablesRight").css({ "float": "right" });
            }, 0);
            _this.updateBreadcrumb(angularGlobals.applicationName);
            _this.loading = false;
        }, function (err) {
            errorNotification(JSON.parse(err._body).errors);
        });
    };
    StatusListAdministrationComponent.prototype.prepareStatus = function () {
        $j('#inner_content').remove();
    };
    StatusListAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > " +
            "<a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.admin + "</a> > " + this.lang.admin_status;
    };
    StatusListAdministrationComponent.prototype.deleteStatus = function (statusId, statusIdentifier) {
        var _this = this;
        var resp = confirm(this.lang.deleteConfirm + ' ' + statusId + '?');
        if (resp) {
            this.http.delete(this.coreUrl + 'rest/status/' + statusIdentifier)
                .subscribe(function () {
                var list = _this.statusList;
                for (var i = 0; i < list.length; i++) {
                    if (list[i].id == statusId) {
                        list.splice(i, 1);
                    }
                }
                _this.table.row($j("#" + statusId)).remove().draw();
                successNotification(_this.lang.delStatus + " : " + statusId);
                _this.nbStatus = Object.keys(_this.statusList).length;
            }, function (err) {
                errorNotification(JSON.parse(err._body).errors);
            });
        }
    };
    return StatusListAdministrationComponent;
}());
StatusListAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals['statuses-administrationView'],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.HttpClient])
], StatusListAdministrationComponent);
exports.StatusListAdministrationComponent = StatusListAdministrationComponent;
