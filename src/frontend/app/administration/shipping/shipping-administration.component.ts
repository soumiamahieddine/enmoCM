import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { MatSidenav } from '@angular/material';
import { NotificationService } from '../../notification.service';
import { HeaderService } from '../../../service/header.service';
import { ActivatedRoute } from '@angular/router';

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
    creationMode: boolean = true;

    shipping: any = {
        label: '',
        description: '',
        options: {
            shapingOptions: ['color', 'both_sides', 'address_page'],
            envelopMode: 'small_simple',
            sendMode: 'fast',
        },
        fee: {
            first_page_price: 0,
            next_page_price: 0,
            postage_price: 0,
        },
        account : {
            id: '',
            password: ''
        }
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



    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private route: ActivatedRoute, private notify: NotificationService, private headerService: HeaderService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {

        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);



        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.headerService.setHeader(this.lang.shippingCreation);

                this.creationMode = true;
                this.shippingClone = JSON.parse(JSON.stringify(this.shipping));

                this.loading = false;

            } else {
                this.headerService.setHeader(this.lang.shippingModification);
                this.creationMode = false;

                //FOR EXEMPLE
                this.shipping = {
                    label: 'Envoi vers maileva',
                    description: 'Envoi vers maileva',
                    options: {
                        shapingOptions: ['color', 'address_page'],
                        envelopMode: 'small_double',
                        sendMode: 'economic',
                    },
                    fee: {
                        first_page_price: 0.10,
                        next_page_price: 0.30,
                        postage_price: 3,
                    },
                    account : {
                        id: 'maileva',
                        password: ''
                    }
                };
                this.shippingClone = JSON.parse(JSON.stringify(this.shipping));
                this.loading = false;
                //

                /*this.http.get('../../rest/administration/shipping/'+params['id'])
                    .subscribe((data: any) => {
                        this.shipping = data.shipping
                        this.shippingClone = JSON.parse(JSON.stringify(this.shipping));
                        this.loading = false;
                    }, (err) => {
                        this.notify.handleErrors(err);
                    });*/

            }
        });


    }

    onSubmit() {
        /*this.http.put('../../rest/administration/shipping', this.shipping)
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

    toggleShapingOption(option: string) {
        const index = this.shipping.options.shapingOptions.indexOf(option);
        if (index > -1) {
            this.shipping.options.shapingOptions.splice(index, 1);
        } else {
            this.shipping.options.shapingOptions.push(option);
        }
    }

    cancelModification() {
        this.shipping = JSON.parse(JSON.stringify(this.shippingClone));
    }
}
