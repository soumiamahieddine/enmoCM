import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;

@Component({
    templateUrl : angularGlobals["notifications-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css'],
    providers   : [NotificationService]
})
export class NotificationsAdministrationComponent implements OnInit {

    coreUrl                     : string;

    notifications               : any[]     = [];
    loading                     : boolean   = false;
    lang                        : any       = LANG;


    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);

        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.http.get(this.coreUrl + 'rest/notifications')
            .subscribe((data : any) => {
                this.notifications = data.notifications;
                this.loading = false;
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updateBreadcrumb(applicationName: string) {
        $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > "+
                                            "<a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " + this.lang.admin_notifications;
    }

    deleteNotification(notification : any) {
        let r = confirm(this.lang.deleteMsg);

        if (r) {
            this.http.delete(this.coreUrl + 'rest/notifications/' + notification.notification_sid)
                .subscribe((data : any) => {
                    this.notifications = data.notifications;
                    this.notify.success(data.success);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
