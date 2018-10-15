import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatSidenav } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "contacts-filling-administration.component.html",
    providers: [NotificationService]
})
export class ContactsFillingAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader: string;
    @ViewChild('snav') public sidenavLeft: MatSidenav;
    @ViewChild('snav2') public sidenavRight: MatSidenav;

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    lang: any = LANG;
    coreUrl: string;

    contactsFilling: any = {
        'rating_columns': [],
        'enable': false,
        'first_threshold': '33',
        'second_threshold': '66',
    };

    arrRatingColumns: String[] = [];
    fillingColor = {
        'first_threshold': '#f87474',
        'second_threshold': '#f6cd81',
        'third_threshold': '#ccffcc',
    };
    fillingColumns = [
        'title',
        'address_postal_code',
        'email',
        'function',
        'salutation_header',
        'salutation_footer',
        'other_data',
        'contact_other_data',
        'occupancy',
        'lastname',
        'address_num',
        'address_country',
        'firstname',
        'department',
        'society_short',
        'website',
        'society',
        'phone',
        'address_complement',
        'address_town',
        'address_street',   
    ];
    fillingColumnsState = [
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false,
    ];
    fillingColumnsSelected = ['society'];

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

        window['MainHeaderComponent'].refreshTitle(this.lang.contactsFillingAdministration);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.http.get(this.coreUrl + 'rest/contactsFilling')
            .subscribe((data: any) => {
                this.contactsFilling = data.contactsFilling;
                if (this.contactsFilling.rating_columns.length > 0) {
                    this.contactsFilling.rating_columns.forEach((col: any) => {
                        let i = this.fillingColumns.indexOf(col);
                        this.fillingColumnsState[i] = true;    
                        this.arrRatingColumns.push(col);
                    });
                }  
                this.loading = false;
            });
    }

    addCriteria(event: any, criteria: String) {
        if (event.checked) {
            this.arrRatingColumns.push(criteria);
        } else {
            this.arrRatingColumns.splice(this.arrRatingColumns.indexOf(criteria), 1);
        }
        this.contactsFilling.rating_columns = this.arrRatingColumns;
        this.contactsFilling.rating_columns.length == 0 ? this.contactsFilling.enable = false : this.contactsFilling.enable = true;
        this.onSubmit();
    }

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/contactsFilling', this.contactsFilling)
            .subscribe(() => {
                this.notify.success(this.lang.contactsFillingUpdated);

            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    toggleFillingContact() {
        this.contactsFilling.enable == true ? this.contactsFilling.enable = false : this.contactsFilling.enable = true;
        this.onSubmit();
    }
}
