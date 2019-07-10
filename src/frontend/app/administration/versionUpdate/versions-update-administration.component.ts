import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { MatSidenav, MatDialog, MatDialogRef } from '@angular/material';
import { HeaderService } from '../../../service/header.service';
import { tap, catchError, exhaustMap } from 'rxjs/operators';
import { NotificationService } from '../../notification.service';
import { of } from 'rxjs';
import { AlertComponent } from '../../../plugins/modal/alert.component';

declare function $j(selector: any): any;

@Component({
    templateUrl: "versions-update-administration.component.html",
    styleUrls: ['versions-update-administration.component.scss'],
    providers: [NotificationService],
})
export class VersionsUpdateAdministrationComponent implements OnInit {

    @ViewChild('snav') public sidenavLeft: MatSidenav;
    @ViewChild('snav2') public sidenavRight: MatSidenav;

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;

    coreUrl: string;
    lang: any = LANG;
    loading: boolean = false;
    updateInprogress: boolean = false;
    dialogRef: MatDialogRef<any>;

    versions: any = {};


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private headerService: HeaderService, private notify: NotificationService, public dialog: MatDialog) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.updateVersionControl);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = true;

        this.http.get('../../rest/versionsUpdate').pipe(
            tap((data: any) => {
                this.versions = data;
            }),
            catchError(err => {
                this.notify.handleErrors(err);
                return of(false);
            }),
            tap(() => {
                this.loading = false;
            })
        ).subscribe();
    }

    updateVersionAccess() {
        this.updateInprogress = true;

        this.http.put('../../rest/versionsUpdate', {}).pipe(
            tap(() => {
                this.dialogRef = this.dialog.open(AlertComponent, { autoFocus: false, disableClose: true, data: { mode: '', title: this.lang.updateOk, msg: '' } });
            }),
            exhaustMap(() => this.dialogRef.afterClosed()),
            tap(() => {
                window.location.reload(true);
                this.dialogRef = null;
            }),
            catchError(err => {
                this.notify.handleErrors(err);
                return of(false);
            }),
            tap(() => {
                this.updateInprogress = false;
            })
        ).subscribe();
    }
}
