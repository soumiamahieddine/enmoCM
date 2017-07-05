import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["users-administrationView"],
    styleUrls   : ['css/users-administration.component.css','../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class UsersAdministrationComponent implements OnInit {

    coreUrl                     : string;

    users                       : any[]     = [];
    userDestRedirect            : any       = {};
    userDestRedirectModels      : any[]     = [];

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
                        },
                        "order": [[ 1, "asc" ]],
                        "columnDefs": [
                            { "orderable": false, "targets": [3,5] }
                        ]
                    });
                    $j('.dataTables_filter input').attr("placeholder", this.lang.search);
                    $j('dataTables_filter input').addClass('form-control');
                    $j(".datatablesLeft").css({"float":"left"});
                    $j(".datatablesCenter").css({"text-align":"center"});
                    $j(".datatablesRight").css({"float":"right"});      

                }, 0);

                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    suspendUser(user: any) {
        if(user.inDiffListDest == 'Y') {
            user.mode = 'up';
            this.userDestRedirect = user;
            this.http.get(this.coreUrl + 'rest/listModels/itemId/'+user.user_id+'/itemMode/dest/objectType/entity_id')
                .map(res => res.json())
                .subscribe((data) => {
                    this.userDestRedirectModels = data.listModels;
                    setTimeout(() => {
                        $j(".redirectDest").typeahead({
                            order: "asc",
                            display: "formattedUser",
                            templateValue: "{{user_id}}",
                            source: {
                                ajax: {
                                    type: "GET",
                                    dataType: "json",
                                    url: this.coreUrl + "rest/users/autocompleter/exclude/"+user.user_id,
                                }
                            }
                        });
                    }, 0);
                }, (err) => {
                    console.log(err);
                    location.href = "index.php";
                });
        } else {
            let r = confirm(this.lang.suspendMsg + " ?");

            if (r) {
                user.enabled = 'N';
                this.http.put(this.coreUrl + 'rest/users/' + user.user_id, user)
                    .map(res => res.json())
                    .subscribe((data) => {
                        successNotification(data.success);
                    }, (err) => {
                        user.enabled = 'Y';
                        errorNotification(JSON.parse(err._body).errors);
                    });
            }
        }
    }

    suspendUserModal(user: any) {
        let r = confirm(this.lang.suspendMsg + " ?");

        if (r) {
            user.enabled = 'N';
            user.redirectListModels = this.userDestRedirectModels;
            //first, update listModels
            this.http.put(this.coreUrl + 'rest/listModels/itemId/'+user.user_id+'/itemMode/dest/objectType/entity_id', user)
                .map(res => res.json())
                .subscribe((data) => {
                    if (data.errors) {
                        user.enabled = 'Y';
                        errorNotification(data.errors);
                    } else {
                        //then suspend user
                        this.http.put(this.coreUrl + 'rest/users/' + user.user_id, user)
                            .map(res => res.json())
                            .subscribe((data) => {
                                user.inDiffListDest = 'N';
                                $j('#changeDiffListDest').modal('hide');
                                successNotification(data.success);
                            }, (err) => {
                                user.enabled = 'Y';
                                errorNotification(JSON.parse(err._body).errors);
                            });
                    }
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        }
    
    }

    activateUser(user: any) {
        let r = confirm(this.lang.authorizeMsg + " ?");

        if (r) {
            user.enabled = 'Y';
            this.http.put(this.coreUrl + 'rest/users/' + user.user_id, user)
                .map(res => res.json())
                .subscribe((data) => {
                    successNotification(data.success);
                }, (err) => {
                    user.enabled = 'N';
                    errorNotification(JSON.parse(err._body).errors);
                });
        }
    }

    deleteUser(user: any) {
        if(user.inDiffListDest == 'Y') {
            user.mode = 'del';
            this.userDestRedirect = user;
            this.http.get(this.coreUrl + 'rest/listModels/itemId/'+user.user_id+'/itemMode/dest/objectType/entity_id')
            .map(res => res.json())
            .subscribe((data) => {
                this.userDestRedirectModels = data.listModels;

                setTimeout(() => {
                    $j(".redirectDest").typeahead({
                        order: "asc",
                        source: {
                            ajax: {
                                type: "GET",
                                dataType: "json",
                                url: this.coreUrl + "rest/users/autocompleter/exclude/"+user.user_id,
                            }
                        }
                    });
                }); 
                
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });
        } else {
            let r = confirm(this.lang.deleteMsg + " ?");

            if (r) {
                this.http.delete(this.coreUrl + 'rest/users/' + user.user_id, user)
                    .map(res => res.json())
                    .subscribe((data) => {
                        for (var i = 0;i<this.users.length;i++) {
                            if(this.users[i].user_id == user.user_id){
                                this.users.splice(i,1);
                            }
                        }
                        this.table.row($j("#"+user.user_id)).remove().draw();
                        successNotification(data.success);
                    }, (err) => {
                        errorNotification(JSON.parse(err._body).errors);
                    });
            }
        }
    }

    deleteUserModal(user: any) {

        let r = confirm(this.lang.deleteMsg + " ?");

        if (r) {
            user.redirectListModels = this.userDestRedirectModels;
            //first, update listModels
            this.http.put(this.coreUrl + 'rest/listModels/itemId/'+user.user_id+'/itemMode/dest/objectType/entity_id', user)
                .map(res => res.json())
                .subscribe((data) => {
                    if (data.errors) {
                        errorNotification(data.errors);
                    } else {
                        //then delete user
                        this.http.delete(this.coreUrl + 'rest/users/' + user.user_id)
                            .map(res => res.json())
                            .subscribe((data) => {
                                user.inDiffListDest = 'N';
                                $j('#changeDiffListDest').modal('hide');
                                for (var i = 0;i<this.users.length;i++) {
                                    if(this.users[i].user_id == user.user_id){
                                        this.users.splice(i,1);
                                    }
                                }
                                this.table.row($j("#"+user.user_id)).remove().draw();
                                successNotification(data.success);
                            }, (err) => {
                                errorNotification(JSON.parse(err._body).errors);
                            });
                    }
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        }
    }

}
