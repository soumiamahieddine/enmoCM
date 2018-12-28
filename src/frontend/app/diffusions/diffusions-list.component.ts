import { Component, AfterViewInit, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MAT_BOTTOM_SHEET_DATA } from '@angular/material';

declare function $j(selector: any): any;

@Component({
    selector: 'app-diffusions-list',
    templateUrl: 'diffusions-list.component.html',
    styleUrls: ['diffusions-list.component.scss'],
    providers: [NotificationService]
})
export class DiffusionsListComponent implements AfterViewInit {

    lang: any = LANG;
    listinstance: any = [];
    visaCircuit: any;
    avisCircuit: any;
    roles: any = [];
    loading: boolean = true;
    tabVisaCircuit: boolean = false;
    tabAvisCircuit: boolean = false;

    constructor(public http: HttpClient, @Inject(MAT_BOTTOM_SHEET_DATA) public data: any) { }

    ngAfterViewInit() {
        this.http.get("../../rest/res/" + this.data.resId + "/listinstance")
            .subscribe((data: any) => {
                if (data != null) {
                    this.roles = Object.keys(data);
                    this.listinstance = data;
                }

                this.http.get("../../rest/res/" + this.data.resId + "/visaCircuit")
                    .subscribe((data: any) => {
                        this.visaCircuit = data;
                        if (this.visaCircuit.length > 0) {
                            this.tabVisaCircuit = true;
                        }

                        this.http.get("../../rest/res/" + this.data.resId + "/avisCircuit")
                            .subscribe((data: any) => {
                                this.avisCircuit = data;
                                if (this.avisCircuit.length > 0) {
                                    this.tabAvisCircuit = true;
                                }
                                this.loading = false;
                            });
                    });
            });
    }
}