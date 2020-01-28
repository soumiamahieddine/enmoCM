import { Component, OnInit, ViewChild, Input, Output, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { AppService } from '../../service/app.service';
import { tap, catchError, finalize, map, filter, exhaustMap } from 'rxjs/operators';
import { of } from 'rxjs';
import { ConfirmComponent } from '../../plugins/modal/confirm.component';
import { MatDialog } from '@angular/material';
import { LinkResourceModalComponent } from './linkResourceModal/link-resource-modal.component';

declare function $j(selector: any): any;

@Component({
    selector: 'app-linked-resource-list',
    templateUrl: "linked-resource-list.component.html",
    styleUrls: ['linked-resource-list.component.scss'],
    providers: [AppService]
})
export class LinkedResourceListComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = true;

    linkedResources: any[] = [];
    dataSource: any;
    displayedColumns = ['resId'];

    thumbnailUrl: string = '';

    @Input('resId') resId: number;
    @Output() reloadBadgeLinkedResources = new EventEmitter<string>();

    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: false }) sort: MatSort;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public appService: AppService,
        public dialog: MatDialog,
    ) { }

    ngOnInit(): void {
        this.loading = true;
        this.initLinkedResources();
    }

    initLinkedResources() {
        this.http.get(`../../rest/resources/${this.resId}/linkedResources`).pipe(
            tap((data: any) => {
                this.linkedResources = data.linkedResources;
                this.reloadBadgeLinkedResources.emit(`${this.linkedResources.length}`);
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.linkedResources);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err)
                return of(false);
            })
        ).subscribe();
    }

    getUsersVisaCircuit(row: any) {
        return row.visaCircuit.map((item: any) => item.userLabel);
    }

    unlinkResource(row: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.unlink, msg: this.lang.confirmAction } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.delete(`../../rest/resources/${this.resId}/linkedResources/${row.resId}`)),
            tap(() => {
                this.linkedResources = this.linkedResources.filter(resource => resource.resId !== row.resId);
                this.reloadBadgeLinkedResources.emit(`${this.linkedResources.length}`);
                this.dataSource = new MatTableDataSource(this.linkedResources);
                this.dataSource.paginator = this.paginator;
                this.dataSource.sort = this.sort;
                this.notify.success(this.lang.resourceUnlinked);
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    viewThumbnail(row: any) {
        if (row.hasDocument) {
            this.thumbnailUrl = '../../rest/resources/' + row.resId + '/thumbnail';
            $j('#viewThumbnail').show();
        }
    }

    closeThumbnail() {
        $j('#viewThumbnail').hide();
    }

    openSearchResourceModal() {
        const dialogRef =  this.dialog.open(LinkResourceModalComponent, { width: '80%',data: { resId: this.resId, currentLinkedRes : this.linkedResources.map(res => res.resId) } });
        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'success'),
            tap(() => {
                this.initLinkedResources();
                this.notify.success(this.lang.resourcesLinked);
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
