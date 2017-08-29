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
var router_1 = require("@angular/router");
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var StatusAdministrationComponent = (function () {
    function StatusAdministrationComponent(http, route, router, notify) {
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
    }
    StatusAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareStatus();
        this.route.params.subscribe(function (params) {
            if (typeof params['identifier'] == "undefined") {
                _this.http.get(_this.coreUrl + 'rest/administration/status/new')
                    .subscribe(function (data) {
                    _this.status.img_filename = "fm-letter";
                    _this.status.can_be_searched = true;
                    _this.status.can_be_modified = true;
                    _this.statusImages = data['statusImages'];
                    _this.creationMode = true;
                    _this.updateBreadcrumb(angularGlobals.applicationName);
                    _this.loading = false;
                });
            }
            else {
                _this.creationMode = false;
                _this.statusIdentifier = params['identifier'];
                _this.getStatusInfos(_this.statusIdentifier);
                _this.loading = false;
            }
        });
    };
    StatusAdministrationComponent.prototype.prepareStatus = function () {
        $j('#inner_content').remove();
    };
    StatusAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > " +
            "<a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " +
            "<a onclick='location.hash = \"/administration/status\"' style='cursor: pointer'>" + this.lang.statuses + "</a> > ";
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
        this.http.get(this.coreUrl + 'rest/administration/status/' + statusIdentifier)
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
            _this.updateBreadcrumb(angularGlobals.applicationName);
        }, function (err) {
            _this.notify.error(JSON.parse(err._body).errors);
        });
    };
    StatusAdministrationComponent.prototype.submitStatus = function () {
        var _this = this;
        if (this.creationMode == true) {
            this.http.post(this.coreUrl + 'rest/status', this.status)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.statusAdded + ' « ' + data.status.id + ' »');
                _this.router.navigate(['administration/status']);
            }, function (err) {
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
        else if (this.creationMode == false) {
            this.http.put(this.coreUrl + 'rest/status/' + this.statusIdentifier, this.status)
                .subscribe(function (data) {
                _this.notify.success(_this.lang.statusUpdated + ' « ' + data.status.id + ' »');
                _this.router.navigate(['administration/status']);
            }, function (err) {
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
    };
    return StatusAdministrationComponent;
}());
StatusAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals['status-administrationView'],
        styleUrls: ['css/status-administration.component.css'],
        providers: [notification_service_1.NotificationService]
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, notification_service_1.NotificationService])
], StatusAdministrationComponent);
exports.StatusAdministrationComponent = StatusAdministrationComponent;
