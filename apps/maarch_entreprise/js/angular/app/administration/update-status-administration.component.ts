import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

import {FormControl} from '@angular/forms';
import {Observable} from 'rxjs/Observable';
import {startWith} from 'rxjs/operators/startWith';
import {map} from 'rxjs/operators/map';

import { AutoCompletePlugin } from '../../plugins/autocomplete.plugin';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["update-status-administrationView"],
    providers   : [NotificationService]
})
export class UpdateStatusAdministrationComponent extends AutoCompletePlugin implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;
    statuses                    : any[]     = [];
    statusId                    : string    = "";
    resId                       : string    = "";
    chrono                      : string    = "";

    loading                     : boolean   = false;

    constructor(public http: HttpClient, private notify: NotificationService) {
        super(http,'statuses');
    }


    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Changement du statut";
        }
    }

    ngOnInit(): void {
        this.loading = true;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = false;
    }

    onSubmit() {

        var body = {
            "status" :  this.statusId
        };
        if (this.resId != "") {
            body["resId"] = this.resId;
        } else if (this.chrono != "") {
            body["chrono"] = this.chrono;
        }
        console.log(body);
        this.http.put(this.coreUrl + "rest/res/resource/status", body)
            .subscribe(() => {
                this.resId = "";
                this.chrono = "";
                this.statusId = "";
                this.notify.success(this.lang.modificationSaved);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    resetInput (e:any) {
        if (e.index == 0) {
            this.resId = ""; 
        } else {
            this.chrono = "";
        }
    }
}
