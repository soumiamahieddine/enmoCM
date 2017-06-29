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
var header_component_1 = require("./header.component");
var administration_component_1 = require("./administration.component");
var users_administration_component_1 = require("./users-administration.component");
var user_administration_component_1 = require("./user-administration.component");
var status_list_administration_component_1 = require("./status-list-administration.component");
var status_administration_component_1 = require("./status-administration.component");
var profile_component_1 = require("./profile.component");
var signature_book_component_1 = require("./signature-book.component");
var AppModule = (function () {
    function AppModule() {
    }
    return AppModule;
}());
AppModule = __decorate([
    core_1.NgModule({
        imports: [
            platform_browser_1.BrowserModule,
            forms_1.FormsModule,
            router_1.RouterModule.forRoot([
                { path: 'administration', component: administration_component_1.AdministrationComponent },
                { path: 'administration/users', component: users_administration_component_1.UsersAdministrationComponent },
                { path: 'administration/users/:userId', component: user_administration_component_1.UserAdministrationComponent },
                { path: 'administration/status/create', component: status_administration_component_1.StatusAdministrationComponent },
                { path: 'administration/status/update/:id', component: status_administration_component_1.StatusAdministrationComponent },
                { path: 'administration/status', component: status_list_administration_component_1.StatusListAdministrationComponent },
                { path: 'profile', component: profile_component_1.ProfileComponent },
                { path: ':basketId/signatureBook/:resId', component: signature_book_component_1.SignatureBookComponent },
                { path: '**', redirectTo: '', pathMatch: 'full' },
            ], { useHash: true }),
            http_1.HttpModule
        ],
        declarations: [
            header_component_1.HeaderComponent,
            app_component_1.AppComponent,
            administration_component_1.AdministrationComponent,
            users_administration_component_1.UsersAdministrationComponent,
            user_administration_component_1.UserAdministrationComponent,
            status_administration_component_1.StatusAdministrationComponent,
            status_list_administration_component_1.StatusListAdministrationComponent,
            profile_component_1.ProfileComponent,
            signature_book_component_1.SignatureBookComponent,
            signature_book_component_1.SafeUrlPipe
        ],
        bootstrap: [app_component_1.AppComponent]
    })
], AppModule);
exports.AppModule = AppModule;
