import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals.administrationView,
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class AdministrationComponent implements OnInit {

    coreUrl                     : string;

    applicationServices         : any[]     = [];
    modulesServices             : any[]     = [];

    loading                     : boolean   = false;


    constructor(public http: HttpClient, private router: Router) {
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
            .subscribe((data : any) => {
                this.applicationServices = data.application;
                this.modulesServices = data.modules;

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
