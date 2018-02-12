import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort} from '@angular/material';


declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["baskets-administrationView"],
    providers   : [NotificationService]
})
export class BasketsAdministrationComponent implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;

    baskets                     : any[]     = [];

    loading                     : boolean   = false;

    displayedColumns = ['basket_id', 'basket_name', 'basket_desc', 'actions'];
    dataSource      : any;
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
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Bannettes";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/baskets")
            .subscribe((data : any) => {
                this.baskets = data['baskets'];
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.baskets);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    delete(basket: any) {
        this.http.delete(this.coreUrl + "rest/baskets/" + basket['basket_id'])
            .subscribe((data : any) => {
                this.notify.success(this.lang.basketDeleted);
                this.baskets = data['baskets'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
