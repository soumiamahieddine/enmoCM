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
var router_1 = require("@angular/router");
require("rxjs/add/operator/map");
var UserAdministrationComponent = (function () {
    function UserAdministrationComponent(http, route) {
        this.http = http;
        this.route = route;
        this.user = [];
        this.resultInfo = "";
        this.loading = false;
    }
    UserAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Utilisateurs";
        }
    };
    UserAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.route.params.subscribe(function (params) {
            _this.userId = params['userId'];
            _this.http.get(_this.coreUrl + 'rest/administration/user/' + _this.userId)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.user = data;
                _this.loading = false;
            }, function () {
                location.href = "index.php";
            });
        });
    };
    UserAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        this.http.put(this.coreUrl + 'rest/user/' + this.userId, this.user)
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
                _this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }
        }, function (error) {
            alert(error.statusText);
        });
    };
    return UserAdministrationComponent;
}());
UserAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["user-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute])
], UserAdministrationComponent);
exports.UserAdministrationComponent = UserAdministrationComponent;
