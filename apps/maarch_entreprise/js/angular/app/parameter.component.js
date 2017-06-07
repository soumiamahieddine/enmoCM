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
        this.mode = null;
        this.parametersList = null;
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
        //this.dtOptions= {
        //};
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareParameter();
        this.route.params.subscribe(function (params) {
            if (_this.route.toString().includes('list')) {
                _this.changeMode('list');
            }
        });
    };
    ParameterComponent.prototype.loadParametersList = function () {
        var _this = this;
        this.http.get(this.coreUrl + 'rest/parameters')
            .subscribe(function (data) {
            _this.parametersList = JSON.parse(data.text());
            _this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i> Paramètres : " + Object.keys(_this.parametersList).length + " paramètre(s)";
            $j('#pageTitle').html(_this.pageTitle);
            //this.dtTrigger.next();
            setTimeout((function () {
                $j('#paramsTable').DataTable();
            }), 0);
            _this.parameter = {
                id: null,
                param_value_string: null,
                param_value_int: null,
                param_value_date: null,
                description: null
            };
        });
    };
    ParameterComponent.prototype.prepareParameter = function () {
        $j('#inner_content').remove();
    };
    ParameterComponent.prototype.getParameterInfos = function (paramId) {
        var _this = this;
        this.http.get(this.coreUrl + 'rest/parameters/' + paramId)
            .subscribe(function (data) {
            var infoParam = JSON.parse(data.text());
            _this.parameter.id = infoParam[0].id;
            if (infoParam[0].param_value_string != null) {
                _this.parameter.param_value_string = infoParam[0].param_value_string;
                _this.type = "string";
            }
            else if (infoParam[0].param_value_int != null) {
                _this.parameter.param_value_int = infoParam[0].param_value_int;
                _this.type = "int";
            }
            else if (infoParam[0].param_value_date != null) {
                _this.parameter.param_value_date = infoParam[0].param_value_date;
                _this.type = "date";
            }
            _this.parameter.description = infoParam[0].description;
            _this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i> Paramètre : " + _this.parameter.id;
            $j('#pageTitle').html(_this.pageTitle);
        });
    };
    ParameterComponent.prototype.changeMode = function (mode) {
        this.mode = mode;
        if (mode == 'list') {
            this.loadParametersList();
        }
    };
    ParameterComponent.prototype.updateParameter = function (paramId) {
        this.paramId = paramId;
        this.changeMode('update');
        this.getParameterInfos(paramId);
    };
    ParameterComponent.prototype.deleteParameter = function (paramId) {
        var _this = this;
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
                _this.resultInfo = "Paramètre supprimé avec succès";
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
                _this.loadParametersList();
            }
        });
    };
    ParameterComponent.prototype.submitParameter = function () {
        var _this = this;
        if (this.mode == 'add') {
            this.http.post(this.coreUrl + 'rest/parameters', this.parameter)
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
                    _this.resultInfo = "Paramètre créé avec succès";
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                    _this.changeMode('list');
                }
            });
        }
        else if (this.mode == "update") {
            this.http.put(this.coreUrl + 'rest/parameters/' + this.paramId, this.parameter)
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
                    _this.resultInfo = "Mise à jour effectuée";
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                    _this.changeMode('list');
                }
            });
        }
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
