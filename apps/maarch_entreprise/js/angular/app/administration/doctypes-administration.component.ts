import { ChangeDetectorRef, Component, OnInit, ViewChild, Inject } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogConfig, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material';

declare function $j(selector: any): any;
declare var angularGlobals: any;


@Component({
    templateUrl: angularGlobals["doctypes-administrationView"],
    providers: [NotificationService]
})

export class DoctypesAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    dialogRef: MatDialogRef<any>;
    config: any = {};
    coreUrl: string;
    lang: any = LANG;

    doctypes: any[] = [];
    currentType: any = false;
    currentSecondLevel: any = false;
    currentFirstLevel: any = false;
    firstLevels: any = false;
    folderTypes: any = false;
    types: any = false;
    secondLevels: any = false;
    processModes: any = false;
    models: any = false;
    indexes: any = false;

    loading: boolean = false;
    creationMode: boolean = false;


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Typologie documentaire";
        }
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/doctypes")
            .subscribe((data: any) => {
                this.doctypes = data['structure'];
                setTimeout(() => {
                    $j('#jstree').jstree({
                        "checkbox": {
                            "three_state": false //no cascade selection
                        },
                        'core': {
                            'themes': {
                                'name': 'proton',
                                'responsive': true
                            },
                            'data': this.doctypes,
                            "check_callback": true
                        },
                        "plugins": ["search", "dnd", "contextmenu"],
                    });
                    var to: any = false;
                    $j('#jstree_search').keyup(function () {
                        if (to) { clearTimeout(to); }
                        to = setTimeout(function () {
                            var v = $j('#jstree_search').val();
                            $j('#jstree').jstree(true).search(v);
                        }, 250);
                    });
                    $j('#jstree')
                        // listen for event
                        .on('select_node.jstree', (e: any, data: any) => {
                            this.loadDoctype(data.node);

                        }).on('move_node.jstree', (e: any, data: any) => {
                            this.loadDoctype(data.node.id);
                            // this.currentDoctype.parent_entity_id = data.parent;
                            // this.moveEntity();
                        })
                        // create the instance
                        .jstree();
                }, 0);
                $j('#jstree').jstree('select_node', this.doctypes[0]);
                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    loadDoctype(data: any) {
        this.creationMode = false;

        // Doctype
        if(data.original.type_id){
            this.currentFirstLevel  = false;
            this.currentSecondLevel = false;
            this.http.get(this.coreUrl + "rest/doctypes/types/" + data.original.type_id )
                .subscribe((data: any) => {
                    this.currentType  = data['doctype'];
                    this.secondLevels = data['secondLevel'];
                    this.processModes = data['processModes'];
                    this.models       = data['models'];
                    this.indexes      = data['indexes'];
                }, (err) => {
                    this.notify.error(err.error.errors);
                });

        // Second level
        } else if(data.original.doctypes_second_level_id) {
            this.currentFirstLevel  = false;
            this.currentType        = false;
            this.http.get(this.coreUrl + "rest/doctypes/secondLevel/" + data.original.doctypes_second_level_id )
                .subscribe((data: any) => {
                    this.currentSecondLevel = data['secondLevel'];
                    this.firstLevels        = data['firstLevel'];
                }, (err) => {
                    this.notify.error(err.error.errors);
                });

        // First level
        } else {
            this.currentSecondLevel = false;
            this.currentType        = false;
            this.http.get(this.coreUrl + "rest/doctypes/firstLevel/" + data.original.doctypes_first_level_id )
                .subscribe((data: any) => {
                    this.currentFirstLevel  = data['firstLevel'];
                    this.folderTypes        = data['folderTypes'];
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    // addElemListModel(element: any) {
    //     var inListModel = false;
    //     var newElemListModel = {
    //         "type": element.type,
    //         "id": element.id,
    //         "labelToDisplay": element.idToDisplay,
    //         "descriptionToDisplay": element.otherInfo,
    //     };
    //     this.currentDoctype.roles.forEach((role: any) => {
    //         if (role.available == true) {
    //             if (this.currentDoctype.listTemplate[role.id]) {

    //                 this.currentDoctype.listTemplate[role.id].forEach((listModel: any) => {
    //                     console.log(listModel);
    //                     if (listModel.id == element.id) {
    //                         inListModel = true;
    //                     }
    //                 });
    //             }
    //         }
    //     });
    //     if (!inListModel) {
    //         this.currentDoctype.listTemplate.cc.unshift(newElemListModel);
    //     }

    // }
    
    resetDatas() {
        this.currentFirstLevel  = false;
        this.currentSecondLevel = false;
        this.currentType        = false;       
    }

    saveFirstLevel() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/doctypes/firstLevel", this.currentFirstLevel)
                .subscribe((data: any) => {
                    this.resetDatas();
                    this.readMode();
                    this.doctypes = data['doctypeTree'];
                    $j('#jstree').jstree(true).settings.core.data = this.doctypes;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.firstLevelAdded);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/doctypes/firstLevel/" + this.currentFirstLevel.doctypes_first_level_id, this.currentFirstLevel)
                .subscribe((data: any) => {
                    this.doctypes = data['doctypeTree'];
                    $j('#jstree').jstree(true).settings.core.data = this.doctypes;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.firstLevelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    saveSecondLevel() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/doctypes/secondLevel", this.currentSecondLevel)
                .subscribe((data: any) => {
                    this.resetDatas();
                    this.readMode();
                    this.doctypes = data['doctypeTree'];
                    $j('#jstree').jstree(true).settings.core.data = this.doctypes;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.secondLevelAdded);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/doctypes/secondLevel/" + this.currentSecondLevel.doctypes_second_level_id, this.currentSecondLevel)
                .subscribe((data: any) => {
                    this.doctypes = data['doctypeTree'];
                    $j('#jstree').jstree(true).settings.core.data = this.doctypes;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.secondLevelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    saveType() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/doctypes/types", this.currentType)
                .subscribe((data: any) => {
                    this.resetDatas();
                    this.readMode();
                    this.doctypes = data['doctypeTree'];
                    $j('#jstree').jstree(true).settings.core.data = this.doctypes;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.documentTypeAdded);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/doctypes/types/" + this.currentType.type_id, this.currentType)
                .subscribe((data: any) => {
                    this.doctypes = data['doctypeTree'];
                    $j('#jstree').jstree(true).settings.core.data = this.doctypes;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.documentTypeUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
    // moveEntity() {
    //     this.http.put(this.coreUrl + "rest/entities/" + this.currentDoctype.entity_id, this.currentDoctype)
    //             .subscribe((data: any) => {
    //                 this.notify.success(this.lang.entityUpdated);
    //             }, (err) => {
    //                 this.notify.error(err.error.errors);
    //             });
    // }

    readMode() {
        this.creationMode = false;
        $j('#jstree').jstree('deselect_all');
        $j('#jstree').jstree('select_node', this.doctypes[0]);
    }

    removeFirstLevel() {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + this.currentFirstLevel.doctypes_first_level_label + ' »');

        if (r) {
            this.http.delete(this.coreUrl + "rest/doctypes/firstLevel/" + this.currentFirstLevel.doctypes_first_level_id)
                .subscribe((data: any) => {
                    this.resetDatas();
                    this.readMode();
                    this.doctypes = data['doctypeTree'];
                    $j('#jstree').jstree(true).settings.core.data = this.doctypes;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.firstLevelDeleted);
                    $j('#jstree').jstree('select_node', this.doctypes[0]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    removeSecondLevel() {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + this.currentSecondLevel.doctypes_second_level_label + ' »');

        if (r) {
            this.http.delete(this.coreUrl + "rest/doctypes/secondLevel/" + this.currentSecondLevel.doctypes_second_level_id)
                .subscribe((data: any) => {
                    this.resetDatas();
                    this.readMode();
                    this.doctypes = data['doctypeTree'];
                    $j('#jstree').jstree(true).settings.core.data = this.doctypes;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.secondLevelDeleted);
                    $j('#jstree').jstree('select_node', this.doctypes[0]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    removeType() {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + this.currentType.description + ' »');

        if (r) {
            this.http.delete(this.coreUrl + "rest/doctypes/types/" + this.currentType.type_id)
                .subscribe((data: any) => {
                    if(data.deleted == 0){
                        this.resetDatas();
                        this.readMode();
                        this.doctypes = data['doctypeTree'];
                        $j('#jstree').jstree(true).settings.core.data = this.doctypes;
                        $j('#jstree').jstree("refresh");
                        this.notify.success(this.lang.documentTypeDeleted);
                        $j('#jstree').jstree('select_node', this.doctypes[0]);
                    } else {
                        this.config = { data: {count: data.deleted, types: data.doctypes} };
                        this.dialogRef = this.dialog.open(DoctypesAdministrationRedirectModalComponent, this.config);
                        this.dialogRef.afterClosed().subscribe((result: any) => {
                        if (result) {
                            this.http.put(this.coreUrl + "rest/doctypes/types/" + this.currentType.type_id + "/redirect", result)
                                .subscribe((data: any) => {
                                    this.resetDatas();
                                    this.readMode();
                                    this.doctypes = data['doctypeTree'];
                                    $j('#jstree').jstree(true).settings.core.data = this.doctypes;
                                    $j('#jstree').jstree("refresh");
                                    this.notify.success(this.lang.documentTypeDeleted);
                                    $j('#jstree').jstree('select_node', this.doctypes[0]);
                                }, (err) => {
                                    this.notify.error(err.error.errors);
                                });
                        }
                        this.dialogRef = null;
                        });
                    }

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    prepareDoctypeAdd() {
        this.currentFirstLevel  = {};
        this.currentSecondLevel = {};
        this.currentType        = {};
        $j('#jstree').jstree('deselect_all');
        this.http.get(this.coreUrl + "rest/administration/doctypes/new")
            .subscribe((data: any) => {
                this.folderTypes  = data['folderTypes'];
                this.firstLevels  = data['firstLevel'];
                this.secondLevels = data['secondLevel'];
                this.processModes = data['processModes'];
                this.models       = data['models'];
                this.indexes      = data['models'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        this.creationMode = true;
    }

}
@Component({
    templateUrl: angularGlobals["doctypes-administration-redirect-modalView"],
})
export class DoctypesAdministrationRedirectModalComponent {
    lang: any = LANG;

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<DoctypesAdministrationRedirectModalComponent>) {
        // super(http, ['entities']);
    }
}