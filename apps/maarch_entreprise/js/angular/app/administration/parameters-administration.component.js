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
var http_1 = require("@angular/common/http");
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var ParametersAdministrationComponent = (function () {
    function ParametersAdministrationComponent(http, notify) {
        this.http = http;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.search = null;
        this.resultInfo = "";
        this.loading = false;
        this.data = [];
    }
    ParametersAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Paramètres";
    };
    ParametersAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + 'rest/administration/parameters')
            .subscribe(function (data) {
            _this.parametersList = data.parametersList;
            _this.data = _this.parametersList;
            setTimeout(function () {
                $j("[md2sortby='id']").click();
            }, 0);
            _this.loading = false;
        });
    };
    ParametersAdministrationComponent.prototype.goUrl = function () {
        location.href = 'index.php?admin=parameters&page=control_param_technic';
    };
    ParametersAdministrationComponent.prototype.deleteParameter = function (paramId) {
        var _this = this;
        var resp = confirm(this.lang.deleteConfirm + ' ' + paramId + '?');
        if (resp) {
            this.http.delete(this.coreUrl + 'rest/parameters/' + paramId)
                .subscribe(function (data) {
                _this.data = data.parameter;
                _this.notify.success(data.success);
            }, function (err) {
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
    };
    return ParametersAdministrationComponent;
}());
ParametersAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["parameters-administrationView"],
        styleUrls: ['css/parameters-administration.component.css'],
        providers: [notification_service_1.NotificationService]
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, notification_service_1.NotificationService])
], ParametersAdministrationComponent);
exports.ParametersAdministrationComponent = ParametersAdministrationComponent;
