import { Component, OnInit, ViewChild, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { AppService } from '../../service/app.service';
import { tap, catchError, finalize } from 'rxjs/operators';
import { of } from 'rxjs';

declare function $j(selector: any): any;

@Component({
    selector: 'app-linked-resource-list',
    templateUrl: "linked-resource-list.component.html",
    styleUrls: ['linked-resource-list.component.scss'],
    providers: [AppService]
})
export class LinkedResourceListComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    linkedResources: any[] = [];
    dataSource: any;
    displayedColumns = ['resId', 'actions'];

    @Input('resId') resId: number;

    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: false }) sort: MatSort;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public appService: AppService
    ) { }

    ngOnInit(): void {
        this.loading = true;
        this.initLinkedResources();
    }

    initLinkedResources() {
        this.http.get('../../rest/resources/100/linkedResources').pipe(
            tap((data: any) => {
                this.linkedResources = data.linkedResources;
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
}
