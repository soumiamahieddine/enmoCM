import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService }        from '../../../service/header.service';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSidenav } from '@angular/material/sidenav';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { AppService } from '../../../service/app.service';

declare function $j(selector: any): any;

@Component({
    templateUrl: "diffusionModels-administration.component.html",
    providers: [NotificationService, AppService]
})
export class DiffusionModelsAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight  : MatSidenav;

    dialogRef                       : MatDialogRef<any>;

    lang                            : any       = LANG;
    loading                         : boolean   = false;

    config                          : any       = {};
    listTemplates                   : any[]     = [];
    listTemplatesForAssign          : any[]     = [];

    displayedColumns    = ['title', 'description', 'object_type', 'actions'];
    dataSource          = new MatTableDataSource(this.listTemplates);


    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: false }) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.dataSource.filter = filterValue;
    }

    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        public dialog: MatDialog, 
        private headerService: HeaderService,
        public appService: AppService
    ) {
        $j("link[href='merged_css.php']").remove();
    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.administration + ' ' + this.lang.diffusionModels);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = true;

        this.http.get("../../rest/listTemplates?admin=true")
            .subscribe((data: any) => {
                data['listTemplates'].forEach((template: any) => {
                    if ((template.type.indexOf('visaCircuit') != -1 || template.type.indexOf('opinionCircuit') != -1) && template.entityId == null) {
                        this.listTemplates.push(template);
                    }
                });
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.listTemplates);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    delete(listTemplate: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + listTemplate.title + ' »');

        if (r) {
            this.http.delete("../../rest/listTemplates/" + listTemplate['id'])
                .subscribe(() => {
                    setTimeout(() => {
                        var i = 0;
                        this.listTemplates.forEach((template: any) => {
                            if (template.id == listTemplate['id']) {
                                this.listTemplates.splice(i, 1);
                            }
                            i++;
                        });
                        this.dataSource = new MatTableDataSource(this.listTemplates);
                        this.dataSource.paginator = this.paginator;
                        this.dataSource.sort = this.sort;
                    }, 0);
                    this.notify.success(this.lang.diffusionModelDeleted);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
