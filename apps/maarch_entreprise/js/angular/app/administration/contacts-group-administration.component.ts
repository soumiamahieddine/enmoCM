import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "../../../../Views/contacts-group-administration.component.html",
    providers: [NotificationService]
})
export class ContactsGroupAdministrationComponent implements OnInit {

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    lang: any = LANG;
    coreUrl: string;
    creationMode: boolean;
    contactsGroup: any = {};

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
            if (typeof params['id'] != "undefined") {
                this.creationMode = false;

                this.http.get(this.coreUrl + 'rest/contactsGroups/' + params['id'])
                    .subscribe((data: any) => {
                        this.contactsGroup = data.contactsGroup;

                        this.loading = false;
                    });
            } else {
                this.creationMode = true;
                this.loading = false;
            }
        });
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + 'rest/contactsGroups', this.contactsGroup)
                .subscribe(() => {
                    this.router.navigate(['/administration/contacts-groups']);
                    this.notify.success(this.lang.contactsGroupAdded);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + 'rest/contactsGroups/' + this.contactsGroup.id, this.contactsGroup)
                .subscribe(() => {
                    this.router.navigate(['/administration/contacts-groups']);
                    this.notify.success(this.lang.contactsGroupUpdated);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}