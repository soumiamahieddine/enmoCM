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
var StateEditionComponent = (function () {
    function StateEditionComponent(http) {
        this.http = http;
        this.test42 = "Ptit test OKLM";
    }
    StateEditionComponent.prototype.prepareState = function () {
        $j('#inner_content').remove();
    };
    StateEditionComponent.prototype.ngOnInit = function () {
        this.prepareState();
    };
    return StateEditionComponent;
}());
StateEditionComponent = __decorate([
    core_1.Component({
        templateUrl: 'Views/stateEdition.component.html',
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', '/maarch_entreprise/js/angular/app/Css/report.css']
    }),
    __metadata("design:paramtypes", [http_1.Http])
], StateEditionComponent);
exports.StateEditionComponent = StateEditionComponent;
