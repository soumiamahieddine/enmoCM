import { Component, OnInit} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;

@Component({
    templateUrl : angularGlobals["priorities-administrationView"],
    providers   : [NotificationService]
})
export class PrioritiesAdministrationComponent implements OnInit {

    coreUrl         : string;
    lang            : any       = LANG;
    loading         : boolean   = false;

    priorities      : any[]     = [];

    datatable       : any;

    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Priorités";
        }
    }
    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.updateBreadcrumb(angularGlobals.applicationName);

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/priorities')
            .subscribe((data : any) => {
                this.priorities = data["priorities"];

                this.loading = false;
            }, () => {
                location.href = "index.php";
            })
    }

    deletePriority(id: string) {
        let r = confirm(this.lang.deleteMsg);

        if (r) {
            this.http.delete(this.coreUrl + "rest/priorities/" + id)
                .subscribe((data : any) => {
                    this.priorities = data["priorities"];
                    this.notify.success(this.lang.priorityDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                })
        }
    }
}