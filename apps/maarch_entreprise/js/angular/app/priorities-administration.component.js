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
var PrioritiesAdministrationComponent = (function () {
    function PrioritiesAdministrationComponent(http) {
        this.http = http;
        this.priorities = [];
        this.lang = {};
        this.loading = false;
    }
    PrioritiesAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Priorités";
        }
    };
    PrioritiesAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.loading = true;
        this.http.get(this.coreUrl + 'rest/administration/priorities')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.priorities = data.priorities;
            _this.lang = data.lang;
            //setTimeout(() => {
            //    this.datatable = $j('#prioritiesTable').DataTable({
            //        "dom": '<"datatablesLeft"p><"datatablesRight"f><"datatablesCenter"l>rt<"datatablesCenter"i><"clear">',
            //        "lengthMenu": [ 10, 25, 50, 75, 100 ],
            //        "oLanguage": {
            //            "sLengthMenu": "<i class='fa fa-bars'></i> _MENU_",
            //            "sZeroRecords": this.lang.noResult,
            //            "sInfo": "_START_ - _END_ / _TOTAL_ "+this.lang.record,
            //            "sSearch": "",
            //            "oPaginate": {
            //                "sFirst":    "<<",
            //                "sLast":    ">>",
            //                "sNext":    this.lang.next+" <i class='fa fa-caret-right'></i>",
            //                "sPrevious": "<i class='fa fa-caret-left'></i> "+this.lang.previous
            //            },
            //            "sInfoEmpty": this.lang.noRecord,
            //            "sInfoFiltered": "(filtré de _MAX_ "+this.lang.record+")"
            //        },
            //        "order": [[ 1, "asc" ]],
            //        "columnDefs": [
            //            { "orderable": false, "targets": [3,5] }
            //        ]
            //    });
            //    $j('.dataTables_filter input').attr("placeholder", this.lang.search);
            //    $j('dataTables_filter input').addClass('form-control');
            //    $j(".datatablesLeft").css({"float":"left"});
            //    $j(".datatablesCenter").css({"text-align":"center"});
            //    $j(".datatablesRight").css({"float":"right"});
            //} ,0);
            _this.loading = false;
        }, function () {
            location.href = "index.php";
        });
    };
    return PrioritiesAdministrationComponent;
}());
PrioritiesAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["priorities-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.Http])
], PrioritiesAdministrationComponent);
exports.PrioritiesAdministrationComponent = PrioritiesAdministrationComponent;
