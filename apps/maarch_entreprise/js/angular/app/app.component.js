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
var platform_browser_1 = require("@angular/platform-browser");
var material_1 = require("@angular/material");
var material_2 = require("@angular/material");
/** Custom options the configure the tooltip's default show/hide delays. */
exports.myCustomTooltipDefaults = {
    showDelay: 500,
    hideDelay: 0,
    touchendHideDelay: 0,
};
var AppComponent = /** @class */ (function () {
    function AppComponent(iconReg, sanitizer) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('img/logo_white.svg')).addSvgIcon('maarchLogoOnly', sanitizer.bypassSecurityTrustResourceUrl('img/logo_only_white.svg'));
    }
    AppComponent = __decorate([
        core_1.Component({
            selector: 'my-app',
            //template: `<menu-app></menu-app><router-outlet></router-outlet>`,
            template: "<router-outlet></router-outlet>",
            encapsulation: core_1.ViewEncapsulation.None,
            styleUrls: [
                '../../node_modules/bootstrap/dist/css/bootstrap.min.css',
                'css/maarch-material.css',
                'css/engine.css',
                'css/jstree-custom.min.css',
                '../../node_modules/ng2-dnd/bundles/style.css'
            ],
            viewProviders: [material_1.MatIconRegistry],
            providers: [
                { provide: material_2.MAT_TOOLTIP_DEFAULT_OPTIONS, useValue: exports.myCustomTooltipDefaults }
            ],
        }),
        __metadata("design:paramtypes", [material_1.MatIconRegistry, platform_browser_1.DomSanitizer])
    ], AppComponent);
    return AppComponent;
}());
exports.AppComponent = AppComponent;
