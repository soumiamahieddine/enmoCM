import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["users-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class UsersAdministrationComponent implements OnInit {

    coreUrl                     : string;

    users                       : any[]     = [];

    resultInfo                  : string    = "";
    loading                     : boolean   = false;


    constructor(public http: Http) {
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

        this.http.get(this.coreUrl + 'rest/administration/users')
            .map(res => res.json())
            .subscribe((data) => {
                this.users = data;

                setTimeout(() => {
                    $j('#usersTable').DataTable({
                        //"language": {
                        //    "lengthMenu":   tempLang.display + " _MENU_ " + tempLang.recordsPerPage,
                        //    "zeroRecords": tempLang.noRecords,
                        //    "info": tempLang.page + " _PAGE_ "+ tempLang.outOf +" _PAGES_",
                        //    "infoEmpty": tempLang.noRecords + " " + tempLang.available,
                        //    "infoFiltered": "(" + tempLang.filteredFrom + " _MAX_ " + tempLang.records + ")",
                        //    "search" : tempLang.search,
                        //    "paginate" : {
                        //        "first" : tempLang.first,
                        //        "last"  : tempLang.last,
                        //        "next"  : tempLang.next,
                        //        "previous" : tempLang.previous
                        //    }
                        //}
                    });
                }, 0);

                this.loading = false;
            });
    }
}
