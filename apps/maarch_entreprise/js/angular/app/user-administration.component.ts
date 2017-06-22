import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import { ActivatedRoute } from '@angular/router';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["user-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
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
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Utilisateurs";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe(params => {
            this.userId = params['userId'];

            this.http.get(this.coreUrl + 'rest/administration/user/' + this.userId)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user = data;

                    this.loading = false;
                }, () => {
                    location.href = "index.php";
                });
        });
    }

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/user/' + this.userId, this.user)
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
