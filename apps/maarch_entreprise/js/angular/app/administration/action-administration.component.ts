import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: angularGlobals["action-administrationView"],
    providers: [NotificationService]
})
export class ActionAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    lang: any = LANG;
    coreUrl: string;
    creationMode: boolean;
    action: any = {};
    statuses: any[] = [];
    actionPagesList: any[] = [];
    categoriesList: any[] = [];
    keywordsList: any[] = [];

    loading: boolean = false;

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    updateBreadcrumb(applicationName: string) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/actions\"' style='cursor: pointer'>" + this.lang.actions + "</a> > ";

        if (this.creationMode == true) {
            breadCrumb += this.lang.actionCreation;
        } else {
            breadCrumb += this.lang.actionModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    }

    prepareActions() {
        $j('#inner_content').remove();
    }

    ngOnInit(): void {
        this.prepareActions();

        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;

                this.http.get(this.coreUrl + 'rest/initAction')
                    .subscribe((data: any) => {
                        this.action = data.action;
                        this.categoriesList = data.categoriesList;
                        this.statuses = data.statuses;

                        this.actionPagesList = data.action_pagesList;
                        this.keywordsList = data.keywordsList;
                        this.loading = false;
                    });
            }
            else {
                this.creationMode = false;

                this.http.get(this.coreUrl + 'rest/actions/' + params['id'])
                    .subscribe((data: any) => {
                        this.action = data.action;
                        this.categoriesList = data.categoriesList;
                        this.statuses = data.statuses;

                        this.actionPagesList = data.action_pagesList;
                        this.keywordsList = data.keywordsList;
                        this.loading = false;
                    });
            }
        });

        this.updateBreadcrumb(angularGlobals.applicationName);

    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + 'rest/actions', this.action)
                .subscribe((data: any) => {
                    this.router.navigate(['/administration/actions']);
                    this.notify.success(this.lang.actionAdded);

                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        } else {
            this.http.put(this.coreUrl + 'rest/actions/' + this.action.id, this.action)
                .subscribe((data: any) => {
                    this.router.navigate(['/administration/actions']);
                    this.notify.success(this.lang.actionUpdated);

                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    }
}