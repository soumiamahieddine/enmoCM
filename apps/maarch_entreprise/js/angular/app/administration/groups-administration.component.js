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
var GroupsAdministrationComponent = (function () {
    function GroupsAdministrationComponent(http, notify) {
        this.http = http;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.groups = [];
        this.groupsForAssign = [];
        this.loading = false;
    }
    GroupsAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Groupes";
        }
    };
    GroupsAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get(this.coreUrl + "rest/groups")
            .subscribe(function (data) {
            _this.groups = data['groups'];
            _this.loading = false;
        }, function () {
            location.href = "index.php";
        });
    };
    GroupsAdministrationComponent.prototype.preDelete = function (group) {
        var _this = this;
        var r = confirm("Etes vous sûr de vouloir supprimer ce groupe ?");
        if (r) {
            if (group.users.length == 0) {
                this.deleteGroup(group);
            }
            else {
                this.groupsForAssign.push("Aucun remplacement");
                this.groups.forEach(function (tmpGroup) {
                    if (group.group_id != tmpGroup.group_id) {
                        _this.groupsForAssign.push(tmpGroup.group_id);
                    }
                });
            }
        }
        console.log(this.groupsForAssign);
    };
    GroupsAdministrationComponent.prototype.reassignUsers = function (group, groupId) {
        var _this = this;
        this.groupsForAssign = [];
        if (groupId == "Aucun remplacement") {
            this.deleteGroup(group);
        }
        else {
            this.http.get(this.coreUrl + "rest/groups/" + group['id'] + "/reassign/" + groupId)
                .subscribe(function (data) {
                _this.deleteGroup(group);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    GroupsAdministrationComponent.prototype.deleteGroup = function (group) {
        var _this = this;
        this.http.delete(this.coreUrl + "rest/groups/" + group['id'])
            .subscribe(function (data) {
            _this.notify.success(_this.lang.groupDeleted);
            _this.groups = data['groups'];
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    return GroupsAdministrationComponent;
}());
GroupsAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["groups-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css'],
        providers: [notification_service_1.NotificationService]
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, notification_service_1.NotificationService])
], GroupsAdministrationComponent);
exports.GroupsAdministrationComponent = GroupsAdministrationComponent;
