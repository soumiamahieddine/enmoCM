import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import { ActivatedRoute } from '@angular/router';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["user-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/user-administration.component.css']
})
export class UserAdministrationComponent implements OnInit {

    coreUrl                     : string;
    userId                      : string;

    user                        : any[]     = [];

    resultInfo                  : string    = "";
    loading                     : boolean   = false;


    constructor(public http: Http, private route: ActivatedRoute) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/users\"' style='cursor: pointer'>Utilisateurs</a> > Modification";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe(params => {
            this.userId = params['userId'];

            this.http.get(this.coreUrl + 'rest/administration/users/' + this.userId)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user = data;

                    this.loading = false;
                }, () => {
                    location.href = "index.php";
                });
        });
    }

    resetPassword() {
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/password", {})
            .map(res => res.json())
            .subscribe((data) => {
                this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            }, (err) => {
                this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            });
    }

    updateGroup(group: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/groups/" + group.group_id, group)
            .map(res => res.json())
            .subscribe((data) => {
                this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            }, (err) => {
                this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            });
    }

    deleteGroup(group: any) {
        let r = confirm('Voulez-vous vraiment retirer l\'utilisateur de ce groupe ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.userId + "/groups/" + group.group_id)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user['groups'] = data.groups;
                    this.resultInfo = data.success;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                }, (err) => {
                    this.resultInfo = JSON.parse(err._body).errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                });
        }
    }

    updateEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/entities/" + entity.entity_id, entity)
            .map(res => res.json())
            .subscribe((data) => {
                this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            }, (err) => {
                this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            });
    }

    updatePrimaryEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/entities/" + entity.entity_id + "/primaryEntity", {})
            .map(res => res.json())
            .subscribe((data) => {
                this.user['entities'] = data.entities;
                this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            }, (err) => {
                this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });

            });
    }

    deleteEntity(entity: any) {
        let r = confirm('Voulez-vous vraiment retirer l\'utilisateur de cette entité ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.userId + "/entities/" + entity.entity_id)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user['entities'] = data.entities;
                    this.resultInfo = data.success;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                }, (err) => {
                    this.resultInfo = JSON.parse(err._body).errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                });
        }
    }


    onSubmit() {
        this.http.put(this.coreUrl + 'rest/users/' + this.userId, this.user)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });

                } else {
                    this.resultInfo = data.success;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                }
            }, (error) => {
                alert(error.statusText);
            });
    }
}
