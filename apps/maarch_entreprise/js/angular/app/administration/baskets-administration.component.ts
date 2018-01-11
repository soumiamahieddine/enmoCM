import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["baskets-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css'],
    providers   : [NotificationService]
})
export class BasketsAdministrationComponent implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;

    baskets                     : any[]     = [];

    loading                     : boolean   = false;


    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Bannettes";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/baskets")
            .subscribe((data : any) => {
                this.baskets = data['baskets'];

                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    delete(basket: any) {
        this.http.delete(this.coreUrl + "rest/baskets/" + basket['basket_id'])
            .subscribe((data : any) => {
                this.notify.success(this.lang.basketDeleted);
                this.baskets = data['baskets'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
