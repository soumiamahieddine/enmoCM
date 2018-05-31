import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { MatPaginator, MatTableDataSource, MatSort } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "../../../../Views/docservers-administration.component.html"
})
export class DocserversAdministrationComponent implements OnInit {

    mobileQuery                     : MediaQueryList;
    private _mobileQueryListener    : () => void;

    coreUrl     : string;
    lang        : any = LANG;
    loading     : boolean = false;
    dataSource  : any;

    docservers    : any = {};
    docserversFasthd : any = [];

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/docservers')
            .subscribe((data: any) => {
                this.docservers = data.docservers;

                this.docserversFasthd = [
                    {
                        actual_size_number: 44444444444,
                        adr_priority_number: 2,
                        coll_id: "letterbox_coll",
                        creation_date: "2011-01-13 14:47:49.197164",
                        device_label: "Fast internal disc bay for letterbox mode",
                        docserver_id: "FASTHD_MAN",
                        docserver_location_id: "NANTERRE",
                        docserver_type_id: "DOC",
                        enabled: "Y",
                        is_readonly: "N",
                        path_template: "/var/www/html/docservers/maarch_courrier_develop/manual/",
                        priority_number: 10,
                        size_limit_number: 50000000000
                    },
                    {
                        actual_size_number: 2455890616,
                        adr_priority_number: 2,
                        coll_id: "letterbox_coll",
                        creation_date: "2011-01-13 14:47:49.197164",
                        device_label: "Fast internal disc bay for letterbox mode",
                        docserver_id: "FASTHD_AI",
                        docserver_location_id: "NANTERRE",
                        docserver_type_id: "DOC",
                        enabled: "Y",
                        is_readonly: "Y",
                        path_template: "/var/www/html/docservers/maarch_courrier_develop/ai/",
                        priority_number: 10,
                        size_limit_number: 50000000000
                    }
                ]
                this.docserversFasthd.forEach((elem: any, index: number) => {
                    var factor = null;

                    elem.size_limit_number = elem.size_limit_number / 1000000000;
                    factor = Math.pow(10, 2);
                    elem.size_limit_number = Math.round(elem.size_limit_number * factor) / factor;

                    elem.actual_size_number = elem.actual_size_number / 1000000000;
                    factor = Math.pow(10, 2);
                    elem.actual_size_number = Math.round(elem.actual_size_number * factor) / factor;

                    //percent
                    elem.percent_number = (elem.actual_size_number*100)/elem.size_limit_number;
                });

                console.log(this.docserversFasthd);
                
                this.loading = false;

            });
    }
}
