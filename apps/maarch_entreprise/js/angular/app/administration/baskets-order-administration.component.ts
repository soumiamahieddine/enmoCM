import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["baskets-order-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css'],
    providers   : [NotificationService]
})
export class BasketsOrderAdministrationComponent implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;

    baskets                     : any[]     = [];

    loading                     : boolean   = false;


    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Ordre des bannettes";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/sortedBaskets")
            .subscribe((data : any) => {
                this.baskets = data['baskets'];

                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    updateOrder(id: string, method: string, power: string) {
        this.http.put(this.coreUrl + "rest/sortedBaskets/" + id, {"method" : method, "power" : power})
            .subscribe((data : any) => {
                this.baskets = data['baskets'];
                this.notify.success(this.lang.modificationSaved);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
