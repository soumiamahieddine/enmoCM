import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService }        from '../../../service/header.service';
import { MatPaginator } from '@angular/material/paginator';
import { MatSidenav } from '@angular/material/sidenav';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { AppService } from '../../../service/app.service';
import {FunctionsService} from "../../../service/functions.service";

declare function $j(selector: any): any;

@Component({
    templateUrl: "parameters-administration.component.html",
    providers: [NotificationService, AppService]
})
export class ParametersAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight  : MatSidenav;
    
    lang: any = LANG;

    parameters: any = {};

    loading: boolean = false;

    displayedColumns = ['id', 'description', 'value', 'actions'];
    dataSource: any;
    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: false }) sort: MatSort;


    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        private headerService: HeaderService,
        public appService: AppService,
        public functions: FunctionsService
    ) {
        $j("link[href='merged_css.php']").remove();
    }

    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
        this.dataSource.filterPredicate = (template: any, filter: string) => {
            return this.functions.filterUnSensitive(template, filter, ['id', 'description', 'value']);
        };
    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.administration + ' ' + this.lang.parameters);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = true;

        this.http.get('../../rest/parameters')
            .subscribe((data: any) => {
                this.parameters = data.parameters;

                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.parameters);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);

                this.loading = false;
            });
    }

    deleteParameter(paramId: string) {
        let r = confirm(this.lang.deleteMsg);

        if (r) {
            this.http.delete('../../rest/parameters/' + paramId)
                .subscribe((data: any) => {
                    this.parameters = data.parameters;
                    this.dataSource = new MatTableDataSource(this.parameters);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                    this.notify.success(this.lang.parameterDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
