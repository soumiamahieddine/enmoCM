import { ChangeDetectorRef, Component, OnInit, Input, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { LANG } from '../translate.component';
import { MatSidenav, MatBottomSheet } from '@angular/material';
import { HeaderService } from "../../service/header.service";
import { TechnicalAdministrationComponent } from "../administration/technical/technical-administration.component";

declare function $j(selector: any): any;

declare const angularGlobals: any;


@Component({
    templateUrl: "administration.component.html",
})
export class AdministrationComponent implements OnInit {

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;

    coreUrl                         : string;
    lang                            : any       = LANG;
    loading                         : boolean   = false;

    organisationServices            : any[]     = [];
    productionServices              : any[]     = [];
    classementServices              : any[]     = [];
    supervisionServices             : any[]     = [];

    @ViewChild('snav') public sidenavLeft: MatSidenav;
    @ViewChild('snav2') public sidenavRight: MatSidenav;

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private router: Router, private headerService: HeaderService, private bottomSheet: MatBottomSheet) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.headerService.headerMessage = this.lang.administration;
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/administration')
            .subscribe((data: any) => {
                this.organisationServices = data.administrations.organisation;
                this.productionServices = data.administrations.production;
                this.classementServices = data.administrations.classement;
                this.supervisionServices = data.administrations.supervision;
                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    goToSpecifiedAdministration(service: any): void {
        if (service.angular == "true") {
            if (service.id == 'admin_technical_configuration') {
                this.openTechnicalAdmin();
            } else {
                this.router.navigate([service.servicepage]);
            }
            
        } else {
            window.location.assign(service.servicepage);
        }
    }

    openTechnicalAdmin(): void {
        this.bottomSheet.open(TechnicalAdministrationComponent);
    }
}
