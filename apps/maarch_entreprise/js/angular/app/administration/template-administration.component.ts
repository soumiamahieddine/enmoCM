import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any): any;
declare var angularGlobals: any;

@Component({
    templateUrl: "../../../../Views/template-administration.component.html",
    providers: [NotificationService]
})
export class TemplateAdministrationComponent implements OnInit {

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    lang: any = LANG;
    coreUrl: string;
    creationMode: boolean;
    template: any = {};
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

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.route.params.subscribe(params => {
            console.log('aa');
            if (typeof params['id'] == "undefined") {
                console.log('b');
                this.creationMode = true;

                // this.http.get(this.coreUrl + 'rest/initAction')
                //     .subscribe((data: any) => {
                //         this.action = data.action;
                //         this.categoriesList = data.categoriesList;
                //         this.statuses = data.statuses;

                //         this.actionPagesList = data.action_pagesList;
                //         this.keywordsList = data.keywordsList;
                        this.loading = false;
                //     });
            }
            else {
                this.creationMode = false;
                console.log('c');
                this.http.get(this.coreUrl + 'rest/templates/' + params['id'])
                    .subscribe((data: any) => {
                        this.template = data.template;
                        // this.categoriesList = data.categoriesList;
                        // this.statuses = data.statuses;

                        // this.actionPagesList = data.action_pagesList;
                        // this.keywordsList = data.keywordsList;
                        this.loading = false;
                    });
            }
        });
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + 'rest/templates', this.template)
                .subscribe(() => {
                    this.router.navigate(['/administration/templates']);
                    this.notify.success(this.lang.templateAdded);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + 'rest/templates/' + this.template.template_id, this.template)
                .subscribe(() => {
                    this.router.navigate(['/administration/templates']);
                    this.notify.success(this.lang.templateUpdated);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}