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
var ParameterAdministrationComponent = (function () {
    function ParameterAdministrationComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.creationMode = true;
        this.parameter = {};
        this.lang = "";
        this.resultInfo = "";
        this.loading = false;
    }
    ParameterAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareParameter();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.creationMode = true;
                _this.http.get(_this.coreUrl + 'rest/administration/parameters/new')
                    .map(function (res) { return res.json(); })
                    .subscribe(function (data) {
                    _this.lang = data.lang;
                    _this.type = 'string';
                    _this.pageTitle = _this.lang.newParameter;
                    _this.loading = false;
                    setTimeout(function () {
                        //$j("select").chosen({width:"100%",disable_search_threshold: 10, search_contains: true}); 
                    }, 0);
                }, function () {
                    location.href = "index.php";
                });
            }
            else {
                _this.creationMode = false;
                _this.http.get(_this.coreUrl + 'rest/administration/parameters/' + params['id'])
                    .map(function (res) { return res.json(); })
                    .subscribe(function (data) {
                    _this.parameter = data.parameter;
                    _this.lang = data.lang;
                    _this.type = data.type;
                    _this.loading = false;
                    setTimeout(function () {
                        //$j("select").chosen({width:"100%",disable_search_threshold: 10, search_contains: true});   
                    }, 0);
                }, function () {
                    location.href = "index.php";
                });
            }
        });
    };
    ParameterAdministrationComponent.prototype.prepareParameter = function () {
        $j('#inner_content').remove();
    };
    ParameterAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/parameters\"' style='cursor: pointer'>Paramètres</a> > Modification";
        }
    };
    ParameterAdministrationComponent.prototype.onSubmit = function () {
        if (this.type == 'date') {
            this.parameter.param_value_date = $j("#paramValue").val();
            this.parameter.param_value_int = null;
            this.parameter.param_value_string = null;
        }
        else if (this.type == 'int') {
            this.parameter.param_value_date = null;
            this.parameter.param_value_string = null;
        }
        else if (this.type == 'string') {
            this.parameter.param_value_date = null;
            this.parameter.param_value_int = null;
        }
        /*if(this.mode == 'create'){
            this.http.post(this.coreUrl + 'rest/parameters', this.parameter)
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors) {
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                    this.parameter.param_value_date=null;
                    this.parameter.param_value_int=null;
                    this.parameter.param_value_string=null;
                } else {
                    this.resultInfo = this.lang.paramCreatedSuccess;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                    this.router.navigate(['administration/parameters']);
                }
                
            });
        } else if(this.mode == "update"){

            this.http.put(this.coreUrl+'rest/parameters/'+this.paramId,this.parameter)
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors){
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {
                    this.resultInfo = this.lang.paramUpdatedSuccess;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                    this.router.navigate(['administration/parameters']);
                }
            });
        }*/
    };
    return ParameterAdministrationComponent;
}());
ParameterAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals['parameter-administrationView'],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute, router_1.Router])
], ParameterAdministrationComponent);
exports.ParameterAdministrationComponent = ParameterAdministrationComponent;
