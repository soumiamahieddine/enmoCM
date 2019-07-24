import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService }        from '../../../service/header.service';
import { MatPaginator, MatTableDataSource, MatSort, MatSidenav} from '@angular/material';
import { AppService } from '../../../service/app.service';

declare function $j(selector: any) : any;

@Component({
    templateUrl: "group-administration.component.html",
    providers   : [NotificationService, AppService]
})
export class GroupAdministrationComponent implements OnInit {
    /*HEADER*/
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    lang                            : any       = LANG;
    loading                         : boolean   = false;

    group                           : any       = {
        security                    : {}
    };
    creationMode                    : boolean;

    usersDisplayedColumns           = ['firstname', 'lastname'];
    basketsDisplayedColumns         = ['basket_name', 'basket_desc'];
    usersDataSource                 : any;
    basketsDataSource               : any;


    @ViewChild('paginatorBaskets') paginatorBaskets: MatPaginator;
    @ViewChild('sortBaskets') sortBaskets: MatSort;
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild('sortUsers') sortUsers: MatSort;

    applyFilter(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.usersDataSource.filter = filterValue;
    }
    applyBasketsFilter(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.basketsDataSource.filter = filterValue;
    }

    constructor(
        public http: HttpClient, 
        private route: ActivatedRoute, 
        private router: Router, 
        private notify: NotificationService, 
        private headerService: HeaderService,
        public appService: AppService
    ) {
        $j("link[href='merged_css.php']").remove();
    }

    ngOnInit(): void {
        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.headerService.setHeader(this.lang.groupCreation);

                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = true;
                this.loading = false;
            } else {                
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = false;
                this.http.get("../../rest/groups/" + params['id'] + "/details")
                    .subscribe((data : any) => {
                        this.group = data['group'];
                        this.headerService.setHeader(this.lang.groupModification, this.group['group_desc']);
                        this.loading = false;
                        setTimeout(() => {
                            this.usersDataSource = new MatTableDataSource(this.group.users);
                            this.usersDataSource.paginator = this.paginator;
                            this.usersDataSource.sort = this.sortUsers;
                            this.basketsDataSource = new MatTableDataSource(this.group.baskets);
                            this.basketsDataSource.paginator = this.paginatorBaskets;
                            this.basketsDataSource.sort = this.sortBaskets;
                        }, 0);

                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post("../../rest/groups", this.group)
                .subscribe((data : any) => {
                    this.notify.success(this.lang.groupAdded);
                    this.router.navigate(["/administration/groups/" + data.group]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put("../../rest/groups/" + this.group['id'] , {"description" : this.group['group_desc'], "security" : this.group['security']})
                .subscribe(() => {
                    this.notify.success(this.lang.groupUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    updateService(service: any) {
        this.http.put("../../rest/groups/" + this.group['id'] + "/services/" + service['id'], service)
            .subscribe(() => {
                this.notify.success(this.lang.groupServicesUpdated);
            }, (err) => {
                service.checked = !service.checked;
                this.notify.error(err.error.errors);
            });
    }

    linkUser(newUser:any) {
        var groupReq = {
            "groupId"   : this.group.group_id,
            "role"      : this.group.role
        };
        this.http.post("../../rest/users/" + newUser.serialId + "/groups", groupReq)
            .subscribe(() => {
                var displayName = newUser.idToDisplay.split(" ");
                var user = {
                    id : newUser.id,
                    user_id : newUser.otherInfo,
                    firstname : displayName[0],
                    lastname : displayName[1]
                };
                this.group.users.push(user);
                this.usersDataSource = new MatTableDataSource(this.group.users);
                this.usersDataSource.paginator = this.paginator;
                this.usersDataSource.sort = this.sortUsers;
                this.notify.success(this.lang.userAdded);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
