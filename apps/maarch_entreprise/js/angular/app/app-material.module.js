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
Object.defineProperty(exports, "__esModule", { value: true });
var material_1 = require("@angular/material");
var AppDateAdapter = /** @class */ (function (_super) {
    __extends(AppDateAdapter, _super);
    function AppDateAdapter() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    AppDateAdapter.prototype.parse = function (value) {
        if ((typeof value === 'string') && (value.indexOf('/') > -1)) {
            var str = value.split('/');
            var year = Number(str[2]);
            var month = Number(str[1]) - 1;
            var date = Number(str[0]);
            return new Date(year, month, date);
        }
        var timestamp = typeof value === 'number' ? value : Date.parse(value);
        return isNaN(timestamp) ? null : new Date(timestamp);
    };
    AppDateAdapter.prototype.format = function (date, displayFormat) {
        if (displayFormat == "input") {
            var day = date.getDate();
            var month = date.getMonth() + 1;
            var year = date.getFullYear();
            return this._to2digit(day) + '/' + this._to2digit(month) + '/' + year;
        }
        else {
            return date.toDateString();
        }
    };
    AppDateAdapter.prototype._to2digit = function (n) {
        return ('00' + n).slice(-2);
    };
    return AppDateAdapter;
}(material_1.NativeDateAdapter));
exports.AppDateAdapter = AppDateAdapter;
exports.APP_DATE_FORMATS = {
    parse: {
        dateInput: { month: 'short', year: 'numeric', day: 'numeric' }
    },
    display: {
        // dateInput: { month: 'short', year: 'numeric', day: 'numeric' },
        dateInput: 'input',
        monthYearLabel: { month: 'short', year: 'numeric', day: 'numeric' },
        dateA11yLabel: { year: 'numeric', month: 'long', day: 'numeric' },
        monthYearA11yLabel: { year: 'numeric', month: 'long' },
    }
};
var core_1 = require("@angular/core");
var ng2_dnd_1 = require("ng2-dnd");
var material_2 = require("@angular/material");
var french_paginator_intl_1 = require("./french-paginator-intl");
var AppMaterialModule = /** @class */ (function () {
    function AppMaterialModule() {
    }
    AppMaterialModule = __decorate([
        core_1.NgModule({
            imports: [
                material_2.MatCheckboxModule,
                material_2.MatSelectModule,
                material_2.MatSlideToggleModule,
                material_2.MatInputModule,
                material_2.MatTooltipModule,
                material_2.MatTabsModule,
                material_2.MatSidenavModule,
                material_2.MatButtonModule,
                material_2.MatCardModule,
                material_2.MatButtonToggleModule,
                material_2.MatProgressSpinnerModule,
                material_2.MatToolbarModule,
                material_2.MatMenuModule,
                material_2.MatGridListModule,
                material_2.MatTableModule,
                material_2.MatPaginatorModule,
                material_2.MatSortModule,
                material_2.MatDatepickerModule,
                material_2.MatNativeDateModule,
                material_2.MatExpansionModule,
                material_2.MatAutocompleteModule,
                material_2.MatSnackBarModule,
                material_2.MatIconModule,
                material_2.MatDialogModule,
                material_2.MatListModule,
                material_2.MatChipsModule,
                material_2.MatStepperModule,
                material_2.MatRadioModule,
                material_2.MatSliderModule,
                ng2_dnd_1.DndModule.forRoot()
            ],
            exports: [
                material_2.MatCheckboxModule,
                material_2.MatSelectModule,
                material_2.MatSlideToggleModule,
                material_2.MatInputModule,
                material_2.MatTooltipModule,
                material_2.MatTabsModule,
                material_2.MatSidenavModule,
                material_2.MatButtonModule,
                material_2.MatCardModule,
                material_2.MatButtonToggleModule,
                material_2.MatProgressSpinnerModule,
                material_2.MatToolbarModule,
                material_2.MatMenuModule,
                material_2.MatGridListModule,
                material_2.MatTableModule,
                material_2.MatPaginatorModule,
                material_2.MatSortModule,
                material_2.MatDatepickerModule,
                material_2.MatNativeDateModule,
                material_2.MatExpansionModule,
                material_2.MatAutocompleteModule,
                material_2.MatSnackBarModule,
                material_2.MatIconModule,
                material_2.MatDialogModule,
                material_2.MatListModule,
                material_2.MatChipsModule,
                material_2.MatStepperModule,
                material_2.MatRadioModule,
                material_2.MatSliderModule,
                ng2_dnd_1.DndModule
            ],
            providers: [
                { provide: material_2.MatPaginatorIntl, useValue: french_paginator_intl_1.getFrenchPaginatorIntl() },
                { provide: material_1.DateAdapter, useClass: AppDateAdapter },
                { provide: material_1.MAT_DATE_FORMATS, useValue: exports.APP_DATE_FORMATS },
                { provide: material_1.MAT_DATE_LOCALE, useValue: 'fr-FR' },
            ]
        })
    ], AppMaterialModule);
    return AppMaterialModule;
}());
exports.AppMaterialModule = AppMaterialModule;
