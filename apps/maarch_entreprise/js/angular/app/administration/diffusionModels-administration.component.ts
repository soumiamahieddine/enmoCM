import { ChangeDetectorRef, Component, OnInit, ViewChild, Inject, TemplateRef } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogConfig, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: angularGlobals["diffusionModels-administrationView"],
    providers: [NotificationService]
})
export class DiffusionModelsAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    dialogRef: MatDialogRef<any>;
    private _mobileQueryListener: () => void;
    config: any = {};
    coreUrl: string;
    lang: any = LANG;

    listTemplates: any[] = [];
    listTemplatesForAssign: any[] = [];

    loading: boolean = false;

    displayedColumns = ['title', 'description', 'object_type', 'actions'];
    dataSource = new MatTableDataSource(this.listTemplates);
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher,public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) {
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
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Groupes";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/listTemplates")
            .subscribe((data: any) => {
                this.listTemplates = data['listTemplates'];
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
        this.http.delete(this.coreUrl + "rest/listTemplates/" + listTemplate['id'])
            .subscribe((data: any) => {
                setTimeout(() => {
                    this.listTemplates = data['listTemplates'];
                    this.dataSource = new MatTableDataSource(this.listTemplates);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
                this.notify.success(this.lang.groupDeleted);

            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}