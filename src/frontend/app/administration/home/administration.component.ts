import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { LANG } from '../../translate.component';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { MatSidenav } from '@angular/material/sidenav';
import { HeaderService } from "../../../service/header.service";
import { TechnicalAdministrationComponent } from "../technical/technical-administration.component";
import { AppService } from '../../../service/app.service';

declare function $j(selector: any): any;

@Component({
    templateUrl: "administration.component.html",
    styleUrls: ['administration.component.scss'],
    providers: [AppService]
})
export class AdministrationComponent implements OnInit {

    lang                            : any       = LANG;
    loading                         : boolean   = false;

    organisationServices            : any[]     = [];
    productionServices              : any[]     = [];
    classementServices              : any[]     = [];
    supervisionServices             : any[]     = [];

    @ViewChild('snav', { static: true }) public sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;

    constructor(
        public http: HttpClient, 
        private router: Router, 
        private headerService: HeaderService, 
        private bottomSheet: MatBottomSheet,
        public appService: AppService) {

        $j("link[href='merged_css.php']").remove();

    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.administration);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = true;

        this.http.get('../../rest/administration')
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
