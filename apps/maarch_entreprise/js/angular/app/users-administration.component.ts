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
    table                       : any

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
                    this.table = $j('#usersTable').DataTable({
                        "dom": '<"datatablesLeft"p><"datatablesRight"f><"datatablesCenter"l>rt<"datatablesCenter"i><"clear">',
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        "oLanguage": {
                            "sLengthMenu": "<i class='fa fa-bars'></i> _MENU_",
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
            }, (err) => {
                location.href = "index.php";
            });
    }

    suspendUser(user: any) {
        let r = confirm(this.lang.suspendMsg+' ?');

        if (r) {
            user.enabled = 'N';
            this.http.put(this.coreUrl + 'rest/user/' + user.user_id, user)
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
                });
        }
    }

    activateUser(user: any) {
        let r = confirm(this.lang.authorizeMsg+' ?');

        if (r) {
            user.enabled = 'Y';
            this.http.put(this.coreUrl + 'rest/user/' + user.user_id, user)
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
                });
        }
    }

    deleteUser(id: string) {
        let r = confirm(this.lang.deleteMsg+' ?');

        if (r) {
            this.http.delete(this.coreUrl + 'rest/user/' + id)
                .map(res => res.json())
                .subscribe((data) => {
                    if (data.errors) {
                        this.resultInfo = data.errors;
                        $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                    } else {
                        for(var i = 0;i<this.users.length;i++){
                            if(this.users[i].user_id == id){
                                this.users.splice(i,1);
                            }
                        }
                        this.table.row($j("#"+id)).remove().draw();
                        this.resultInfo = data.success;
                        $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                    }
                });
        }
    }
}
