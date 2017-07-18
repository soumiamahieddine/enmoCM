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
var translate_component_1 = require("./translate.component");
var PriorityAdministrationComponent = (function () {
    function PriorityAdministrationComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.lang = translate_component_1.LANG;
        this.loading = false;
        this.priority = {
            working_days: false
        };
    }
    PriorityAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/priorities\"' style='cursor: pointer'>Priorités</a>";
        }
    };
    PriorityAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.creationMode = true;
                _this.loading = false;
            }
            else {
                _this.creationMode = false;
                _this.id = params['id'];
                _this.http.get(_this.coreUrl + "rest/priorities/" + _this.id)
                    .map(function (res) { return res.json(); })
                    .subscribe(function (data) {
                    _this.priority = data.priority;
                    _this.loading = false;
                }, function () {
                    location.href = "index.php";
                });
            }
        });
    };
    PriorityAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/priorities", this.priority)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                successNotification(data.success);
                _this.router.navigate(["/administration/priorities"]);
            }, function (err) {
                errorNotification(JSON.parse(err._body).errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/priorities/" + this.id, this.priority)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                successNotification(data.success);
                _this.router.navigate(["/administration/priorities"]);
            }, function (err) {
                errorNotification(JSON.parse(err._body).errors);
            });
        }
    };
    return PriorityAdministrationComponent;
}());
PriorityAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["priority-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute, router_1.Router])
], PriorityAdministrationComponent);
exports.PriorityAdministrationComponent = PriorityAdministrationComponent;
