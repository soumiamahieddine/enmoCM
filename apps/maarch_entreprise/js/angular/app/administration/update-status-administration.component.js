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
var UpdateStatusAdministrationComponent = /** @class */ (function () {
    function UpdateStatusAdministrationComponent(http, notify) {
        this.http = http;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.statuses = [];
        this.resId = "";
        this.chrono = "";
        this.loading = false;
    }
    UpdateStatusAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Changement du statut";
        }
    };
    UpdateStatusAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get(this.coreUrl + "rest/administration/status")
            .subscribe(function (data) {
            _this.statuses = data['statusList'];
            _this.loading = false;
        }, function () {
            location.href = "index.php";
        });
    };
    UpdateStatusAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        var body = {
            "status": $j("#statuses option:selected")[0].value
        };
        if (this.resId != "") {
            body["resId"] = this.resId;
        }
        else if (this.chrono != "") {
            body["chrono"] = this.chrono;
        }
        this.http.put(this.coreUrl + "rest/res/resource/status", body)
            .subscribe(function () {
            _this.resId = "";
            _this.chrono = "";
            $j('#statuses').prop('selectedIndex', 0);
            _this.notify.success(_this.lang.modificationSaved);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UpdateStatusAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["update-status-administrationView"],
            styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css'],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [http_1.HttpClient, notification_service_1.NotificationService])
    ], UpdateStatusAdministrationComponent);
    return UpdateStatusAdministrationComponent;
}());
exports.UpdateStatusAdministrationComponent = UpdateStatusAdministrationComponent;
