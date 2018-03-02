import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { LANG } from '../translate.component';

declare function $j(selector: any): any;

declare const angularGlobals: any;


@Component({
    templateUrl: angularGlobals.administrationView,
})
export class AdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;

    organisationServices: any[] = [];
    productionServices: any[] = [];
    classementServices: any[] = [];
    supervisionServices: any[] = [];

    loading: boolean = false;


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private router: Router) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    prepareAdministration() {
        $j('#inner_content').remove();
        $j('#menunav').hide();
        $j('#divList').remove();
        $j('#magicContactsTable').remove();
        $j('#manageBasketsOrderTable').remove();
        $j('#controlParamTechnicTable').remove();
        $j('#container').width("99%");
        if ($j('#content h1')[0] && $j('#content h1')[0] != $j('my-app h1')[0]) {
            $j('#content h1')[0].remove();
        }
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > Administration";
        }
    }

    ngOnInit(): void {
        this.prepareAdministration();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/administration')
            .subscribe((data: any) => {
                this.organisationServices = data.administrations.organisation;
                this.productionServices = data.administrations.production;
                this.classementServices = data.administrations.classement;
                this.supervisionServices = data.administrations.supervision;

                this.loading = false;
            });
    }

    goToSpecifiedAdministration(service: any): void {
        if (service.angular == "true") {
            this.router.navigate([service.servicepage]);
        } else {
            window.location.assign(service.servicepage);
        }
    }
}
