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
var material_1 = require("@angular/material");
var core_1 = require("@angular/core");
var material_2 = require("@angular/material");
var CustomSnackbarComponent = /** @class */ (function () {
    function CustomSnackbarComponent(data) {
        this.data = data;
    }
    CustomSnackbarComponent = __decorate([
        core_1.Component({
            selector: 'custom-snackbar',
            template: '<mat-grid-list cols="4" rowHeight="1:1"><mat-grid-tile colspan="1"><mat-icon class="fa fa-{{data.icon}} fa-2x"></mat-icon></mat-grid-tile><mat-grid-tile colspan="3">{{data.message}}</mat-grid-tile></mat-grid-list>' // You may also use a HTML file
        }),
        __param(0, core_1.Inject(material_2.MAT_SNACK_BAR_DATA)),
        __metadata("design:paramtypes", [Object])
    ], CustomSnackbarComponent);
    return CustomSnackbarComponent;
}());
exports.CustomSnackbarComponent = CustomSnackbarComponent;
var NotificationService = /** @class */ (function () {
    function NotificationService(snackBar) {
        this.snackBar = snackBar;
    }
    NotificationService.prototype.success = function (message) {
        this.snackBar.openFromComponent(CustomSnackbarComponent, {
            duration: 2000,
            data: { message: message, icon: 'info-circle' }
        });
    };
    NotificationService.prototype.error = function (message) {
        this.snackBar.openFromComponent(CustomSnackbarComponent, {
            duration: 2000,
            data: { message: message, icon: 'exclamation-triangle' }
        });
    };
    NotificationService = __decorate([
        core_1.Injectable(),
        __metadata("design:paramtypes", [material_1.MatSnackBar])
    ], NotificationService);
    return NotificationService;
}());
exports.NotificationService = NotificationService;
