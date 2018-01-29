import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator,MatTableDataSource, MatSort } from '@angular/material';

declare function $j(selector: any) : any;

declare var angularGlobals : any;

@Component({
    templateUrl : angularGlobals["parameters-administrationView"],
    styleUrls   : ['css/parameters-administration.component.css'],
    providers   : [NotificationService]
})

export class ParametersAdministrationComponent implements OnInit {
    coreUrl         : string;

    lang            : any           = LANG;

    parametersList  : any;    

    resultInfo      : string        = "";
    loading         : boolean       = false;
    data            : Parameter[]   = [];

    displayedColumns = ['id','description','param_value_string','param_value_int','param_value_date','actions'];
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
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>"+this.lang.administration+"</a> > "+this.lang.parameters;
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        
        this.http.get(this.coreUrl + 'rest/administration/parameters')
            .subscribe((data : any) => {
                this.parametersList = data.parametersList;
                this.data = this.parametersList;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.data);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
                this.loading = false;

            });
    }

    goUrl(){
        location.href = 'index.php?admin=parameters&page=control_param_technic';
    }

    deleteParameter(paramId : string){
        let resp = confirm(this.lang.confirmAction+' '+this.lang.delete+' « '+paramId+' »');
        if (resp) {
            this.http.delete(this.coreUrl + 'rest/parameters/'+paramId)
                .subscribe((data : any) => {
                    this.data = data.parameters;
                    this.dataSource = new MatTableDataSource(this.data);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                    this.notify.success(this.lang.parameterDeleted+' « '+paramId+' »');           
                },(err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    }
}
export interface Parameter {
    id: string;
    description: string;
    param_value_string: string;
    param_value_int: number;
    param_value_date: Date;
}