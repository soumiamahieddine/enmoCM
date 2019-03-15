import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { MatSidenav } from '@angular/material';
import { NotificationService } from '../../notification.service';
import { HeaderService } from '../../../service/header.service';

declare function $j(selector: any): any;


@Component({
    templateUrl: "shipping-administration.component.html",
    styleUrls: ['shipping-administration.component.scss'],
    providers: [NotificationService]
})
export class ShippingAdministrationComponent implements OnInit {

    @ViewChild('snav') public sidenavLeft: MatSidenav;
    @ViewChild('snav2') public sidenavRight: MatSidenav;

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;

    lang: any = LANG;
    loading: boolean = false;

    shipping: any = {
        label : '',
        description : '',
        shapingOptions : ['color', 'both_sides', 'address_page'],
        envelopMode : 'small_simple',
        sendMode : 'fast',
        first_page_price : 0,
        next_page_price : 0,
        postage_price : 0,
    };
    shippingClone: any = null;

    shapingOptions: string[] = [
        'color',
        'both_sides',
        'address_page',
    ];

    envelopModes: string[] = [
        'small_simple',
        'small_double',
        'big_a4',
    ];

    sendModes: string[] = [
        'fast',
        'economic',
        'registered_mail',
        'registered_mail_ar'
    ];

    

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, private headerService: HeaderService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.shippingAdmin);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = false;

        /*this.http.get('../../rest/administration/shipping')
            .subscribe((data: any) => {
                this.loading = false;
            }, (err) => {
                this.notify.handleErrors(err);
            });*/

            
    }

    onSubmit() {
        /*this.http.put('../../rest/configurations/admin_email_server', this.shipping)
            .subscribe((data: any) => {
                this.shippingClone = JSON.parse(JSON.stringify(this.shipping));
                this.notify.success(this.lang.configurationUpdated);
            }, (err) => {
                this.notify.handleErrors(err);
            });*/
    }

    checkModif() {
        return (JSON.stringify(this.shippingClone) === JSON.stringify(this.shipping));
    }
}
