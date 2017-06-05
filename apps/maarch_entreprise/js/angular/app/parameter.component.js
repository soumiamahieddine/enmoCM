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
var ParameterComponent = (function () {
    function ParameterComponent(http, route) {
        this.http = http;
        this.route = route;
        this.test = 'test';
        this.mode = "update";
        this.parameter = {
            id: null,
            param_value_string: null,
            param_value_int: null,
            param_value_date: null,
            description: null
        };
        this.resultInfo = "";
    }
    ParameterComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareParameter();
        this.route.params.subscribe(function (params) {
            _this.paramId = params['id'];
            if (_this.paramId != null) {
                _this.http.get(_this.coreUrl + 'rest/parameters/' + _this.paramId)
                    .map(function (res) { return res.json(); })
                    .subscribe(function (data) {
                    if (data.error) {
                        console.log(data.errors);
                        return;
                    }
                    _this.parameter.id = data.id;
                });
            }
        });
    };
    ParameterComponent.prototype.prepareParameter = function () {
        console.log("PREPARE");
        $j('#inner_content').remove();
    };
    ParameterComponent.prototype.submitParameter = function () {
        var _this = this;
        console.log(this.parameter);
        this.http.post(this.coreUrl + 'rest/parameters', this.parameter)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.errors) {
                _this.resultInfo = data.errors;
                console.log(_this.resultInfo);
            }
            else {
                console.log('SUCCES');
            }
        });
        ;
    };
    return ParameterComponent;
}());
ParameterComponent = __decorate([
    core_1.Component({
        templateUrl: 'Views/parameter.component.html',
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute])
], ParameterComponent);
exports.ParameterComponent = ParameterComponent;
