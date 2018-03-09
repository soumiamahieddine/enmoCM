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
var layout_1 = require("@angular/cdk/layout");
var http_1 = require("@angular/common/http");
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var material_1 = require("@angular/material");
var BasketsAdministrationComponent = /** @class */ (function () {
    function BasketsAdministrationComponent(changeDetectorRef, media, http, notify) {
        this.http = http;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.baskets = [];
        this.basketsOrder = [];
        this.loading = false;
        this.displayedColumns = ['basket_id', 'basket_name', 'basket_desc', 'actions'];
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = function () { return changeDetectorRef.detectChanges(); };
        this.mobileQuery.addListener(this._mobileQueryListener);
    }
    BasketsAdministrationComponent.prototype.applyFilter = function (filterValue) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    };
    BasketsAdministrationComponent.prototype.ngOnDestroy = function () {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    };
    BasketsAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Bannettes";
        }
    };
    BasketsAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get(this.coreUrl + "rest/baskets")
            .subscribe(function (data) {
            _this.baskets = data['baskets'];
            _this.loading = false;
            setTimeout(function () {
                _this.http.get(_this.coreUrl + "rest/sortedBaskets")
                    .subscribe(function (data) {
                    _this.basketsOrder = data['baskets'];
                }, function () {
                    location.href = "index.php";
                });
                _this.dataSource = new material_1.MatTableDataSource(_this.baskets);
                _this.dataSource.paginator = _this.paginator;
                _this.dataSource.sort = _this.sort;
            }, 0);
        }, function () {
            location.href = "index.php";
        });
    };
    BasketsAdministrationComponent.prototype.delete = function (basket) {
        var _this = this;
        var r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + basket['basket_name'] + ' »');
        if (r) {
            this.http.delete(this.coreUrl + "rest/baskets/" + basket['basket_id'])
                .subscribe(function (data) {
                _this.notify.success(_this.lang.basketDeleted);
                _this.baskets = data['baskets'];
                _this.dataSource = new material_1.MatTableDataSource(_this.baskets);
                _this.dataSource.paginator = _this.paginator;
                _this.dataSource.sort = _this.sort;
                _this.http.get(_this.coreUrl + "rest/sortedBaskets")
                    .subscribe(function (data) {
                    _this.basketsOrder = data['baskets'];
                }, function () {
                    location.href = "index.php";
                });
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    BasketsAdministrationComponent.prototype.updateBasketOrder = function (currentBasket) {
        var _this = this;
        this.http.put(this.coreUrl + "rest/sortedBaskets/" + currentBasket.basket_id, this.basketsOrder)
            .subscribe(function (data) {
            _this.baskets = data['baskets'];
            _this.notify.success(_this.lang.modificationSaved);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    __decorate([
        core_1.ViewChild('snav2'),
        __metadata("design:type", material_1.MatSidenav)
    ], BasketsAdministrationComponent.prototype, "sidenav", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatPaginator),
        __metadata("design:type", material_1.MatPaginator)
    ], BasketsAdministrationComponent.prototype, "paginator", void 0);
    __decorate([
        core_1.ViewChild(material_1.MatSort),
        __metadata("design:type", material_1.MatSort)
    ], BasketsAdministrationComponent.prototype, "sort", void 0);
    BasketsAdministrationComponent = __decorate([
        core_1.Component({
            templateUrl: angularGlobals["baskets-administrationView"],
            providers: [notification_service_1.NotificationService]
        }),
        __metadata("design:paramtypes", [core_1.ChangeDetectorRef, layout_1.MediaMatcher, http_1.HttpClient, notification_service_1.NotificationService])
    ], BasketsAdministrationComponent);
    return BasketsAdministrationComponent;
}());
exports.BasketsAdministrationComponent = BasketsAdministrationComponent;
