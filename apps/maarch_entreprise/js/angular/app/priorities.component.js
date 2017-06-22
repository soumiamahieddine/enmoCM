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
var prioritiesDataTable;
var PrioritiesComponent = (function () {
    function PrioritiesComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.resultInfo = "";
    }
    PrioritiesComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        console.log(this.coreUrl);
        this.preparePriorities();
        this.http.get(this.coreUrl + 'rest/priorities')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.errors) {
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }
            else {
                _this.prioritiesList = data.prioritiesList;
                setTimeout(function () {
                    prioritiesDataTable = $j('#prioritiesTable');
                }, 0);
            }
        });
    };
    PrioritiesComponent.prototype.preparePriorities = function () {
        $j('#inner_content').remove();
    };
    return PrioritiesComponent;
}());
PrioritiesComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals.prioritiesView,
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/parameter.component.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute, router_1.Router])
], PrioritiesComponent);
exports.PrioritiesComponent = PrioritiesComponent;
