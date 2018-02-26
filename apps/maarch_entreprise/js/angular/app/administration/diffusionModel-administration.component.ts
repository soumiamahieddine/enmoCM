import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort} from '@angular/material';

import { AutoCompletePlugin } from '../../plugins/autocomplete.plugin';

declare function $j(selector: any) : any;

declare const angularGlobals : any;


@Component({
    templateUrl : angularGlobals["diffusionModel-administrationView"],
    providers   : [NotificationService]
})
export class DiffusionModelAdministrationComponent extends AutoCompletePlugin implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl                     : string;
    lang                        : any       = LANG;

    creationMode                : boolean;

    diffusionModel               : any       = {};
    loading                     : boolean   = false;

    displayedColumns = ['firstname', 'lastname'];
    dataSource      : any;
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher,public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService) {
        super(http, ['users']);
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }
    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }
    
    updateBreadcrumb(applicationName: string) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/groups\"' style='cursor: pointer'>" + this.lang.groups + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.groupCreation;
        } else {
            breadCrumb += this.lang.groupModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;
                this.loading = false;
                this.updateBreadcrumb(angularGlobals.applicationName);
            } else {
                this.creationMode = false;
                this.http.get(this.coreUrl + "rest/listTemplates/" + params['id'])
                    .subscribe((data : any) => {
                        this.updateBreadcrumb(angularGlobals.applicationName);
                        this.diffusionModel = data['listTemplate'];
                        this.diffusionModel.roles = [{
                            "id":"avis",
                            "label":"avis"
                        }]
                        this.loading = false;
                        setTimeout(() => {
                            this.dataSource = new MatTableDataSource(this.diffusionModel);
                            this.dataSource.paginator = this.paginator;
                            this.dataSource.sort = this.sort;
                        }, 0);

                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }
}
