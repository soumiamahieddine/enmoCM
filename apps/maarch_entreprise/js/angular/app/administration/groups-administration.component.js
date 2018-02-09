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
var __param = (this && this.__param) || function (paramIndex, decorator) {
    return function (target, key) { decorator(target, key, paramIndex); }
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = require("@angular/core");
var http_1 = require("@angular/common/http");
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var material_1 = require("@angular/material");
var GroupsAdministrationComponent = /** @class */ (function () {
    function GroupsAdministrationComponent(http, notify, dialog) {
        this.http = http;
        this.notify = notify;
        this.dialog = dialog;
        this.config = {};
        this.lang = translate_component_1.LANG;
        this.groups = [];
        this.groupsForAssign = [];
        this.loading = false;
        this.displayedColumns = ['group_id', 'group_desc', 'actions'];
        this.dataSource = new material_1.MatTableDataSource(this.groups);
    }
    GroupsAdministrationComponent.prototype.applyFilter = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    };
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
            setTimeout(function () {
                _this.dataSource = new material_1.MatTableDataSource(_this.groups);
                _this.dataSource.paginator = _this.paginator;
                _this.dataSource.sort = _this.sort;
            }, 0);
        }, function () {
            location.href = "index.php";
        });
    };
    GroupsAdministrationComponent.prototype.preDelete = function (group) {
        var _this = this;
        if (group.users.length == 0) {
            var r = confirm("Etes vous sûr de vouloir supprimer ce groupe ?");
            if (r) {
                this.deleteGroup(group);
            }
        }
        else {
            this.groupsForAssign = [];
            this.groups.forEach(function (tmpGroup) {
                if (group.group_id != tmpGroup.group_id) {
                    _this.groupsForAssign.push(tmpGroup);
                }
            });
            this.config = { data: { id: group.id, group_desc: group.group_desc, groupsForAssign: this.groupsForAssign, users: group.users } };
            this.dialogRef = this.dialog.open(GroupsAdministrationRedirectModalComponent, this.config);
            this.dialogRef.afterClosed().subscribe(function (result) {
                console.log(result);
                if (result) {
                    if (result == "_NO_REPLACEMENT") {
                        _this.deleteGroup(group);
                    }
                    else {
                        _this.http.put(_this.coreUrl + "rest/groups/" + group.id + "/reassign/" + result, {})
                            .subscribe(function (data) {
                            _this.deleteGroup(group);
                        }, function (err) {
                            _this.notify.error(err.error.errors);
                        });
                    }
                }
                _this.dialogRef = null;
            });
        }
    };
    GroupsAdministrationComponent.prototype.deleteGroup = function (group) {
        var _this = this;
        this.http.delete(this.coreUrl + "rest/groups/" + group['id'])
            .subscribe(function (data) {
            setTimeout(function () {
                _this.groups = data['groups'];
                _this.dataSource = new material_1.MatTableDataSource(_this.groups);
                _this.dataSource.paginator = _this.paginator;
                _this.dataSource.sort = _this.sort;
            }, 0);
            _this.notify.success(_this.lang.groupDeleted);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    __decorate([
        core_1.ViewChild(material_1.MatPaginator),
        __metadata("design:type", material_1.MatPaginator)
    ], GroupsAdministrationComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], GroupsAdministrationComponent.prototype, "sort", void 0);
    GroupsAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["groups-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [http_1.HttpClient, notification_service_1.NotificationService, material_1.MatDialog])
    ], GroupsAdministrationComponent);
    return GroupsAdministrationComponent;
}());
exports.GroupsAdministrationComponent = GroupsAdministrationComponent;
var GroupsAdministrationRedirectModalComponent = /** @class */ (function () {
    function GroupsAdministrationRedirectModalComponent(http, data, dialogRef) {
        this.http = http;
        this.data = data;
        this.dialogRef = dialogRef;
        this.lang = translate_component_1.LANG;
    }
    GroupsAdministrationRedirectModalComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["groups-administration-redirect-modalView"],
        }),
        __param(1, core_1.Inject(material_1.MAT_DIALOG_DATA)),
        __metadata("design:paramtypes", [http_1.HttpClient, Object, material_1.MatDialogRef])
    ], GroupsAdministrationRedirectModalComponent);
    return GroupsAdministrationRedirectModalComponent;
}());
exports.GroupsAdministrationRedirectModalComponent = GroupsAdministrationRedirectModalComponent;
