import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { LANG } from '../../translate.component';
import { MatSidenav } from '@angular/material/sidenav';
import { HeaderService } from "../../../service/header.service";
import { AppService } from '../../../service/app.service';
import { PrivilegeService } from '../../../service/privileges.service';

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
        public appService: AppService,
        private privilegeService: PrivilegeService) { }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.administration);

        this.headerService.sideNavLeft = this.sidenavLeft;

        this.loading = true;

        this.organisationServices = this.privilegeService.getCurrentUserAdministrationsByUnit('organisation');
        this.productionServices = this.privilegeService.getCurrentUserAdministrationsByUnit('production');
        this.classementServices = this.privilegeService.getCurrentUserAdministrationsByUnit('classement');
        this.supervisionServices = this.privilegeService.getCurrentUserAdministrationsByUnit('supervision');

        this.loading = false;
    }

    goToSpecifiedAdministration(service: any): void {
        if (service.angular === true) {
            this.router.navigate([service.route]);   
        } else {
            window.location.assign(service.route);
        }
    }
}
