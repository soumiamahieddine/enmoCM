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
var ReportsComponent = (function () {
    function ReportsComponent(http) {
        this.http = http;
        this.test42 = "Ptit test OKLM";
        this.arrayArgsPut = [];
        this.lang = [];
    }
    ReportsComponent.prototype.prepareState = function () {
        $j('#inner_content').remove();
    };
    ReportsComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.prepareState();
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + 'rest/report/groups')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.groups = data['group'];
            _this.lang = data['lang'];
        });
    };
    ReportsComponent.prototype.loadGroup = function () {
        var _this = this;
        this.http.get(this.coreUrl + 'rest/report/groups/' + this.groups[$j("#group_id").prop("selectedIndex") - 1].group_id) // SELECTED ANDGULAR  .selected()
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.checkboxes = data;
            console.log(_this.checkboxes[0].id);
        });
        $j("#formCategoryId").removeClass("hide");
    };
    ReportsComponent.prototype.clickOnCategory = function (id) {
        $j(".category").addClass("hide");
        $j("#" + id).removeClass("hide");
    };
    ReportsComponent.prototype.updateDB = function () {
        var _this = this;
        for (var i = 0; i < $j(":checkbox").length; i++) {
            this.arrayArgsPut.push({ id: this.checkboxes[i].id, checked: $j(":checkbox")[i].checked });
        }
        console.log(this.arrayArgsPut);
        this.http.put(this.coreUrl + 'rest/report/groups/' + this.groups[$j("#group_id").prop("selectedIndex") - 1].group_id, this.arrayArgsPut) // SELECTED ANDGULAR  .selected()
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.arrayArgsPut = [];
        });
    };
    return ReportsComponent;
}());
ReportsComponent = __decorate([
    core_1.Component({
        templateUrl: 'Views/reports.component.html',
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', '../maarch_entreprise/css/reports.css']
    }),
    __metadata("design:paramtypes", [http_1.Http])
], ReportsComponent);
exports.ReportsComponent = ReportsComponent;
