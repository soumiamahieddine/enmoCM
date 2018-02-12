import { Component, OnInit} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["entity-administrationView"],
    providers   : [NotificationService]
})
export class EntityAdministrationComponent implements OnInit {

    coreUrl             : string;
    lang                : any       = LANG;

    id                  : string;
    creationMode        : boolean;

    entity              : any       = {};
    entityIdAvailable   : boolean;

    loading             : boolean   = false;


    constructor(public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/entities\"' style='cursor: pointer'>Entités</a>";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;
                this.entityIdAvailable = false;
                this.loading = false;
            } else {
                this.creationMode = false;
                this.entityIdAvailable = true;
                this.id = params['id'];
                this.http.get(this.coreUrl + "rest/entities/" + this.id + "/details")
                    .subscribe((data : any) => {
                        this.entity = data.basket;

                        this.loading = false;
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    isAvailable() {
        if (this.entity.id) {
            this.http.get(this.coreUrl + "rest/entities/" + this.entity.id)
                .subscribe(() => {
                    this.entityIdAvailable = false;
                }, (err) => {
                    this.entityIdAvailable = false;
                    if (err.error.errors == "Entity not found") {
                        this.entityIdAvailable = true;
                    }
                });
        } else {
            this.entityIdAvailable = false;
        }
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/entities", this.entity)
                .subscribe((data : any) => {
                    this.notify.success(this.lang.entityAdded);
                    this.router.navigate(["/administration/entities"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/entities/" + this.id, this.entity)
                .subscribe((data : any) => {
                    this.notify.success(this.lang.entityUpdated);
                    this.router.navigate(["/administration/entities"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

}