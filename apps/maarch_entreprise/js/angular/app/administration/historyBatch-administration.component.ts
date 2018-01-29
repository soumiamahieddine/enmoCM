import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort } from '@angular/material';
import {MatDatepickerInputEvent} from '@angular/material/datepicker';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["historyBatch-administrationView"],
    styleUrls   : [],
    providers   : [NotificationService]
})

export class HistoryBatchAdministrationComponent implements OnInit {
    coreUrl                 : string;
    lang                    : any           = LANG;
    
    loading                 : boolean       = false;
    data: HistoryBatch[] = [];
    CurrentYear: number = new Date().getFullYear();
    currentMonth: number = new Date().getMonth() + 1;
    minDate: Date = new Date();
    
    displayedColumns = ['batch_id','event_date', 'total_processed', 'total_errors', 'info', 'module_name'];
    dataSource = new MatTableDataSource(this.data);
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>"+this.lang.administration+"</a> > "+this.lang.historyBatch;
        }
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        
        this.loading = true;

        this.updateBreadcrumb(angularGlobals.applicationName);
        $j('#inner_content').remove();

        this.minDate = new Date(this.CurrentYear+'-'+this.currentMonth+'-01');

        this.http.get(this.coreUrl + 'rest/administration/historyBatch/eventDate/'+this.minDate.toJSON())
            .subscribe((data:any) => {
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

    refreshHistory() {
        this.http.get(this.coreUrl + 'rest/administration/historyBatch/eventDate/'+this.minDate.toJSON())
        .subscribe((data:any) => {
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
export interface HistoryBatch {
    batch_id: number;
    event_date: Date;
    total_processed: string;
    total_errors: string;
    info: string;
    module_name: string;
}