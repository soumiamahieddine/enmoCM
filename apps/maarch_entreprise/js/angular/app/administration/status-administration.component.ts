import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: angularGlobals['status-administrationView'],
    styleUrls: ['css/status-administration.component.css'],
    providers: [NotificationService]
})
export class StatusAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;

    creationMode: boolean;

    statusIdentifier: string;
    status: any = {
        id: null,
        label_status: null,
        can_be_searched: null,
        can_be_modified: null,
        is_folder_status: null,
        img_filename: null
    };
    statusImages: any = "";

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
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;

        this.prepareStatus();

        this.route.params.subscribe((params) => {
            if (typeof params['identifier'] == "undefined") {
                this.http.get(this.coreUrl + 'rest/administration/statuses/new')
                    .subscribe((data) => {
                        this.status.img_filename = "fm-letter";
                        this.status.can_be_searched = true
                        this.status.can_be_modified = true
                        this.statusImages = data['statusImages'];
                        this.creationMode = true;
                        this.loading = false;
                    });
            } else {
                this.creationMode = false;
                this.statusIdentifier = params['identifier'];
                this.getStatusInfos(this.statusIdentifier);
                this.loading = false;
            }

            this.updateBreadcrumb(angularGlobals.applicationName);
        });
    }

    prepareStatus() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > " +
            "<a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " +
            "<a onclick='location.hash = \"/administration/statuses\"' style='cursor: pointer'>" + this.lang.statuses + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.statusCreation;
        } else {
            breadCrumb += this.lang.statusModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    }

    getStatusInfos(statusIdentifier: string) {
        this.http.get(this.coreUrl + 'rest/statuses/' + statusIdentifier)
            .subscribe((data) => {
                this.status = data['status'][0];
                if (this.status.can_be_searched == 'Y') {
                    this.status.can_be_searched = true;
                } else {
                    this.status.can_be_searched = false;
                }
                if (this.status.can_be_modified == 'Y') {
                    this.status.can_be_modified = true;
                } else {
                    this.status.can_be_modified = false;
                }
                if (this.status.is_folder_status == 'Y') {
                    this.status.is_folder_status = true;
                } else {
                    this.status.is_folder_status = false;
                }
                this.statusImages = data['statusImages'];
            }, (err) => {
                this.notify.error(JSON.parse(err._body).errors);
            });
    }

    submitStatus() {
        if (this.creationMode == true) {
            this.http.post(this.coreUrl + 'rest/statuses', this.status)
                .subscribe((data: any) => {
                    this.notify.success(this.lang.statusAdded);
                    this.router.navigate(['administration/statuses']);
                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        } else if (this.creationMode == false) {

            this.http.put(this.coreUrl + 'rest/statuses/' + this.statusIdentifier, this.status)
                .subscribe((data: any) => {
                    this.notify.success(this.lang.statusUpdated);
                    this.router.navigate(['administration/statuses']);
                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    }

}
