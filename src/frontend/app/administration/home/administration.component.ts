import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { LANG } from '../../translate.component';
import { MatSidenav } from '@angular/material/sidenav';
import { HeaderService } from "../../../service/header.service";
import { AppService } from '../../../service/app.service';
import { PrivilegeService } from '../../../service/privileges.service';
import { Observable } from 'rxjs';
import { FormControl } from '@angular/forms';
import { startWith, map } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';

@Component({
    templateUrl: "administration.component.html",
    styleUrls: ['administration.component.scss'],
    providers: [AppService]
})
export class AdministrationComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    organisationServices: any[] = [];
    productionServices: any[] = [];
    classementServices: any[] = [];
    supervisionServices: any[] = [];

    searchService = new FormControl();

    administrations: any[] = [];
    filteredAdministrations: Observable<string[]>;

    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;
    @ViewChild('searchServiceInput', { static: true }) searchServiceInput: ElementRef;

    constructor(
        public http: HttpClient,
        private router: Router,
        private headerService: HeaderService,
        public appService: AppService,
        private privilegeService: PrivilegeService,
        private latinisePipe: LatinisePipe) { }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.administration);

        //this.loading = true;

        this.organisationServices = this.privilegeService.getCurrentUserAdministrationsByUnit('organisation');
        this.productionServices = this.privilegeService.getCurrentUserAdministrationsByUnit('production');
        this.classementServices = this.privilegeService.getCurrentUserAdministrationsByUnit('classement');
        this.supervisionServices = this.privilegeService.getCurrentUserAdministrationsByUnit('supervision');

        this.administrations = this.organisationServices.concat(this.productionServices).concat(this.classementServices).concat(this.supervisionServices);

        this.filteredAdministrations = this.searchService.valueChanges
            .pipe(
                startWith(''),
                map(value => this._filter(value, 'administrations'))
            );

        this.loading = false;

        setTimeout(() => {
            this.searchServiceInput.nativeElement.focus();
        }, 0);
    }

    goToSpecifiedAdministration(service: any): void {
        if (service.angular === true) {
            this.router.navigate([service.route]);
        } else {
            window.location.assign(service.route);
        }
    }

    private _filter(value: string, type: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this[type].filter((option: any) => this.latinisePipe.transform(option['label'].toLowerCase()).includes(filterValue));
        } else {
            return this[type];
        }
    }
}
