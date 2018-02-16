"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var forms_1 = require("@angular/forms");
var startWith_1 = require("rxjs/operators/startWith");
var map_1 = require("rxjs/operators/map");
var AutoCompletePlugin = /** @class */ (function () {
    function AutoCompletePlugin(http, target) {
        var _this = this;
        this.http = http;
        this.userList = [];
        this.statusesList = [];
        this.coreUrl = angularGlobals.coreUrl;
        if (target == 'users') {
            this.userCtrl = new forms_1.FormControl();
            this.http.get(this.coreUrl + 'rest/users/autocompleter')
                .subscribe(function (data) {
                _this.userList = data;
                _this.filteredUsers = _this.userCtrl.valueChanges
                    .pipe(startWith_1.startWith(''), map_1.map(function (user) { return user ? _this.autocompleteFilterUser(user) : _this.userList.slice(); }));
            }, function () {
                location.href = "index.php";
            });
        }
        else if (target == 'statuses') {
            this.statusCtrl = new forms_1.FormControl();
            this.http.get(this.coreUrl + 'rest/statuses')
                .subscribe(function (data) {
                _this.statusesList = data['statuses'];
                _this.filteredStatuses = _this.statusCtrl.valueChanges
                    .pipe(startWith_1.startWith(''), map_1.map(function (status) { return status ? _this.autocompleteFilterStatuses(status) : _this.statusesList.slice(); }));
                console.log(_this.statusesList);
            }, function () {
                location.href = "index.php";
            });
        }
        else {
        }
    }
    AutoCompletePlugin.prototype.autocompleteFilterUser = function (name) {
        return this.userList.filter(function (user) {
            return user.formattedUser.toLowerCase().indexOf(name.toLowerCase()) === 0;
        });
    };
    AutoCompletePlugin.prototype.autocompleteFilterStatuses = function (name) {
        console.log(this.statusesList.filter(function (status) {
            return status.label_status.toLowerCase().indexOf(name.toLowerCase()) === 0;
        }));
        return this.statusesList.filter(function (status) {
            return status.label_status.toLowerCase().indexOf(name.toLowerCase()) === 0;
        });
    };
    return AutoCompletePlugin;
}());
exports.AutoCompletePlugin = AutoCompletePlugin;
