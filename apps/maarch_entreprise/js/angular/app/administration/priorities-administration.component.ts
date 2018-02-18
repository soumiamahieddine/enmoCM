import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: angularGlobals["priorities-administrationView"],
    providers: [NotificationService]
})
export class PrioritiesAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;
    loading: boolean = false;

    priorities: any[] = [];

    datatable: any;

    displayedColumns = ['label', 'delays', 'working_days', 'actions'];
    dataSource: any;
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
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " + this.lang.priorities;
        }
    }
    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.updateBreadcrumb(angularGlobals.applicationName);

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/priorities')
            .subscribe((data: any) => {
                this.priorities = data["priorities"];
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.priorities);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            })
    }

    deletePriority(id: string) {
        let r = confirm(this.lang.deleteMsg);

        if (r) {
            this.http.delete(this.coreUrl + "rest/priorities/" + id)
                .subscribe((data: any) => {
                    this.priorities = data["priorities"];
                    this.dataSource = new MatTableDataSource(this.priorities);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                    this.notify.success(this.lang.priorityDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                })
        }
    }
}