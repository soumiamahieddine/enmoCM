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
    templateUrl: "../../../../Views/historyBatch-administration.component.html",
    providers: [NotificationService]
})

export class HistoryBatchAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;

    loading: boolean = false;
    data                            : any[]     = [];
    
    startDate                       : Date      = new Date();
    endDate                         : Date      = new Date();

    displayedColumns = ['batch_id', 'event_date', 'total_processed', 'total_errors', 'info', 'module_name'];
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

        this.startDate.setHours(0,0,0,0);
        this.startDate.setMonth(this.endDate.getMonth()-1);
        this.endDate.setHours(23,59,59,59);

        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " + this.lang.historyBatch;
        }
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.updateBreadcrumb(angularGlobals.applicationName);
        $j('#inner_content').remove();

        this.http.get(this.coreUrl + 'rest/batchHistories', {params: {"startDate" : (this.startDate.getTime() / 1000).toString(), "endDate" : (this.endDate.getTime() / 1000).toString()}})
            .subscribe((data: any) => {
                this.data = data['batchHistories'];
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.data);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    refreshHistory() {
        this.startDate.setHours(0,0,0,0);
        this.endDate.setHours(23,59,59,59);
        
        this.http.get(this.coreUrl + 'rest/batchHistories', {params: {"startDate" : (this.startDate.getTime() / 1000).toString(), "endDate" : (this.endDate.getTime() / 1000).toString()}})
            .subscribe((data: any) => {
                this.data = data['batchHistories'];
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.data);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }
}