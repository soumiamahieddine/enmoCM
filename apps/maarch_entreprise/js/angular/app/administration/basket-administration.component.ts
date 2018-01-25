import { Component, OnInit} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["basket-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css'],
    providers   : [NotificationService]
})
export class BasketAdministrationComponent implements OnInit {

    coreUrl             : string;
    lang                : any       = LANG;

    id                  : string;
    creationMode        : boolean;

    basket              : any       = {};
    basketGroups        : any[]     = [];
    basketIdAvailable   : boolean;

    loading             : boolean   = false;


    constructor(public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/baskets\"' style='cursor: pointer'>Bannettes</a>";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;
                this.basketIdAvailable = false;
                this.loading = false;
            } else {
                this.creationMode = false;
                this.basketIdAvailable = true;
                this.id = params['id'];
                this.http.get(this.coreUrl + "rest/baskets/" + this.id)
                    .subscribe((data : any) => {
                        this.basket = data.basket;
                        this.basket.id = data.basket.basket_id;
                        this.basket.name = data.basket.basket_name;
                        this.basket.description = data.basket.basket_desc;
                        this.basket.clause = data.basket.basket_clause;
                        this.basket.isSearchBasket = data.basket.is_visible != "Y";
                        this.basket.isFolderBasket = data.basket.is_folder_basket == "Y";
                        this.basket.flagNotif = data.basket.flag_notif == "Y";

                        this.http.get(this.coreUrl + "rest/baskets/" + this.id + "/groups")
                            .subscribe((data : any) => {
                                this.basketGroups = data.groups;

                                this.loading = false;
                            }, () => {
                                location.href = "index.php";
                            });
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    isAvailable() {
        this.http.get(this.coreUrl + "rest/baskets/" + this.basket.id)
            .subscribe(() => {
                this.basketIdAvailable = false;
            }, (err) => {
                this.basketIdAvailable = false;
                if (err.error.errors == "Basket not found") {
                    this.basketIdAvailable = true;
                }
            });
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/baskets", this.basket)
                .subscribe((data : any) => {
                    this.notify.success(this.lang.basketAdded);
                    this.router.navigate(["/administration/baskets"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/baskets/" + this.id, this.basket)
                .subscribe((data : any) => {
                    this.notify.success(this.lang.basketUpdated);
                    this.router.navigate(["/administration/baskets"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

}