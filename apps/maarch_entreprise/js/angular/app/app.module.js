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
var administration_component_1 = require("./administration.component");
var users_administration_component_1 = require("./users-administration.component");
var user_administration_component_1 = require("./user-administration.component");
var status_list_administration_component_1 = require("./status-list-administration.component");
var status_administration_component_1 = require("./status-administration.component");
var actions_administration_component_1 = require("./actions-administration.component");
var action_administration_component_1 = require("./action-administration.component");
var parameter_administration_component_1 = require("./parameter-administration.component");
var parameters_administration_component_1 = require("./parameters-administration.component");
var priorities_administration_component_1 = require("./priorities-administration.component");
var priority_administration_component_1 = require("./priority-administration.component");
var profile_component_1 = require("./profile.component");
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
                { path: 'administration/users/new', component: user_administration_component_1.UserAdministrationComponent },
                { path: 'administration/users/:id', component: user_administration_component_1.UserAdministrationComponent },
                { path: 'administration/status', component: status_list_administration_component_1.StatusListAdministrationComponent },
                { path: 'administration/status/new', component: status_administration_component_1.StatusAdministrationComponent },
                { path: 'administration/status/:identifier', component: status_administration_component_1.StatusAdministrationComponent },
                { path: 'profile', component: profile_component_1.ProfileComponent },
                { path: 'administration/parameters', component: parameters_administration_component_1.ParametersAdministrationComponent },
                { path: 'administration/parameters/new', component: parameter_administration_component_1.ParameterAdministrationComponent },
                { path: 'administration/parameters/:id', component: parameter_administration_component_1.ParameterAdministrationComponent },
                { path: 'administration/reports', component: reports_component_1.ReportsComponent },
                { path: 'administration/priorities', component: priorities_administration_component_1.PrioritiesAdministrationComponent },
                { path: 'administration/priorities/new', component: priority_administration_component_1.PriorityAdministrationComponent },
                { path: 'administration/priorities/:id', component: priority_administration_component_1.PriorityAdministrationComponent },
                { path: ':basketId/signatureBook/:resId', component: signature_book_component_1.SignatureBookComponent },
                { path: 'administration/actions', component: actions_administration_component_1.ActionsAdministrationComponent },
                { path: 'administration/actions/new', component: action_administration_component_1.ActionAdministrationComponent },
                { path: 'administration/actions/:id', component: action_administration_component_1.ActionAdministrationComponent },
                { path: '**', redirectTo: '', pathMatch: 'full' },
            ], { useHash: true }),
            http_1.HttpModule
        ],
        declarations: [
            app_component_1.AppComponent,
            action_administration_component_1.ActionAdministrationComponent,
            actions_administration_component_1.ActionsAdministrationComponent,
            administration_component_1.AdministrationComponent,
            reports_component_1.ReportsComponent,
            users_administration_component_1.UsersAdministrationComponent,
            user_administration_component_1.UserAdministrationComponent,
            status_administration_component_1.StatusAdministrationComponent,
            status_list_administration_component_1.StatusListAdministrationComponent,
            priorities_administration_component_1.PrioritiesAdministrationComponent,
            priority_administration_component_1.PriorityAdministrationComponent,
            parameters_administration_component_1.ParametersAdministrationComponent,
            parameter_administration_component_1.ParameterAdministrationComponent,
            profile_component_1.ProfileComponent,
            signature_book_component_1.SignatureBookComponent,
            signature_book_component_1.SafeUrlPipe
        ],
        bootstrap: [app_component_1.AppComponent]
    })
], AppModule);
exports.AppModule = AppModule;
