import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["historyBatch-administrationView"],
    styleUrls   : [],
    providers   : [NotificationService]
})

export class HistoryBatchAdministrationComponent implements OnInit {
    coreUrl                 : string;
    lang                    : any           = LANG;
    search                  : string        = null;
    _search                 : string    = '';

    filterModules           : any           = [];
    filterModule            : string        = '';
    filterByDate            : string        = '';
    
    loading                 : boolean       = false;
    data                    : any           = [];
    CurrentYear             : number        = new Date().getFullYear();
    currentMonth            : number        = new Date().getMonth()+1;
    minDate                 : Date          = new Date();
    
    
    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>"+this.lang.administration+"</a> > "+this.lang.historyBatch;
        }
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        
        this.loading = true;

        this.updateBreadcrumb(angularGlobals.applicationName);
        $j('#inner_content').remove();

        this.minDate = new Date(this.CurrentYear+'-'+this.currentMonth+'-01');

        this.http.get(this.coreUrl + 'rest/administration/historyBatch/eventDate/'+this.minDate.toJSON())
            .subscribe((data:any) => {
                this.data = data.historyList;

                this.filterModules = data.filters.modules;
                this.loading = false;
                setTimeout(() => {
                    $j("[md2sortby='event_date']").click().click();
                }, 0);
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }

    refreshHistory() {
        this.http.get(this.coreUrl + 'rest/administration/historyBatch/eventDate/'+this.minDate.toJSON())
        .subscribe((data:any) => {
            this.data = data.historyList;
            this.filterModules = data.filters.modules;

        }, (err) => {
            console.log(err);
            location.href = "index.php";
        });
    }

}