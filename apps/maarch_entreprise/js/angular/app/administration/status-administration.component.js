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
var layout_1 = require("@angular/cdk/layout");
var http_1 = require("@angular/common/http");
var router_1 = require("@angular/router");
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var StatusAdministrationComponent = /** @class */ (function () {
    function StatusAdministrationComponent(changeDetectorRef, media, http, route, router, notify) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.status = {
            id: null,
            label_status: null,
            can_be_searched: null,
            can_be_modified: null,
            is_folder_status: null,
            img_filename: null
        };
        this.statusImages = "";
        this.loading = false;
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = function () { return changeDetectorRef.detectChanges(); };
        this.mobileQuery.addListener(this._mobileQueryListener);
    }
    StatusAdministrationComponent.prototype.ngOnDestroy = function () {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    };
    StatusAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareStatus();
        this.route.params.subscribe(function (params) {
            if (typeof params['identifier'] == "undefined") {
                _this.http.get(_this.coreUrl + 'rest/administration/statuses/new')
                    .subscribe(function (data) {
                    _this.status.img_filename = "fm-letter";
                    _this.status.can_be_searched = true;
                    _this.status.can_be_modified = true;
                    _this.statusImages = data['statusImages'];
                    _this.creationMode = true;
                    _this.loading = false;
                });
                _this.statusIdAvailable = false;
            }
            else {
                _this.creationMode = false;
                _this.statusIdentifier = params['identifier'];
                _this.getStatusInfos(_this.statusIdentifier);
                _this.statusIdAvailable = true;
                _this.loading = false;
            }
            _this.updateBreadcrumb(angularGlobals.applicationName);
        });
    };
    StatusAdministrationComponent.prototype.prepareStatus = function () {
        $j('#inner_content').remove();
    };
    StatusAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > " +
            "<a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " +
            "<a onclick='location.hash = \"/administration/statuses\"' style='cursor: pointer'>" + this.lang.statuses + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.statusCreation;
        }
        else {
            breadCrumb += this.lang.statusModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    };
    StatusAdministrationComponent.prototype.getStatusInfos = function (statusIdentifier) {
        var _this = this;
        this.http.get(this.coreUrl + 'rest/statuses/' + statusIdentifier)
            .subscribe(function (data) {
            _this.status = data['status'][0];
            if (_this.status.can_be_searched == 'Y') {
                _this.status.can_be_searched = true;
            }
            else {
                _this.status.can_be_searched = false;
            }
            if (_this.status.can_be_modified == 'Y') {
                _this.status.can_be_modified = true;
            }
            else {
                _this.status.can_be_modified = false;
            }
            if (_this.status.is_folder_status == 'Y') {
                _this.status.is_folder_status = true;
            }
            else {
                _this.status.is_folder_status = false;
            }
            _this.statusImages = data['statusImages'];
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    StatusAdministrationComponent.prototype.isAvailable = function () {
        var _this = this;
        if (this.status.id) {
            this.http.get(this.coreUrl + "rest/status/" + this.status.id)
                .subscribe(function () {
                _this.statusIdAvailable = false;
            }, function (err) {
                _this.statusIdAvailable = false;
                if (err.error.errors == "id not found") {
                    _this.statusIdAvailable = true;
                }
            });
        }
        else {
            this.statusIdAvailable = false;
        }
    };
    StatusAdministrationComponent.prototype.submitStatus = function () {
        var _this = this;
        if (this.creationMode == true) {
            this.http.post(this.coreUrl + 'rest/statuses', this.status)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.statusAdded);
                _this.router.navigate(['administration/statuses']);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else if (this.creationMode == false) {
            this.http.put(this.coreUrl + 'rest/statuses/' + this.statusIdentifier, this.status)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.statusUpdated);
                _this.router.navigate(['administration/statuses']);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    StatusAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals['status-administrationView'],
            styleUrls: ['css/status-administration.component.css'],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [core_1.ChangeDetectorRef, layout_1.MediaMatcher, http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService])
    ], StatusAdministrationComponent);
    return StatusAdministrationComponent;
}());
exports.StatusAdministrationComponent = StatusAdministrationComponent;
