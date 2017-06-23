"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = require("@angular/core");
var platform_browser_1 = require("@angular/platform-browser");
var router_1 = require("@angular/router");
var http_1 = require("@angular/http");
var forms_1 = require("@angular/forms");
var app_component_1 = require("./app.component");
var users_administration_component_1 = require("./users-administration.component");
var profile_component_1 = require("./profile.component");
var parameter_component_1 = require("./parameter.component");
var parameters_component_1 = require("./parameters.component");
var administration_component_1 = require("./administration.component");
var signature_book_component_1 = require("./signature-book.component");
var reports_component_1 = require("./reports.component");
var AppModule = (function () {
    function AppModule() {
    }
    return AppModule;
}());
AppModule = __decorate([
    core_1.NgModule({
        imports: [
            platform_browser_1.BrowserModule,
            //DataTablesModule,
            forms_1.FormsModule,
            router_1.RouterModule.forRoot([
                { path: 'administration', component: administration_component_1.AdministrationComponent },
                { path: 'administration/users', component: users_administration_component_1.UsersAdministrationComponent },
                { path: 'profile', component: profile_component_1.ProfileComponent },
                { path: 'administration/parameter/create', component: parameter_component_1.ParameterComponent },
                { path: 'administration/parameter/update/:id', component: parameter_component_1.ParameterComponent },
                { path: 'administration/parameters', component: parameters_component_1.ParametersComponent },
                { path: 'administration/reports', component: reports_component_1.ReportsComponent },
                { path: ':basketId/signatureBook/:resId', component: signature_book_component_1.SignatureBookComponent },
                { path: '**', redirectTo: '', pathMatch: 'full' },
            ], { useHash: true }),
            http_1.HttpModule
        ],
        declarations: [app_component_1.AppComponent, administration_component_1.AdministrationComponent, users_administration_component_1.UsersAdministrationComponent, parameters_component_1.ParametersComponent, parameter_component_1.ParameterComponent, reports_component_1.ReportsComponent, profile_component_1.ProfileComponent, signature_book_component_1.SignatureBookComponent, signature_book_component_1.SafeUrlPipe],
        bootstrap: [app_component_1.AppComponent]
    })
], AppModule);
exports.AppModule = AppModule;
