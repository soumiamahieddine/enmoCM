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
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>"+this.lang.administration+"</a> > "+this.lang.actions;
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
                this.loading = false;
                setTimeout(() => {
                    $j("[md2sortby='id']").click();
                }, 0);
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }

    deleteAction(action: any) {
        let r = confirm(this.lang.confirmAction+' '+this.lang.delete+' « '+action.label_action+' »');

        if (r) {
            this.http.delete(this.coreUrl + 'rest/actions/' + action.id)
                .subscribe((data : any) => {
                    this.data = data.action;
                    this.notify.success(this.lang.actionDeleted+' « '+action.label_action+' »');
                    
                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    }

}