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
var UsersAdministrationComponent = (function () {
    function UsersAdministrationComponent(http) {
        this.http = http;
        this.users = [];
        this.userDestRedirect = {};
        this.userDestRedirectModels = [];
        this.lang = {};
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
            .subscribe(function (data) {
            _this.users = data['users'];
            _this.lang = data.lang;
            setTimeout(function () {
                _this.table = $j('#usersTable').DataTable({
                    "dom": '<"datatablesLeft"p><"datatablesRight"f><"datatablesCenter"l>rt<"datatablesCenter"i><"clear">',
                    "lengthMenu": [10, 25, 50, 75, 100],
                    "oLanguage": {
                        "sLengthMenu": "<i class='fa fa-bars'></i> _MENU_",
                        "sZeroRecords": _this.lang.noResult,
                        "sInfo": "_START_ - _END_ / _TOTAL_ " + _this.lang.record,
                        "sSearch": "",
                        "oPaginate": {
                            "sFirst": "<<",
                            "sLast": ">>",
                            "sNext": _this.lang.next + " <i class='fa fa-caret-right'></i>",
                            "sPrevious": "<i class='fa fa-caret-left'></i> " + _this.lang.previous
                        },
                        "sInfoEmpty": _this.lang.noRecord,
                        "sInfoFiltered": "(filtré de _MAX_ " + _this.lang.record + ")"
                    },
                    "order": [[1, "asc"]],
                    "columnDefs": [
                        { "orderable": false, "targets": [3, 5] }
                    ]
                });
                $j('.dataTables_filter input').attr("placeholder", _this.lang.search);
                $j('dataTables_filter input').addClass('form-control');
                $j(".datatablesLeft").css({ "float": "left" });
                $j(".datatablesCenter").css({ "text-align": "center" });
                $j(".datatablesRight").css({ "float": "right" });
            }, 0);
            _this.loading = false;
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
                setTimeout(function () {
                    $j(".redirectDest").typeahead({
                        order: "asc",
                        display: "formattedUser",
                        templateValue: "{{user_id}}",
                        source: {
                            ajax: {
                                type: "GET",
                                dataType: "json",
                                url: _this.coreUrl + "rest/users/autocompleter/exclude/" + user.user_id,
                            }
                        }
                    });
                }, 0);
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
                    successNotification(data.success);
                }, function (err) {
                    user.enabled = 'Y';
                    errorNotification(JSON.parse(err._body).errors);
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
                    errorNotification(data.errors);
                }
                else {
                    //then suspend user
                    _this.http.put(_this.coreUrl + 'rest/users/' + user.user_id, user)
                        .subscribe(function (data) {
                        user.inDiffListDest = 'N';
                        $j('#changeDiffListDest').modal('hide');
                        successNotification(data.success);
                    }, function (err) {
                        user.enabled = 'Y';
                        errorNotification(JSON.parse(err._body).errors);
                    });
                }
            }, function (err) {
                errorNotification(JSON.parse(err._body).errors);
            });
        }
    };
    UsersAdministrationComponent.prototype.activateUser = function (user) {
        var r = confirm(this.lang.authorizeMsg + " ?");
        if (r) {
            user.enabled = 'Y';
            this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                .subscribe(function (data) {
                successNotification(data.success);
            }, function (err) {
                user.enabled = 'N';
                errorNotification(JSON.parse(err._body).errors);
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
                setTimeout(function () {
                    $j(".redirectDest").typeahead({
                        order: "asc",
                        source: {
                            ajax: {
                                type: "GET",
                                dataType: "json",
                                url: _this.coreUrl + "rest/users/autocompleter/exclude/" + user.user_id,
                            }
                        }
                    });
                });
            }, function (err) {
                errorNotification(JSON.parse(err._body).errors);
            });
        }
        else {
            var r = confirm(this.lang.deleteMsg + " ?");
            if (r) {
                this.http.delete(this.coreUrl + 'rest/users/' + user.user_id, user)
                    .subscribe(function (data) {
                    for (var i = 0; i < _this.users.length; i++) {
                        if (_this.users[i].user_id == user.user_id) {
                            _this.users.splice(i, 1);
                        }
                    }
                    _this.table.row($j("#" + user.user_id)).remove().draw();
                    successNotification(data.success);
                }, function (err) {
                    errorNotification(JSON.parse(err._body).errors);
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
                    errorNotification(data.errors);
                }
                else {
                    //then delete user
                    _this.http.delete(_this.coreUrl + 'rest/users/' + user.user_id)
                        .subscribe(function (data) {
                        user.inDiffListDest = 'N';
                        $j('#changeDiffListDest').modal('hide');
                        for (var i = 0; i < _this.users.length; i++) {
                            if (_this.users[i].user_id == user.user_id) {
                                _this.users.splice(i, 1);
                            }
                        }
                        _this.table.row($j("#" + user.user_id)).remove().draw();
                        successNotification(data.success);
                    }, function (err) {
                        errorNotification(JSON.parse(err._body).errors);
                    });
                }
            }, function (err) {
                errorNotification(JSON.parse(err._body).errors);
            });
        }
    };
    return UsersAdministrationComponent;
}());
UsersAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["users-administrationView"],
        styleUrls: ['css/users-administration.component.css', '../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.HttpClient])
], UsersAdministrationComponent);
exports.UsersAdministrationComponent = UsersAdministrationComponent;
