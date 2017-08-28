import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["actions-administrationView"],
    styleUrls   : [],
    providers   : [NotificationService]
})

export class ActionsAdministrationComponent implements OnInit {
    coreUrl                 : string;
    lang                    : any           = LANG;
    search                  : string        = null;

    actions                 : any[]         = [];
    titles                  : any[]         = [];

    loading                 : boolean       = false;
    data                    : any           = [];

    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Actions";
        }
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        
        this.loading = true;

        this.updateBreadcrumb(angularGlobals.applicationName);
        $j('#inner_content').remove();

        this.http.get(this.coreUrl + 'rest/administration/actions')
            .subscribe((data) => {
                this.actions = data['actions'];
                this.data = this.actions;
                this.titles = data['titles'];
                this.loading = false;
                setTimeout(() => {
                    $j("[md2sortby='id']").click();
                }, 0);
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }

    deleteAction(id: number) {
        let r = confirm(this.lang.deleteMsg+' ?');

        if (r) {
            this.http.delete(this.coreUrl + 'rest/actions/' + id)
                .subscribe((data : any) => {
                    this.data = data.action;
                    this.notify.success(data.success);
                    
                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    }

}