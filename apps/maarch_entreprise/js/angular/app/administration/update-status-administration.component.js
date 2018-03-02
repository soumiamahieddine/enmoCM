"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
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
var layout_1 = require("@angular/cdk/layout");
var http_1 = require("@angular/common/http");
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var autocomplete_plugin_1 = require("../../plugins/autocomplete.plugin");
var UpdateStatusAdministrationComponent = /** @class */ (function (_super) {
    __extends(UpdateStatusAdministrationComponent, _super);
    function UpdateStatusAdministrationComponent(changeDetectorRef, media, http, notify) {
        var _this = _super.call(this, http, ['statuses']) || this;
        _this.http = http;
        _this.notify = notify;
        _this.lang = translate_component_1.LANG;
        _this.statuses = [];
        _this.statusId = "";
        _this.resId = "";
        _this.chrono = "";
        _this.loading = false;
        $j("link[href='merged_css.php']").remove();
        _this.mobileQuery = media.matchMedia('(max-width: 768px)');
        _this._mobileQueryListener = function () { return changeDetectorRef.detectChanges(); };
        _this.mobileQuery.addListener(_this._mobileQueryListener);
        return _this;
    }
    UpdateStatusAdministrationComponent.prototype.ngOnDestroy = function () {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    };
    UpdateStatusAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Changement du statut";
        }
    };
    UpdateStatusAdministrationComponent.prototype.ngOnInit = function () {
        this.loading = true;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = false;
    };
    UpdateStatusAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        var body = {
            "status": this.statusId
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
            _this.statusId = "";
            _this.notify.success(_this.lang.modificationSaved);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UpdateStatusAdministrationComponent.prototype.resetInput = function (e) {
        if (e.index == 0) {
            this.resId = "";
        }
        else {
            this.chrono = "";
        }
    };
    UpdateStatusAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["update-status-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [core_1.ChangeDetectorRef, layout_1.MediaMatcher, http_1.HttpClient, notification_service_1.NotificationService])
    ], UpdateStatusAdministrationComponent);
    return UpdateStatusAdministrationComponent;
}(autocomplete_plugin_1.AutoCompletePlugin));
exports.UpdateStatusAdministrationComponent = UpdateStatusAdministrationComponent;
