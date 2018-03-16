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
var animations_1 = require("@angular/platform-browser/animations");
var forms_1 = require("@angular/forms");
var http_1 = require("@angular/common/http");
var app_material_module_1 = require("./app-material.module");
var header_component_1 = require("./header.component");
var notification_service_1 = require("./notification.service");
var app_component_1 = require("./app.component");
var app_routing_module_1 = require("./app-routing.module");
// import { AdministrationModule }                 from './administration/administration.module';
var menu_component_1 = require("./menu/menu.component");
var menu_nav_component_1 = require("./menu/menu-nav.component");
var profile_component_1 = require("./profile.component");
var signature_book_component_1 = require("./signature-book.component");
var AppModule = /** @class */ (function () {
    function AppModule() {
    }
    AppModule = __decorate([
        core_1.NgModule({
            imports: [
                platform_browser_1.BrowserModule,
                animations_1.BrowserAnimationsModule,
                forms_1.FormsModule,
                http_1.HttpClientModule,
                // AdministrationModule,
                app_routing_module_1.AppRoutingModule,
                app_material_module_1.AppMaterialModule
            ],
            declarations: [
                header_component_1.HeaderComponent,
                app_component_1.AppComponent,
                profile_component_1.ProfileComponent,
                menu_component_1.MenuComponent,
                menu_nav_component_1.MenuNavComponent,
                signature_book_component_1.SignatureBookComponent,
                signature_book_component_1.SafeUrlPipe,
                notification_service_1.CustomSnackbarComponent
            ],
            entryComponents: [
                notification_service_1.CustomSnackbarComponent
            ],
            bootstrap: [app_component_1.AppComponent]
        })
    ], AppModule);
    return AppModule;
}());
exports.AppModule = AppModule;
