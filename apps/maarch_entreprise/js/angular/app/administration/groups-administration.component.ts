import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["groups-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css'],
    providers   : [NotificationService]
})
export class GroupsAdministrationComponent implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;

    groups                      : any[]     = [];
    groupsForAssign             : any[]     = [];

    loading                     : boolean   = false;


    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Groupes";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/groups")
            .subscribe((data : any) => {
                this.groups = data['groups'];

                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    preDelete(group: any) {
        let r = confirm("Etes vous sûr de vouloir supprimer ce groupe ?");

        if (r) {
            if (group.users.length == 0) {
                this.deleteGroup(group);
            } else {
                this.groupsForAssign.push("Aucun remplacement");
                this.groups.forEach((tmpGroup) => {
                    if (group.group_id != tmpGroup.group_id) {
                        this.groupsForAssign.push(tmpGroup.group_id);
                    }
                });
            }
        }
    }

    reassignUsers(group: any, groupId: string) {
        this.groupsForAssign = [];
        if (groupId == "Aucun remplacement") {
            this.deleteGroup(group);
        } else {
            this.http.get(this.coreUrl + "rest/groups/" + group['id'] + "/reassign/" + groupId)
                .subscribe((data : any) => {
                    this.deleteGroup(group);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    deleteGroup(group: any) {
        this.http.delete(this.coreUrl + "rest/groups/" + group['id'])
            .subscribe((data : any) => {
                this.notify.success(this.lang.groupDeleted);
                this.groups = data['groups'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
