import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';

declare function $j (selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare const angularGlobals : any;


@Component({
    templateUrl : angularGlobals["reports-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class ReportsAdministrationComponent implements OnInit {

    coreUrl         : string;
    lang            : any       = LANG;
    groups          : any[]     = [];
    reports         : any[]     = [];

    selectedGroup   : string    = "";


    constructor(public http: HttpClient) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Etats et edition";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.http.get(this.coreUrl + 'rest/groups')
            .subscribe((data: any) => {
                this.groups = data['groups'];
            });
    }

    loadReports() {
        this.http.get(this.coreUrl + 'rest/reports/groups/'+ this.selectedGroup)
            .subscribe((data: any) => {
                this.reports = data['reports'];
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/reports/groups/'+ this.selectedGroup, this.reports)
            .subscribe((data: any) => {
                successNotification(data['success']);
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }
}