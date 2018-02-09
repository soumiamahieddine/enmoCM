import { Component, OnInit, ViewChild, Inject, TemplateRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogConfig, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["groups-administrationView"],
    providers   : [NotificationService]
})
export class GroupsAdministrationComponent implements OnInit {
    dialogRef: MatDialogRef<any>;
    config: any = {};
    coreUrl                     : string;
    lang                        : any       = LANG;

    groups                      : any[]     = [];
    groupsForAssign             : any[]     = [];

    loading                     : boolean   = false;

    displayedColumns = ['group_id','group_desc','actions'];
    dataSource = new MatTableDataSource(this.groups);
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    constructor(public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Groupes";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/groups")
            .subscribe((data : any) => {
                this.groups = data['groups'];
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.groups);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    preDelete(group: any) {

            if (group.users.length == 0) {
                let r = confirm("Etes vous sûr de vouloir supprimer ce groupe ?");

                if (r) {
                    this.deleteGroup(group);
                } 
            } else {
                this.groupsForAssign = [];
                this.groups.forEach((tmpGroup) => {
                    if (group.group_id != tmpGroup.group_id) {
                        this.groupsForAssign.push(tmpGroup);
                    }
                });
                this.config = {data : {id: group.id,group_desc: group.group_desc, groupsForAssign : this.groupsForAssign, users : group.users}};
                    this.dialogRef = this.dialog.open(GroupsAdministrationRedirectModalComponent,this.config);
                    this.dialogRef.afterClosed().subscribe((result: string) => {
                        console.log(result);
                        if (result) {
                            if (result == "_NO_REPLACEMENT") {
                                this.deleteGroup(group);
                            } else {
                                this.http.put(this.coreUrl + "rest/groups/" + group.id + "/reassign/" + result,{})
                                    .subscribe((data : any) => {
                                        this.deleteGroup(group);
                                    }, (err) => {
                                        this.notify.error(err.error.errors);
                                    });
                            }
                        }
                        this.dialogRef = null;
                  });
            }
 
    }

    deleteGroup(group: any) {
        this.http.delete(this.coreUrl + "rest/groups/" + group['id'])
            .subscribe((data : any) => {
                setTimeout(() => {
                    this.groups = data['groups'];
                    this.dataSource = new MatTableDataSource(this.groups);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
                this.notify.success(this.lang.groupDeleted);
                
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
@Component({
    templateUrl: angularGlobals["groups-administration-redirect-modalView"],
  })
  export class GroupsAdministrationRedirectModalComponent {
    lang: any = LANG;

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any,public dialogRef: MatDialogRef<GroupsAdministrationRedirectModalComponent>) {
    }
  }