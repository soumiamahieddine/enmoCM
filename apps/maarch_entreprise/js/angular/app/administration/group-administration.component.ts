import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare const angularGlobals : any;


@Component({
    templateUrl : angularGlobals["group-administrationView"],
    providers   : [NotificationService]
})
export class GroupAdministrationComponent implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;

    creationMode                : boolean;

    group                       : any       = {
        security                : {}
    };
    loading                     : boolean   = false;

    constructor(public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/groups\"' style='cursor: pointer'>" + this.lang.groups + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.groupCreation;
        } else {
            breadCrumb += this.lang.groupModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;
                this.loading = false;
                this.updateBreadcrumb(angularGlobals.applicationName);
            } else {
                this.creationMode = false;
                this.http.get(this.coreUrl + "rest/groups/" + params['id'] + "/details")
                    .subscribe((data : any) => {
                        this.updateBreadcrumb(angularGlobals.applicationName);
                        this.group = data['group'];
                        this.loading = false;

                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/groups", this.group)
                .subscribe((data : any) => {
                    this.notify.success(this.lang.groupAdded);
                    this.router.navigate(["/administration/groups/" + data.group.id]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/groups/" + this.group['id'] , {"description" : this.group['group_desc'], "security" : this.group['security']})
                .subscribe((data : any) => {
                    this.notify.success(this.lang.groupUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }

    }

    updateService(service: any) {
        this.http.put(this.coreUrl + "rest/groups/" + this.group['id'] + "/services/" + service['id'], service)
            .subscribe((data : any) => {
                this.notify.success(this.lang.groupUpdated);
            }, (err) => {
                service.checked = !service.checked;
                this.notify.error(err.error.errors);
            });
    }
}
