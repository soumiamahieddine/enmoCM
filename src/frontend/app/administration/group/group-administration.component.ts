import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService }        from '../../../service/header.service';
import { MatPaginator } from '@angular/material/paginator';
import { MatSidenav } from '@angular/material/sidenav';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { AppService } from '../../../service/app.service';
import { PrivilegeService } from '../../../service/privileges.service';
import { tap, catchError } from 'rxjs/operators';
import { of } from 'rxjs';

declare function $j(selector: any) : any;

@Component({
    templateUrl: "group-administration.component.html",
    styleUrls: ['group-administration.component.scss'],
    providers   : [NotificationService, AppService, PrivilegeService]
})
export class GroupAdministrationComponent implements OnInit {
    /*HEADER*/
    @ViewChild('snav', { static: true }) public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight  : MatSidenav;

    lang                            : any       = LANG;
    loading                         : boolean   = false;

    group                           : any       = {
        security                    : {}
    };
    creationMode                    : boolean;
    menus: any = {};

    usersDisplayedColumns           = ['firstname', 'lastname'];
    basketsDisplayedColumns         = ['basket_name', 'basket_desc'];
    usersDataSource                 : any;
    basketsDataSource               : any;


    @ViewChild('paginatorBaskets', { static: false }) paginatorBaskets: MatPaginator;
    @ViewChild('sortBaskets', { static: true }) sortBaskets: MatSort;
    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild('sortUsers', { static: true }) sortUsers: MatSort;

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
        public appService: AppService,
        private privilegeService: PrivilegeService
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

                        const toto = this.privilegeService.getMenus().map(elem => elem.unit).filter((elem, pos, arr) => arr.indexOf(elem) === pos);

                        console.log(toto);

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

    toggleService(ev: any, service: any) {
        console.log(ev);

        // TO DO : WAIT BACK
        /*if (ev.checked) {
            this.addService(service);
        } else {
            this.removeService(service);
        }*/
        
    }

    addService(service: any) {
        this.http.post(`../../rest/groups/${this.group.id}/services/${service.id}`, {}).pipe(
            tap(() => {
                this.group.privileges.push(service.id);
                this.notify.success(this.lang.groupServicesUpdated);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    removeService(service: any) {
        this.http.delete(`../../rest/groups/${this.group.id}/services/${service.id}`).pipe(
            tap(() => {
                this.group.privileges.splice(this.group.privileges.indexOf(service.id), 1);
                this.notify.success(this.lang.groupServicesUpdated);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
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
