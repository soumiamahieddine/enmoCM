import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["entities-administrationView"],
    providers   : [NotificationService]
})
export class EntitiesAdministrationComponent implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;

    entities                    : any[]     = [];

    loading                     : boolean   = false;


    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Entités";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/entities")
            .subscribe((data : any) => {
                this.entities = data['entities'];

                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    updateStatus(entity: any, method: string) {
        this.http.put(this.coreUrl + "rest/entities/" + entity['entity_id'] + "/status", {"method" : method})
            .subscribe((data : any) => {
                this.notify.success("");
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    delete(entity: any) {
        this.http.delete(this.coreUrl + "rest/entities/" + entity['entity_id'])
            .subscribe((data : any) => {
                this.notify.success(this.lang.entityDeleted);
                this.entities = data['entities'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
