import { ChangeDetectorRef, Component, ViewChild, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
// import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogConfig, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';


declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: angularGlobals["notifications-schedule-administrationView"],
    providers: [NotificationService]
})
export class NotificationsScheduleAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;

    crontab: any[] = [];
    authorizedNotification: any[] = [];
    loading: boolean = false;
    lang: any = LANG;

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private router: Router, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);

        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.http.get(this.coreUrl + 'rest/notifications/schedule')
            .subscribe((data: any) => {
                this.crontab = data.crontab;
                this.authorizedNotification = data.authorizedNotification;
                this.loading = false;
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName +
                "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration +
                "</a> > <a onclick='location.hash = \"/administration/notifications\"' style='cursor: pointer'>" + this.lang.notifications +
                "</a> > " + this.lang.notificationsSchedule;
        }
    }

    onSubmit() {
        this.http.post(this.coreUrl + 'rest/notifications/schedule', this.crontab)
            .subscribe((data: any) => {
                this.router.navigate(['/administration/notifications']);
                this.notify.success(this.lang.NotificationScheduleUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

}
