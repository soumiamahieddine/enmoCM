import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["update-status-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css'],
    providers   : [NotificationService]
})
export class UpdateStatusAdministrationComponent implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;

    statuses                    : any[]     = [];
    resId                       : string    = "";
    chrono                      : string    = "";

    loading                     : boolean   = false;


    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Changement du statut";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/statuses")
            .subscribe((data : any) => {
                this.statuses = data['statuses'];

                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    onSubmit() {
        var body = {
            "status" : $j("#statuses option:selected")[0].value
        };
        if (this.resId != "") {
            body["resId"] = this.resId;
        } else if (this.chrono != "") {
            body["chrono"] = this.chrono;
        }
        this.http.put(this.coreUrl + "rest/res/resource/status", body)
            .subscribe(() => {
                this.resId = "";
                this.chrono = "";
                $j('#statuses').prop('selectedIndex', 0);
                this.notify.success(this.lang.modificationSaved);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
