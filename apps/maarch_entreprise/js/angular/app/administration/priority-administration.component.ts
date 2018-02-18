import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: angularGlobals["priority-administrationView"],
    providers: [NotificationService]
})
export class PriorityAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    id: string;
    creationMode: boolean;
    lang: any = LANG;
    loading: boolean = false;

    priority: any = {
        useDoctypeDelay: false,
        color: "#135f7f",
        delays: "0",
        working_days: "false"
    };
    selectedWorkingDays: any;

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
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/priorities\"' style='cursor: pointer'>" + this.lang.priorities + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.priorityCreation;
        } else {
            breadCrumb += this.lang.priorityModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;
                this.updateBreadcrumb(angularGlobals.applicationName);
                this.loading = false;
            } else {
                this.creationMode = false;
                this.updateBreadcrumb(angularGlobals.applicationName);
                this.id = params['id'];
                this.http.get(this.coreUrl + "rest/priorities/" + this.id)
                    .subscribe((data: any) => {
                        this.priority = data.priority;
                        if (this.priority.delays == '*') {
                            this.priority.useDoctypeDelay = false;
                        } else {
                            this.priority.useDoctypeDelay = true;
                        }
                        if (this.priority.working_days === true) {
                            this.priority.working_days = "true";
                        } else {
                            this.priority.working_days = "false";
                        }
                        this.loading = false;
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    onSubmit() {
        if (this.priority.useDoctypeDelay == false) {
            this.priority.delays = '*';
        }
        if (this.priority.working_days == "true") {
            this.priority.working_days = true
        } else {
            this.priority.working_days = false
        }
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/priorities", this.priority)
                .subscribe(() => {
                    this.notify.success(this.lang.priorityAdded);
                    this.router.navigate(["/administration/priorities"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/priorities/" + this.id, this.priority)
                .subscribe(() => {
                    this.notify.success(this.lang.priorityUpdated);
                    this.router.navigate(["/administration/priorities"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

}