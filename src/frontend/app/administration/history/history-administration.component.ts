import { Component, OnInit, ViewChild }  from '@angular/core';
import { HttpClient }                                       from '@angular/common/http';
import { MatPaginator } from '@angular/material/paginator';
import { MatSidenav } from '@angular/material/sidenav';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { LANG }                                             from '../../translate.component';
import { HeaderService }        from '../../../service/header.service';
import { AppService } from '../../../service/app.service';

declare function $j(selector: any): any;

@Component({
    templateUrl: "history-administration.component.html",
    providers: [AppService]
})
export class HistoryAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight  : MatSidenav;

    lang                            : any       = LANG;
    loading                         : boolean   = false;
    limitExceeded                   : boolean   = false;
    batchLimitExceeded              : boolean   = false;

    data                            : any[]     = [];
    batchData                       : any[]     = [];
    startDate                       : Date      = new Date();
    endDate                         : Date      = new Date();
    batchStartDate                  : Date      = new Date();
    batchEndDate                    : Date      = new Date();


    dataSource          = new MatTableDataSource(this.data);
    batchDataSource     = new MatTableDataSource(this.batchData);
    displayedColumns    = ['event_date', 'event_type', 'user_id', 'info', 'remote_ip'];
    batchDisplayedColumns = ['event_date', 'total_processed', 'total_errors', 'info', 'module_name'];

    accessBatchHistory              : boolean   = true;
    accessHistory                   : boolean   = true;


    @ViewChild('paginator', { static: false }) paginator: MatPaginator;
    @ViewChild('batchPaginator', { static: false }) batchPaginator: MatPaginator;
    @ViewChild('sort', { static: false }) sort: MatSort;
    @ViewChild('batchSort', { static: false }) batchSort: MatSort;
    applyFilter(filterValue: string, historyType : string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        if(historyType == 'normal'){
            this.dataSource.filter = filterValue;
        } else {
            this.batchDataSource.filter = filterValue
        }        
    }

    constructor(
        public http: HttpClient, 
        private headerService: HeaderService,
        public appService: AppService
    ) {
        $j("link[href='merged_css.php']").remove();

        this.startDate.setHours(0,0,0,0);
        this.startDate.setMonth(this.endDate.getMonth()-1);
        this.endDate.setHours(23,59,59,59);

        this.batchStartDate.setHours(0,0,0,0);
        this.batchStartDate.setMonth(this.endDate.getMonth()-1);
        this.batchEndDate.setHours(23,59,59,59);
    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.administration + ' ' + this.lang.history);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = true;

        this.http.get('../../rest/history', {params: {"startDate" : (this.startDate.getTime() / 1000).toString(), "endDate" : (this.endDate.getTime() / 1000).toString()}})
            .subscribe((data: any) => {
                this.data = data['history'];
                this.limitExceeded = data['limitExceeded'];
                this.loading = false;
                setTimeout(() => {
                    this.accessHistory = true;
                    this.dataSource = new MatTableDataSource(this.data);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, (data: any) => {
                if(data['error'].errors == 'Service forbidden'){
                        this.loading = false;
                        this.accessHistory = false;
                } else {
                    location.href = "index.php";
                }                
            });

        this.http.get('../../rest/batchHistories', {params: {"startDate" : (this.batchStartDate.getTime() / 1000).toString(), "endDate" : (this.batchEndDate.getTime() / 1000).toString()}})
            .subscribe((data: any) => {
                this.batchData = data['batchHistories'];
                this.batchLimitExceeded = data['limitExceeded'];
                this.loading = false;
                setTimeout(() => {
                    this.accessBatchHistory = true;
                    this.batchDataSource = new MatTableDataSource(this.batchData);
                    this.batchDataSource.paginator = this.batchPaginator;
                    this.batchDataSource.sort = this.batchSort;
                }, 0);
            }, (data: any) => {
                if(data['error'].errors == 'Service forbidden'){
                        this.loading = false;
                        this.accessBatchHistory = false;
                } else {
                    location.href = "index.php";
                }
                
            });
            
    }

    ngAfterViewInit() {
        this.batchDataSource.paginator = this.batchPaginator;
        this.batchDataSource.sort = this.batchSort;
    }
    
    _setDataSource(indexNumber : any) {
        setTimeout(() => {
            switch (indexNumber) {
                case 0:
                  !this.dataSource.paginator ? this.dataSource.paginator = this.paginator : null;
                  !this.dataSource.sort ? this.dataSource.sort = this.sort : null;
                  break;
                case 1:
                  !this.batchDataSource.paginator ? this.batchDataSource.paginator = this.batchPaginator : null;
                  !this.batchDataSource.sort ? this.batchDataSource.sort = this.batchSort : null;
            }
        });
    }

    refreshHistory(historyType : string) {
        if (historyType == 'normal') {
            this.startDate.setHours(0,0,0,0);
            this.endDate.setHours(23,59,59,59);
            this.http.get('../../rest/history', {params: {"startDate" : (this.startDate.getTime() / 1000).toString(), "endDate" : (this.endDate.getTime() / 1000).toString()}})
                .subscribe((data: any) => {
                    this.data = data['history'];
                    this.limitExceeded = data['limitExceeded'];
                    this.loading = false;
                    setTimeout(() => {
                        this.dataSource = new MatTableDataSource(this.data);
                        this.dataSource.paginator = this.paginator;
                        this.dataSource.sort = this.sort;
                    }, 0);
                }, () => {
                    location.href = "index.php";
                });
        } else {
            this.batchStartDate.setHours(0,0,0,0);
            this.batchEndDate.setHours(23,59,59,59);
            this.http.get('../../rest/batchHistories', {params: {"startDate" : (this.batchStartDate.getTime() / 1000).toString(), "endDate" : (this.batchEndDate.getTime() / 1000).toString()}})
                .subscribe((data: any) => {
                    this.batchData = data['batchHistories'];
                    this.batchLimitExceeded = data['limitExceeded'];
                    this.loading = false;
                    setTimeout(() => {
                        this.accessBatchHistory = true;
                        this.batchDataSource = new MatTableDataSource(this.batchData);
                        this.batchDataSource.paginator = this.batchPaginator;
                        this.batchDataSource.sort = this.batchSort;
                    }, 0);
                }, (data: any) => {
                    if(data['error'].errors == 'Service forbidden'){
                        this.loading = false;
                        this.accessBatchHistory = false;
                    } else {
                        location.href = "index.php";
                    }
                });
        }
    }
}
