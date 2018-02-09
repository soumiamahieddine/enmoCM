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
        this.coreUrl = angularGlobals.coreUrl;
        this.userCtrl = new forms_1.FormControl();
        if (target == 'users') {
            this.http.get(this.coreUrl + 'rest/users/autocompleter')
                .subscribe(function (data) {
                _this.userList = data;
            }, function () {
                location.href = "index.php";
            });
        }
        else {
        }
        this.filteredUsers = this.userCtrl.valueChanges
            .pipe(startWith_1.startWith(''), map_1.map(function (user) { return user ? _this.autocompleteFilter(user) : _this.userList.slice(); }));
    }
    AutoCompletePlugin.prototype.autocompleteFilter = function (name) {
        return this.userList.filter(function (user) {
            return user.formattedUser.toLowerCase().indexOf(name.toLowerCase()) === 0;
        });
    };
    return AutoCompletePlugin;
}());
exports.AutoCompletePlugin = AutoCompletePlugin;
