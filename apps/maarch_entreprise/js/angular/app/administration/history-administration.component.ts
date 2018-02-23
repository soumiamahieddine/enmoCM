import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort } from '@angular/material';
import { MatDatepickerInputEvent } from '@angular/material/datepicker';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: angularGlobals["history-administrationView"],
    styleUrls: [],
    providers: [NotificationService]
})

export class HistoryAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;

    loading: boolean = false;
    data: History[] = [];
    CurrentYear: number = new Date().getFullYear();
    currentMonth: number = new Date().getMonth() + 1;
    minDate: Date = new Date();

    displayedColumns = ['event_date', 'event_type', 'user_id', 'info', 'remote_ip'];
    dataSource = new MatTableDataSource(this.data);
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " + this.lang.history;
        }
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.updateBreadcrumb(angularGlobals.applicationName);
        $j('#inner_content').remove();

        this.minDate = new Date(this.CurrentYear + '-' + this.currentMonth + '-01');
        this.http.get(this.coreUrl + 'rest/administration/history/eventDate/' + this.minDate.toJSON())
            .subscribe((data: any) => {
                this.data = data.historyList;
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.data);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }

    refreshHistory(event: MatDatepickerInputEvent<Date>) {
        console.log(event);
        this.http.get(this.coreUrl + 'rest/administration/history/eventDate/' + this.minDate.toJSON())
            .subscribe((data: any) => {
                this.data = data.historyList;
                this.dataSource = new MatTableDataSource(this.data);
                this.dataSource.paginator = this.paginator;
                this.dataSource.sort = this.sort;
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }
}
export interface History {
    event_date: Date;
    event_type: string;
    user_id: string;
    table_name: number;
    info: string;
    remote_ip: string;
}