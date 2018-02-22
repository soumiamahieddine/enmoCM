import { ChangeDetectorRef, Component, OnInit, ViewChild, Inject, TemplateRef } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { DOCUMENT } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogConfig, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material';

import { AutoCompletePlugin } from '../../plugins/autocomplete.plugin';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: angularGlobals["users-administrationView"],
    styleUrls: ['css/users-administration.component.css'],
    providers: [NotificationService]
})
export class UsersAdministrationComponent extends AutoCompletePlugin implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    dialogRef: MatDialogRef<any>;
    search: string = null;
    coreUrl: string;

    users: any[] = [];
    userDestRedirect: any = {};
    userDestRedirectModels: any[] = [];

    lang: any = LANG;

    loading: boolean = false;

    data: Users[] = [];

    config: any = {};

    displayedColumns = ['user_id', 'lastname', 'firstname', 'status', 'mail', 'actions'];
    dataSource = new MatTableDataSource(this.data);
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) {
        super(http, 'users');
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > " + this.lang.users;
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/administration/users')
            .subscribe((data: any) => {
                this.users = data['users'];
                this.data = this.users;
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.data);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    suspendUser(user: any) {
        if (user.inDiffListDest == 'Y') {
            user.mode = 'up';
            this.userDestRedirect = user;
            this.http.get(this.coreUrl + 'rest/listModels/itemId/' + user.user_id + '/itemMode/dest/objectType/entity_id')
                .subscribe((data: any) => {
                    this.userDestRedirectModels = data.listModels;
                    this.config = { data: { userDestRedirect: this.userDestRedirect, userDestRedirectModels: this.userDestRedirectModels } };
                    this.dialogRef = this.dialog.open(UsersAdministrationRedirectModalComponent, this.config);
                    this.dialogRef.afterClosed().subscribe((result: string) => {
                        console.log(result);
                        if (result) {
                            user.enabled = 'N';
                            user.redirectListModels = result;
                            //first, update listModels
                            this.http.put(this.coreUrl + 'rest/listModels/itemId/' + user.user_id + '/itemMode/dest/objectType/entity_id', user)
                                .subscribe((data: any) => {
                                    if (data.errors) {
                                        user.enabled = 'Y';
                                        this.notify.error(data.errors);
                                    } else {
                                        //then suspend user
                                        this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                                            .subscribe((data: any) => {
                                                user.inDiffListDest = 'N';
                                                this.notify.success(this.lang.userSuspended + ' « ' + user.user_id + ' »');

                                            }, (err) => {
                                                user.enabled = 'Y';
                                                this.notify.error(JSON.parse(err._body).errors);
                                            });
                                    }
                                }, (err) => {
                                    this.notify.error(JSON.parse(err._body).errors);
                                });
                        }
                        this.dialogRef = null;
                    });

                }, (err) => {
                    console.log(err);
                    location.href = "index.php";
                });
        } else {
            let r = confirm(this.lang.confirmAction + ' ' + this.lang.suspend + ' « ' + user.user_id + ' »');

            if (r) {
                user.enabled = 'N';
                this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                    .subscribe((data: any) => {
                        this.notify.success(this.lang.userSuspended + ' « ' + user.user_id + ' »');

                    }, (err) => {
                        user.enabled = 'Y';
                        this.notify.error(JSON.parse(err._body).errors);
                    });
            }
        }
    }

    activateUser(user: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.authorize + ' « ' + user.user_id + ' »');

        if (r) {
            user.enabled = 'Y';
            this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                .subscribe((data: any) => {
                    this.notify.success(this.lang.userAuthorized + ' « ' + user.user_id + ' »');

                }, (err) => {
                    user.enabled = 'N';
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    }

    deleteUser(user: any) {

        if (user.inDiffListDest == 'Y') {
            user.mode = 'del';
            this.userDestRedirect = user;
            this.http.get(this.coreUrl + 'rest/listModels/itemId/' + user.user_id + '/itemMode/dest/objectType/entity_id')
                .subscribe((data: any) => {
                    this.userDestRedirectModels = data.listModels;
                    this.config = { data: { userDestRedirect: this.userDestRedirect, userDestRedirectModels: this.userDestRedirectModels } };
                    this.dialogRef = this.dialog.open(UsersAdministrationRedirectModalComponent, this.config);
                    this.dialogRef.afterClosed().subscribe((result: string) => {
                        if (result) {
                            user.redirectListModels = result;
                            //first, update listModels
                            this.http.put(this.coreUrl + 'rest/listModels/itemId/' + user.user_id + '/itemMode/dest/objectType/entity_id', user)
                                .subscribe((data: any) => {
                                    if (data.errors) {
                                        this.notify.error(data.errors);
                                    } else {
                                        //then delete user
                                        this.http.delete(this.coreUrl + 'rest/users/' + user.id)
                                            .subscribe((data: any) => {
                                                user.inDiffListDest = 'N';
                                                this.data = data.users;
                                                this.dataSource = new MatTableDataSource(this.data);
                                                this.dataSource.paginator = this.paginator;
                                                this.dataSource.sort = this.sort;

                                                this.notify.success(this.lang.userDeleted + ' « ' + user.user_id + ' »');

                                            }, (err) => {
                                                this.notify.error(JSON.parse(err._body).errors);
                                            });
                                    }
                                }, (err) => {
                                    this.notify.error(JSON.parse(err._body).errors);
                                });
                        }
                    });

                }, (err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        } else {
            let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + user.user_id + ' »');

            if (r) {
                this.http.delete(this.coreUrl + 'rest/users/' + user.id, user)
                    .subscribe((data: any) => {
                        this.data = data.users;
                        this.dataSource = new MatTableDataSource(this.data);
                        this.dataSource.paginator = this.paginator;
                        this.dataSource.sort = this.sort;
                        this.notify.success(this.lang.userDeleted + ' « ' + user.user_id + ' »');

                    }, (err) => {
                        this.notify.error(JSON.parse(err._body).errors);
                    });
            }
        }
    }
}
export interface Users {
    user_id: string;
    lastname: string;
    firstname: string;
    status: string;
    mail: string;
}
@Component({
    templateUrl: angularGlobals["users-administration-redirect-modalView"],
})
export class UsersAdministrationRedirectModalComponent extends AutoCompletePlugin {
    lang: any = LANG;

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<UsersAdministrationRedirectModalComponent>) {
        super(http, 'users');
    }
    sendFunction() {
        var valid = true;
        this.data.userDestRedirectModels.each(function (element: any) {
            if (!element.redirectUserId) {
                valid = false;
            }
        });
        return valid;
    }
}