import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;

@Component({
    templateUrl : angularGlobals['statuses-administrationView'],
    styleUrls   : [],
    providers   : [NotificationService]
})
export class StatusesAdministrationComponent implements OnInit {
    coreUrl                     : string;
    lang                        : any       = LANG;

    nbStatus                    : number;
    statusList                  : any;
    data                        : any       = [];

    loading                     : boolean   = false;


    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.prepareStatus();

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/administration/status')
            .subscribe((data : any) => {
                this.statusList = data.statusList;
                this.data = this.statusList; 
                setTimeout(() => {
                    $j("[md2sortby='label_status']").click();
                }, 0);
                this.updateBreadcrumb(angularGlobals.applicationName);
                this.loading = false;
            }, (err) => {
                this.notify.error(JSON.parse(err._body).errors);
            });
    }
    
    prepareStatus() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
        $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > "+
                                            "<a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " + this.lang.statuses;
    }

    deleteStatus(status : any){
        var resp = confirm(this.lang.confirmAction+' '+this.lang.delete+' « '+status.id+' »');
        if(resp){
            this.http.delete(this.coreUrl + 'rest/status/'+status.identifier)
                .subscribe((data : any) => {
                    this.data = data.statuses;
                    this.notify.success(this.lang.statusDeleted+' « '+status.id+' »');
                    
                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    }
 
}
