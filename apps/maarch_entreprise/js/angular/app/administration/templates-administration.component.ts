import { ChangeDetectorRef, Component, ViewChild, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort} from '@angular/material';

declare function $j(selector: any): any;
declare var angularGlobals: any;

@Component({
    templateUrl: "../../../../Views/templates-administration.component.html",
    providers: [NotificationService]
})

export class TemplatesAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;
    search: string = null;

    templates: any[] = [];
    titles: any[] = [];

    loading: boolean = false;

    displayedColumns = ['template_label', 'template_comment', 'template_type', 'template_target', 'actions'];
    dataSource = new MatTableDataSource(this.templates);
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

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/templates')
            .subscribe((data) => {
                this.templates = data['templates'];
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.templates);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }

    deleteTemplate(template: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + template.template_label + ' »');

        if (r) {
            this.http.delete(this.coreUrl + 'rest/templates/' + template.template_id)
                .subscribe(() => {
                    for (let i in this.templates) {
                        if (this.templates[i].template_id == template.template_id) {
                            this.templates.splice(Number(i), 1);
                        }
                    }
                    this.dataSource = new MatTableDataSource(this.templates);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;

                    this.notify.success(this.lang.templateDeleted);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
