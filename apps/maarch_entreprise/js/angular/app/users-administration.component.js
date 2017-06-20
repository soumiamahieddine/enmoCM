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
var UsersAdministrationComponent = (function () {
    function UsersAdministrationComponent(http) {
        this.http = http;
        this.users = [];
        this.resultInfo = "";
        this.loading = false;
    }
    UsersAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Utilisateurs";
        }
    };
    UsersAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get(this.coreUrl + 'rest/administration/users')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.users = data;
            setTimeout(function () {
                $j('#usersTable').DataTable({});
            }, 0);
            _this.loading = false;
        });
    };
    return UsersAdministrationComponent;
}());
UsersAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["users-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.Http])
], UsersAdministrationComponent);
exports.UsersAdministrationComponent = UsersAdministrationComponent;
