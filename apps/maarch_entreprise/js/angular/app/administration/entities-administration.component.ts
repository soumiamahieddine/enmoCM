import { ChangeDetectorRef, Component, OnInit, ViewChild, Inject } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatSidenav, MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogConfig, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material';


import { AutoCompletePlugin } from '../../plugins/autocomplete.plugin';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: angularGlobals["entities-administrationView"],
    providers: [NotificationService]
})
export class EntitiesAdministrationComponent extends AutoCompletePlugin implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    dialogRef: MatDialogRef<any>;
    coreUrl: string;
    lang: any = LANG;
    isDraggable: boolean = true;

    entities: any[] = [];
    entityTypeList: any[];
    currentEntity: any = {};
    config: any = {};

    loading: boolean = false;
    creationMode: boolean = false;

    displayedColumns = ['firstname', 'lastname'];
    dataSource = new MatTableDataSource(this.currentEntity.users);
    @ViewChild('snav2') sidenav: MatSidenav;

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) {
        super(http, ['usersAndEntities', 'users']);
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Entités";
        }
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;
        this.http.get(this.coreUrl + "rest/entityTypes")
            .subscribe((data: any) => {
                this.entityTypeList = data['types'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });

        this.http.get(this.coreUrl + "rest/entities")
            .subscribe((data: any) => {
                this.entities = data['entities'];
                setTimeout(() => {
                    $j('#jstree').jstree({
                        "checkbox": {
                            'deselect_all': true,
                            "three_state": false //no cascade selection
                        },
                        'core': {
                            'themes': {
                                'name': 'proton',
                                'responsive': true
                            },
                            'multiple': false,
                            'data': this.entities,
                            "check_callback": function (operation: any, node: any, node_parent: any, node_position: any, more: any) {
                                if (operation == 'move_node') {
                                    if (!node_parent.original.allowed) {
                                        return false
                                    } else
                                        return true;
                                }
                            }
                        },
                        "dnd": {
                            is_draggable: function (nodes: any) {
                                var i = 0,
                                    j = nodes.length;
                                for (; i < j; i++) {
                                    if (!nodes[i].original.allowed) {
                                        return false;
                                    }
                                }
                                return true;
                            }
                        },
                        "plugins": ["checkbox", "search", "dnd", "sort"]
                    });
                    $j('#jstree').jstree('select_node', this.entities[0]);
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
                            if (this.sidenav.opened == false) {
                                this.sidenav.open();
                            }
                            if (this.creationMode == true) {
                                this.currentEntity.parent_entity_id = data.node.id;
                            } else {
                                this.loadEntity(data.node.id);
                            }

                        }).on('deselect_node.jstree', (e: any, data: any) => {

                            this.sidenav.close();

                        }).on('move_node.jstree', (e: any, data: any) => {


                            if (this.currentEntity.parent_entity_id != this.currentEntity.entity_id) {
                                this.currentEntity.parent_entity_id = data.parent;
                            }
                            this.moveEntity();
                        })
                        // create the instance
                        .jstree();

                    $j(document).on('dnd_start.vakata', (e: any, data: any) => {
                        $j('#jstree').jstree('deselect_all');
                        $j('#jstree').jstree('select_node', data.data.nodes[0]);
                    });
                }, 0);
                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    loadEntity(entity_id: any) {
        this.http.get(this.coreUrl + "rest/entities/" + entity_id + '/details')
            .subscribe((data: any) => {
                this.currentEntity = data['entity'];
                this.dataSource = new MatTableDataSource(this.currentEntity.users);
                this.dataSource.paginator = this.paginator;
                this.dataSource.sort = this.sort;
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    addElemListModel(element: any) {
        var inListModel = false;
        var newElemListModel = {
            "type": element.type,
            "id": element.id,
            "labelToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo,
        };
        this.currentEntity.roles.forEach((role: any) => {
            if (role.available == true) {
                if (this.currentEntity.listTemplate[role.id]) {

                    this.currentEntity.listTemplate[role.id].forEach((listModel: any) => {
                        console.log(listModel);
                        if (listModel.id == element.id) {
                            inListModel = true;
                        }
                    });
                }
            }
        });
        if (!inListModel) {
            if (this.currentEntity.listTemplate.dest.length == 0 && element.type == 'user') {
                this.currentEntity.listTemplate.dest.unshift(newElemListModel);
            } else {
                this.currentEntity.listTemplate.cc.unshift(newElemListModel);
            }

        }

        this.elementCtrl.setValue('');
    }

    addElemListModelVisa(element: any) {
        var inListModel = false;
        var newElemListModel = {
            "type": element.type,
            "id": element.id,
            "labelToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo,
        };

        this.currentEntity.visaTemplate.unshift(newElemListModel);

        this.userCtrl.setValue('');
    }

    saveEntity() {
        if (this.currentEntity.parent_entity_id == '#') {
            this.currentEntity.parent_entity_id = '';
        }

        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/entities", this.currentEntity)
                .subscribe((data: any) => {
                    this.entities = data['entities'];
                    $j('#jstree').jstree(true).settings.core.data = this.entities;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.entityAdded);
                    this.creationMode = false;
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
                .subscribe((data: any) => {
                    this.entities = data['entities'];
                    $j('#jstree').jstree(true).settings.core.data = this.entities;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.entityUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    moveEntity() {
        this.http.put(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
            .subscribe((data: any) => {
                this.notify.success(this.lang.entityUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    readMode() {
        this.creationMode = false;
        this.isDraggable = true;
        $j('#jstree').jstree('deselect_all');
        for (let i = 0; i < this.entities.length; i++) {
            if (this.entities[i].allowed == true) {
                $j('#jstree').jstree('select_node', this.entities[i]);
                break;
            }
        }
    }

    removeEntity() {
        if (this.currentEntity.documents > 0 || this.currentEntity.redirects > 0 || this.currentEntity.instances > 0 || this.currentEntity.users.length > 0) {
            this.config = { data: { entity: this.currentEntity } };
            this.dialogRef = this.dialog.open(EntitiesAdministrationRedirectModalComponent, this.config);
            this.dialogRef.afterClosed().subscribe((result: any) => {
                console.log(result);
                if (result) {
                    this.http.put(this.coreUrl + "rest/entities/" + result.entity_id + "/reassign/" + result.redirectEntity, {})
                        .subscribe((data: any) => {
                            this.entities = data['entities'];
                            $j('#jstree').jstree(true).settings.core.data = this.entities;
                            $j('#jstree').jstree("refresh");
                            this.notify.success(this.lang.entityDeleted);
                            for (let i = 0; i < this.entities.length; i++) {
                                if (this.entities[i].allowed == true) {
                                    $j('#jstree').jstree('select_node', this.entities[i]);
                                    break;
                                }
                            }
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                }
                this.dialogRef = null;
            });
        } else {
            let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + this.currentEntity.entity_label + ' »');

            if (r) {
                this.http.delete(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id)
                    .subscribe((data: any) => {
                        this.entities = data['entities'];
                        $j('#jstree').jstree(true).settings.core.data = this.entities;
                        $j('#jstree').jstree("refresh");
                        this.notify.success(this.lang.entityDeleted);
                        for (let i = 0; i < this.entities.length; i++) {
                            if (this.entities[i].allowed == true) {
                                $j('#jstree').jstree('select_node', this.entities[i]);
                                break;
                            }
                        }
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            }
        }

    }

    prepareEntityAdd() {
        this.creationMode = true;
        this.isDraggable = false;
        this.currentEntity = { "entity_type": this.entityTypeList[0].id };
        $j('#jstree').jstree('deselect_all');
        for (let i = 0; i < this.entities.length; i++) {
            if (this.entities[i].allowed == true) {
                $j('#jstree').jstree('select_node', this.entities[i]);
                break;
            }
        }
    }


    updateStatus(entity: any, method: string) {
        this.http.put(this.coreUrl + "rest/entities/" + entity['entity_id'] + "/status", { "method": method })
            .subscribe((data: any) => {
                this.notify.success("");
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updateDiffList(template: any, role: any): any {
        if (role == 'dest' && this.currentEntity.listTemplate.dest.length > 0) {
            this.currentEntity.listTemplate.dest.forEach((listModel: any) => {
                if (listModel.id != template.id) {
                    this.currentEntity.listTemplate.cc.push(listModel);
                }
            });
            this.currentEntity.listTemplate.dest = [template];
        }
    }
    removeDiffList(i: number, role: string): any {
        this.currentEntity.listTemplate[role].splice(i, 1);
    }
    removeDiffListVisa(i: number): any {
        this.currentEntity.visaTemplate.splice(i, 1);
    }
}
@Component({
    templateUrl: angularGlobals["entities-administration-redirect-modalView"],
})
export class EntitiesAdministrationRedirectModalComponent extends AutoCompletePlugin {
    lang: any = LANG;

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<EntitiesAdministrationRedirectModalComponent>) {
        super(http, ['entities']);
    }
}