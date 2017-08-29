import { Component, OnInit, Pipe, PipeTransform} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;

@Pipe({ name: 'dataPipe' })
export class DataTablePipe implements PipeTransform {
  transform(array: any[], field: string, query: string): any {
    if (query) {
      query = query.toLowerCase();
      return array.filter((value: any) =>
        value[field].toLowerCase().indexOf(query) > -1);
    }
    return array;
  }
}

@Component({
    templateUrl : angularGlobals["users-administrationView"],
    styleUrls   : ['css/users-administration.component.css'],
    providers   : [NotificationService]
})

export class UsersAdministrationComponent implements OnInit {
    search                      : string    = null;
    
    coreUrl                     : string;

    users                       : any[]     = [];
    userDestRedirect            : any       = {};
    userDestRedirectModels      : any[]     = [];

    lang                        : any       = LANG;

    loading                     : boolean   = false;

    data                        : any       = [];
    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>"+this.lang.administration+"</a> > "+this.lang.users;
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/administration/users')
            .subscribe((data : any) => {
                this.users = data['users'];
                this.data = this.users;
                this.loading = false;
                setTimeout(() => {
                    $j("[md2sortby='user_id']").click();
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    suspendUser(user: any) {
        if(user.inDiffListDest == 'Y') {
            user.mode = 'up';
            this.userDestRedirect = user;
            this.http.get(this.coreUrl + 'rest/listModels/itemId/'+user.user_id+'/itemMode/dest/objectType/entity_id')
                .subscribe((data : any) => {
                    this.userDestRedirectModels = data.listModels;
                }, (err) => {
                    console.log(err);
                    location.href = "index.php";
                });
        } else {
            let r = confirm(this.lang.confirmAction+' '+this.lang.suspend+' « '+user.user_id+' »');

            if (r) {
                user.enabled = 'N';
                this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                    .subscribe((data : any) => {
                        this.notify.success(this.lang.userSuspended+' « '+user.user_id+' »');
                        
                    }, (err) => {
                        user.enabled = 'Y';
                        this.notify.error(JSON.parse(err._body).errors);
                    });
            }
        }
    }

    suspendUserModal(user: any) {
        let r = confirm(this.lang.confirmAction+' '+this.lang.suspend+' « '+user.user_id+' »');

        if (r) {
            user.enabled = 'N';
            user.redirectListModels = this.userDestRedirectModels;
            //first, update listModels
            this.http.put(this.coreUrl + 'rest/listModels/itemId/'+user.user_id+'/itemMode/dest/objectType/entity_id', user)
                .subscribe((data : any) => {
                    if (data.errors) {
                        user.enabled = 'Y';
                        this.notify.error(data.errors);
                    } else {
                        //then suspend user
                        this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                            .subscribe((data : any) => {
                                user.inDiffListDest = 'N';
                                $j('#changeDiffListDest').modal('hide');
                                this.notify.success(this.lang.userSuspended+' « '+user.user_id+' »');
                                
                            }, (err) => {
                                user.enabled = 'Y';
                                this.notify.error(JSON.parse(err._body).errors);
                            });
                    }
                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    
    }

    activateUser(user: any) {
        let r = confirm(this.lang.confirmAction+' '+this.lang.authorize+' « '+user.user_id+' »');

        if (r) {
            user.enabled = 'Y';
            this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                .subscribe((data : any) => {
                    this.notify.success(this.lang.userAuthorized+' « '+user.user_id+' »');
                    
                }, (err) => {
                    user.enabled = 'N';
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    }

    deleteUser(user: any) {

        if(user.inDiffListDest == 'Y') {
            user.mode = 'del';
            this.userDestRedirect = user;
            this.http.get(this.coreUrl + 'rest/listModels/itemId/'+user.user_id+'/itemMode/dest/objectType/entity_id')
                .subscribe((data : any) => {
                    this.userDestRedirectModels = data.listModels;

                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        } else {            
            let r = confirm(this.lang.confirmAction+' '+this.lang.delete+' « '+user.user_id+' »');

            if (r) {
                this.http.delete(this.coreUrl + 'rest/users/' + user.id, user)
                    .subscribe((data : any) => {
                        this.data = data.users;
                        this.notify.success(this.lang.userDeleted+' « '+user.user_id+' »');
                        
                    }, (err) => {
                        this.notify.error(JSON.parse(err._body).errors);
                    });
            }
        }
    }

    deleteUserModal(user: any) {
        let r = confirm(this.lang.confirmAction+' '+this.lang.delete+' « '+user.user_id+' »');

        if (r) {
            user.redirectListModels = this.userDestRedirectModels;
            //first, update listModels
            this.http.put(this.coreUrl + 'rest/listModels/itemId/'+user.user_id+'/itemMode/dest/objectType/entity_id', user)
                .subscribe((data : any) => {
                    if (data.errors) {
                        this.notify.error(data.errors);
                    } else {
                        //then delete user
                        this.http.delete(this.coreUrl + 'rest/users/' + user.id)
                            .subscribe((data : any) => {
                                user.inDiffListDest = 'N';
                                this.data = data.users;
                                $j('#changeDiffListDest').modal('hide');
                                this.notify.success(this.lang.userDeleted+' « '+user.user_id+' »');
                                                                
                            }, (err) => {
                                this.notify.error(JSON.parse(err._body).errors);
                            });
                    }
                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    }

}
