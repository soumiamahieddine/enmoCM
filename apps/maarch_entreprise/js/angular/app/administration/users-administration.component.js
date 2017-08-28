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
var DataTablePipe = (function () {
    function DataTablePipe() {
    }
    DataTablePipe.prototype.transform = function (array, field, query) {
        if (query) {
            query = query.toLowerCase();
            return array.filter(function (value) {
                return value[field].toLowerCase().indexOf(query) > -1;
            });
        }
        return array;
    };
    return DataTablePipe;
}());
DataTablePipe = __decorate([
    core_1.Pipe({ name: 'dataPipe' })
], DataTablePipe);
exports.DataTablePipe = DataTablePipe;
var UsersAdministrationComponent = (function () {
    function UsersAdministrationComponent(http, notify) {
        this.http = http;
        this.notify = notify;
        this.search = null;
        this.users = [];
        this.userDestRedirect = {};
        this.userDestRedirectModels = [];
        this.lang = translate_component_1.LANG;
        this.loading = false;
        this.data = [];
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
            .subscribe(function (data) {
            _this.users = data['users'];
            _this.data = _this.users;
            _this.loading = false;
            setTimeout(function () {
                $j("[md2sortby='user_id']").click();
            }, 0);
        }, function () {
            location.href = "index.php";
        });
    };
    UsersAdministrationComponent.prototype.suspendUser = function (user) {
        var _this = this;
        if (user.inDiffListDest == 'Y') {
            user.mode = 'up';
            this.userDestRedirect = user;
            this.http.get(this.coreUrl + 'rest/listModels/itemId/' + user.user_id + '/itemMode/dest/objectType/entity_id')
                .subscribe(function (data) {
                _this.userDestRedirectModels = data.listModels;
            }, function (err) {
                console.log(err);
                location.href = "index.php";
            });
        }
        else {
            var r = confirm(this.lang.suspendMsg + " ?");
            if (r) {
                user.enabled = 'N';
                this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                    .subscribe(function (data) {
                    _this.notify.success(data.success);
                }, function (err) {
                    user.enabled = 'Y';
                    _this.notify.error(JSON.parse(err._body).errors);
                });
            }
        }
    };
    UsersAdministrationComponent.prototype.suspendUserModal = function (user) {
        var _this = this;
        var r = confirm(this.lang.suspendMsg + " ?");
        if (r) {
            user.enabled = 'N';
            user.redirectListModels = this.userDestRedirectModels;
            //first, update listModels
            this.http.put(this.coreUrl + 'rest/listModels/itemId/' + user.user_id + '/itemMode/dest/objectType/entity_id', user)
                .subscribe(function (data) {
                if (data.errors) {
                    user.enabled = 'Y';
                    _this.notify.error(data.errors);
                }
                else {
                    //then suspend user
                    _this.http.put(_this.coreUrl + 'rest/users/' + user.id, user)
                        .subscribe(function (data) {
                        user.inDiffListDest = 'N';
                        $j('#changeDiffListDest').modal('hide');
                        _this.notify.success(data.success);
                    }, function (err) {
                        user.enabled = 'Y';
                        _this.notify.error(JSON.parse(err._body).errors);
                    });
                }
            }, function (err) {
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
    };
    UsersAdministrationComponent.prototype.activateUser = function (user) {
        var _this = this;
        var r = confirm(this.lang.authorizeMsg + " ?");
        if (r) {
            user.enabled = 'Y';
            this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                .subscribe(function (data) {
                _this.notify.success(data.success);
            }, function (err) {
                user.enabled = 'N';
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
    };
    UsersAdministrationComponent.prototype.deleteUser = function (user) {
        var _this = this;
        if (user.inDiffListDest == 'Y') {
            user.mode = 'del';
            this.userDestRedirect = user;
            this.http.get(this.coreUrl + 'rest/listModels/itemId/' + user.user_id + '/itemMode/dest/objectType/entity_id')
                .subscribe(function (data) {
                _this.userDestRedirectModels = data.listModels;
            }, function (err) {
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
        else {
            var r = confirm(this.lang.deleteMsg + " ?");
            if (r) {
                this.http.delete(this.coreUrl + 'rest/users/' + user.id, user)
                    .subscribe(function (data) {
                    _this.data = data.users;
                    _this.notify.success(data.success);
                }, function (err) {
                    _this.notify.error(JSON.parse(err._body).errors);
                });
            }
        }
    };
    UsersAdministrationComponent.prototype.deleteUserModal = function (user) {
        var _this = this;
        var r = confirm(this.lang.deleteMsg + " ?");
        if (r) {
            user.redirectListModels = this.userDestRedirectModels;
            //first, update listModels
            this.http.put(this.coreUrl + 'rest/listModels/itemId/' + user.user_id + '/itemMode/dest/objectType/entity_id', user)
                .subscribe(function (data) {
                if (data.errors) {
                    _this.notify.error(data.errors);
                }
                else {
                    //then delete user
                    _this.http.delete(_this.coreUrl + 'rest/users/' + user.id)
                        .subscribe(function (data) {
                        user.inDiffListDest = 'N';
                        _this.data = data.users;
                        $j('#changeDiffListDest').modal('hide');
                        _this.notify.success(data.success);
                    }, function (err) {
                        _this.notify.error(JSON.parse(err._body).errors);
                    });
                }
            }, function (err) {
                _this.notify.error(JSON.parse(err._body).errors);
            });
        }
    };
    return UsersAdministrationComponent;
}());
UsersAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["users-administrationView"],
        styleUrls: ['css/users-administration.component.css'],
        providers: [notification_service_1.NotificationService]
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, notification_service_1.NotificationService])
], UsersAdministrationComponent);
exports.UsersAdministrationComponent = UsersAdministrationComponent;
