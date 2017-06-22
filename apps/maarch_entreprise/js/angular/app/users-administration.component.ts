import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["users-administrationView"],
    styleUrls   : ['css/users-administration.component.css','../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class UsersAdministrationComponent implements OnInit {

    coreUrl                     : string;

    users                       : any[]     = [];
    lang                        : any       = {};
    

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
                this.users = data.users;
                this.lang = data.lang;

                setTimeout(() => {
                    $j('#usersTable').DataTable({
                        "dom": '<"datatablesLeft"p><"datatablesRight"f>rt<"datatablesCenter"i><"clear">',
                        "oLanguage": {
                            "sLengthMenu": "Display _MENU_ records per page",
                            "sZeroRecords": this.lang.noResult,
                            "sInfo": "_START_ - _END_ / _TOTAL_ "+this.lang.record,
                            "sSearch": "",
                            "oPaginate": {
                                "sFirst":    "<<",
                                "sLast":    ">>",
                                "sNext":    this.lang.next+" <i class='fa fa-caret-right'></i>",
                                "sPrevious": "<i class='fa fa-caret-left'></i> "+this.lang.previous
                            },
                            "sInfoEmpty": this.lang.noRecord,
                            "sInfoFiltered": "(filtré de _MAX_ "+this.lang.record+")"
                        }
                    });
                    $j('.dataTables_filter input').attr("placeholder", this.lang.search);
                    $j('dataTables_filter input').addClass('form-control');
                    $j(".datatablesLeft").css({"float":"left"});
                    $j(".datatablesCenter").css({"text-align":"center"});
                    $j(".datatablesRight").css({"float":"right"});                    
                }, 0);

                this.loading = false;
            });
    }
}
