import { Component, OnInit, ViewChild, TemplateRef, ViewContainerRef } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { TranslateService } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../../../service/notification/notification.service';
import { HeaderService } from '../../../service/header.service';
import { AppService } from '../../../service/app.service';
import { FunctionsService } from '../../../service/functions.service';
import { AdministrationService } from '../administration.service';
import { MatDialog } from '@angular/material/dialog';
import { tap, catchError, filter, exhaustMap } from 'rxjs/operators';
import { ConfirmComponent } from '../../../plugins/modal/confirm.component';
import { of } from 'rxjs/internal/observable/of';

@Component({
    selector: 'app-registered-mail-list',
    templateUrl: './registered-mail-list.component.html',
    styleUrls: ['./registered-mail-list.component.scss']
})
export class RegisteredMailListComponent implements OnInit {

    subMenus: any[] = [
        {
            icon: 'fas fa-dolly-flatbed',
            route: '/administration/registeredMails',
            label: this.translate.instant('lang.registeredMailNumberRanges'),
            current: true
        },
        {
            icon: 'fas fa-warehouse',
            route: '/administration/issuingSites',
            label: this.translate.instant('lang.issuingSites'),
            current: false
        },
    ];

    @ViewChild('adminMenuTemplate', { static: true }) adminMenuTemplate: TemplateRef<any>;

    parameters: any = {};

    loading: boolean = true;

    data: any[] = [];

    displayedColumns = ['customerAccountNumber', 'trackerNumber', 'registredMailType', 'rangeNumber', 'currentNumber', 'status', 'fullness', 'actions'];
    filterColumns = ['customerAccountNumber', 'trackerNumber', 'registredMailType', 'rangeNumber', 'currentNumber', 'fullness', 'statusLabel'];

    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: false }) sort: MatSort;

    constructor(
        private translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        private headerService: HeaderService,
        public appService: AppService,
        public functions: FunctionsService,
        public adminService: AdministrationService,
        private viewContainerRef: ViewContainerRef,
        public dialog: MatDialog
    ) { }

    ngOnInit(): void {
        this.headerService.setHeader(this.translate.instant('lang.administration') + ' ' + this.translate.instant('lang.registeredMailNumberRanges'));

        this.headerService.injectInSideBarLeft(this.adminMenuTemplate, this.viewContainerRef, 'adminMenu');
        this.loading = false;
        this.getData();
    }

    getData() {
        this.data = [];

        // FOR TEST
        this.data = [
            {
                id: 1,
                customerAccountNumber : '1098498410',
                trackerNumber: 'AZPOKF30KDZP',
                registredMailType: 'B01',
                rangeStart: 1000,
                rangeEnd: 1500,
                currentNumber: null,
                fullness: 100,
                status: 'END',
            },
            {
                id: 2,
                customerAccountNumber : '1098498410',
                trackerNumber: 'AZPOKF30KDZP',
                registredMailType: 'B01',
                rangeStart: 1501,
                rangeEnd: 2000,
                currentNumber: 1621,
                fullness: 26,
                status: 'OK',
            },
            {
                id: 3,
                customerAccountNumber : '1098498410',
                trackerNumber: 'AZPOKF30KDZP',
                registredMailType: 'B02',
                rangeStart: 1501,
                rangeEnd: 2000,
                currentNumber: null,
                fullness: 0,
                status: 'OK',
            },
            {
                id: 2,
                customerAccountNumber : '1098498410',
                trackerNumber: 'AZPOKF30KDZP',
                registredMailType: 'B01',
                rangeStart: 2001,
                rangeEnd: 2500,
                currentNumber: null,
                fullness: 0,
                status: 'SPD',
            },
        ];
        this.data = this.data.map((item: any) => {
            return {
                ...item,
                statusLabel : item.status !== 'OK' ? this.translate.instant('lang.inactive') : this.translate.instant('lang.active'),
                rangeNumber : `${item.rangeStart} - ${item.rangeEnd}`,
            };
        });
        this.loading = false;
        setTimeout(() => {
            this.adminService.setDataSource('admin_regitered_mail', this.data, this.sort, this.paginator, this.filterColumns);
        }, 0);


        /*this.http.get('../rest/registeredMail').pipe(
            tap((data: any) => {
                this.data = data['sites'];
                this.loading = false;
                setTimeout(() => {
                    this.adminService.setDataSource('admin_regitered_mail_issuing_site', this.data, this.sort, this.paginator, this.filterColumns);
                }, 0);
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();*/
    }

    activate(row: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { panelClass: 'maarch-modal', autoFocus: false, disableClose: true, data: { title: this.translate.instant('lang.activate'), msg: 'En activant cette plage, cela clôtura la plage actuelle utilisée pour ce type de recommandé.' } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            // exhaustMap(() => this.http.delete(`../rest/registeredMail/${row.id}`)),
            tap(() => {
                row.status = 'OK';
                row.statusLabel = this.translate.instant('lang.active');
                setTimeout(() => {
                    this.adminService.setDataSource('admin_regitered_mail_issuing_site', this.data, this.sort, this.paginator, this.filterColumns);
                }, 0);
                this.notify.success(this.translate.instant('Plage activée'));
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    stop(row: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { panelClass: 'maarch-modal', autoFocus: false, disableClose: true, data: { title: this.translate.instant('lang.suspend'), msg: 'En clôturant la plage, vous ne pourrez plus utiliser de recommandé de ce type tant que vous n\'en n\'aurez pas activé une autre.' } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            // exhaustMap(() => this.http.delete(`../rest/registeredMail/${row.id}`)),
            tap(() => {
                row.status = 'END';
                row.statusLabel = this.translate.instant('lang.inactive');
                setTimeout(() => {
                    this.adminService.setDataSource('admin_regitered_mail_issuing_site', this.data, this.sort, this.paginator, this.filterColumns);
                }, 0);
                this.notify.success(this.translate.instant('Plage cloturée'));
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    delete(row: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { panelClass: 'maarch-modal', autoFocus: false, disableClose: true, data: { title: this.translate.instant('lang.delete'), msg: this.translate.instant('lang.confirmAction') } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            // exhaustMap(() => this.http.delete(`../rest/registeredMail/${row.id}`)),
            tap(() => {
                this.data = this.data.filter((item: any) => item.id !== row.id);
                setTimeout(() => {
                    this.adminService.setDataSource('admin_regitered_mail_issuing_site', this.data, this.sort, this.paginator, this.filterColumns);
                }, 0);
                this.notify.success(this.translate.instant('lang.issuingSiteDeleted'));
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

}
