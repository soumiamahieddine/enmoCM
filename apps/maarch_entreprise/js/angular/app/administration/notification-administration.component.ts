import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { ActivatedRoute, Router } from '@angular/router';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare var angularGlobals : any;

@Component({
    templateUrl : angularGlobals["notification-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class NotificationAdministrationComponent implements OnInit {

    coreUrl             : string;
    notificationId              : string;
    creationMode                : boolean;
    notification                : any         = {};
    loading                     : boolean   = false;
    lang                        : any       = LANG;


    constructor(public http: HttpClient, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.prepareNotifications();

        this.loading = true;

        this.route.params.subscribe(params => {
        if (typeof params['id'] == "undefined") {
                this.creationMode = true;
                this.loading = false;
            }
        });
    }

    prepareNotifications() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/notifications\"' style='cursor: pointer'>notifications</a>";
        }
    }


}
