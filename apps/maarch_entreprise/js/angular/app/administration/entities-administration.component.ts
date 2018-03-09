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
    idCircuitVisa:number;

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
        super(http, ['usersAndEntities', 'visaUsers']);
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
                if (this.currentEntity.visaTemplate[0]) {
                    this.idCircuitVisa = this.currentEntity.visaTemplate[0].id;
                }
                this.dataSource = new MatTableDataSource(this.currentEntity.users);
                this.dataSource.paginator = this.paginator;
                this.dataSource.sort = this.sort;
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    addElemListModel(element: any) {
        /* create listDiff struct */
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "entity_id",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        }
        var inListModel = false;
        var newElemListModel: any = {};

        this.currentEntity.roles.forEach((role: any) => {
            if (role.available == true) {
                if (this.currentEntity.listTemplate[role.id]) {
                    this.currentEntity.listTemplate[role.id].forEach((listModel: any) => {
                        /* insert current diff list */
                        newDiffList.items.push({
                            "item_type": listModel.item_type,
                            "item_mode": role.id,
                            "item_id": listModel.item_id,
                            "sequence": listModel.sequence
                        });

                        if (listModel.item_id == element.id) {
                            inListModel = true;
                        }
                    });
                }
            }
        });
        if (!inListModel) {
            if (this.currentEntity.listTemplate.dest.length == 0 && element.type == 'user') {
                newElemListModel = {
                    "item_type": "user_id",
                    "item_id": element.id,
                    "labelToDisplay": element.idToDisplay,
                    "descriptionToDisplay": element.otherInfo,
                    "item_mode": "dest",
                    "sequence": 0
                };
                this.currentEntity.listTemplate.dest.unshift(newElemListModel);
                newDiffList.items.push({
                    "item_type": "user_id",
                    "item_mode": "dest",
                    "item_id": newElemListModel.item_id,
                    "sequence": newElemListModel.sequence
                });
            } else {
                var itemType = '';
                if (element.type == 'user') {
                    itemType = 'user_id';
                } else {
                    itemType = 'entity_id';
                }

                newElemListModel = {
                    "item_type": itemType,
                    "item_id": element.id,
                    "labelToDisplay": element.idToDisplay,
                    "descriptionToDisplay": element.otherInfo,
                    "item_mode": "cc",
                    "sequence": 0
                };
                this.currentEntity.listTemplate.cc.unshift(newElemListModel);
                newDiffList.items.push({
                    "item_type": newElemListModel.item_type,
                    "item_mode": "cc",
                    "item_id": newElemListModel.item_id,
                    "sequence": 0
                });
            }
            if (this.currentEntity.listTemplate.id) {
                this.http.put(this.coreUrl + "rest/listTemplates/" + this.currentEntity.listTemplate.id, newDiffList)
                    .subscribe((data: any) => {
                        this.currentEntity.listTemplate.id = data.id;
                        this.notify.success(this.lang.entityUpdated);
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            } else {
                this.http.post(this.coreUrl + "rest/listTemplates", newDiffList)
                    .subscribe((data: any) => {
                        this.currentEntity.listTemplate.id = data.id;
                        this.notify.success(this.lang.entityUpdated);
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            }
        }
        this.elementCtrl.setValue('');
    }

    addElemListModelVisa(element: any) {
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "VISA_CIRCUIT",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        }
        var itemMode = '';

        var newElemListModel = {
            "id": '',
            "item_type": 'user_id',
            "item_mode": "sign",
            "item_id": element.id,
            "sequence": this.currentEntity.visaTemplate.length,
            "idToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo
        };

        this.currentEntity.visaTemplate.forEach((listModel: any, i: number) => {
            listModel.sequence = i;
            listModel.item_mode = "visa";
            newDiffList.items.push({
                "id": listModel.id,
                "item_id": listModel.item_id,
                "item_type": "user_id",
                "item_mode": listModel.item_mode,
                "sequence": listModel.sequence
            });
        });

        newDiffList.items.push(newElemListModel);

        if (this.currentEntity.visaTemplate.length > 0) {
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa, newDiffList)
                .subscribe((data: any) => {
                    this.idCircuitVisa = data.id;
                    this.currentEntity.visaTemplate.push(newElemListModel);
                    this.notify.success(this.lang.entityUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.post(this.coreUrl + "rest/listTemplates", newDiffList)
                .subscribe((data: any) => {
                    this.idCircuitVisa = data.id;
                    this.currentEntity.visaTemplate.push(newElemListModel);
                    this.notify.success(this.lang.entityUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
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
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "entity_id",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        }

        if (role == 'dest' && this.currentEntity.listTemplate.dest.length > 0) {
            this.currentEntity.listTemplate.dest.forEach((listModel: any) => {
                if (listModel.item_id != template.item_id) {
                    this.currentEntity.listTemplate.cc.push(listModel);
                }
            });
            this.currentEntity.listTemplate.dest = [template];
        }

        this.currentEntity.roles.forEach((role: any) => {
            if (role.available == true) {
                if (this.currentEntity.listTemplate[role.id]) {
                    this.currentEntity.listTemplate[role.id].forEach((listModel: any) => {
                        newDiffList.items.push({
                            "item_id": listModel.item_id,
                            "item_type": listModel.item_type,
                            "item_mode": role.id,
                            "sequence": listModel.sequence
                        })
                    });
                }
            }
        });
        if (this.currentEntity.listTemplate.id) {
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.currentEntity.listTemplate.id, newDiffList)
                .subscribe((data: any) => {
                    this.currentEntity.listTemplate.id = data.id;
                    this.notify.success(this.lang.entityUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.post(this.coreUrl + "rest/listTemplates", newDiffList)
                .subscribe((data: any) => {
                    this.currentEntity.listTemplate.id = data.id;
                    this.notify.success(this.lang.entityUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
    updateDiffListVisa(template: any): any {
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "VISA_CIRCUIT",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        }
        this.currentEntity.visaTemplate.forEach((listModel: any, i: number) => {
            listModel.sequence = i;
            if (i == (this.currentEntity.visaTemplate.length - 1)) {
                listModel.item_mode = "sign";
            } else {
                listModel.item_mode = "visa";
            }
            newDiffList.items.push({
                "id": listModel.id,
                "item_id": listModel.item_id,
                "item_type": "user_id",
                "item_mode": listModel.item_mode,
                "sequence": listModel.sequence
            });
        });
        this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa, newDiffList)
            .subscribe((data: any) => {
                this.idCircuitVisa = data.id;
                this.notify.success(this.lang.entityUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });

    }
    removeDiffList(i: number, role: string): any {
        this.currentEntity.listTemplate[role].splice(i, 1);
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "entity_id",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        }
        this.currentEntity.roles.forEach((role: any) => {
            if (role.available == true) {
                if (this.currentEntity.listTemplate[role.id]) {
                    this.currentEntity.listTemplate[role.id].forEach((listModel: any) => {
                        newDiffList.items.push({
                            "item_id": listModel.item_id,
                            "item_type": listModel.item_type,
                            "item_mode": role.id,
                            "sequence": listModel.sequence
                        });
                    });
                }
            }
        });
        this.http.put(this.coreUrl + "rest/listTemplates/" + this.currentEntity.listTemplate.id, newDiffList)
            .subscribe((data: any) => {
                this.currentEntity.listTemplate.id = data.id;
                this.notify.success(this.lang.entityUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
    removeDiffListVisa(template: any, i: number): any {
        this.currentEntity.visaTemplate.splice(i, 1);
        
        if (this.currentEntity.visaTemplate.length > 0) {
            var newDiffList = {
                "object_id": this.currentEntity.entity_id,
                "object_type": "VISA_CIRCUIT",
                "title": this.currentEntity.entity_id,
                "description": this.currentEntity.entity_id,
                "items": Array()
            }
    
            this.currentEntity.visaTemplate.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (i == (this.currentEntity.visaTemplate.length - 1)) {
                    listModel.item_mode = "sign";
                } else {
                    listModel.item_mode = "visa";
                }
                newDiffList.items.push({
                    "item_id": listModel.item_id,
                    "item_type": "user_id",
                    "item_mode": listModel.item_mode,
                    "sequence": listModel.sequence
                });
            });
    
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa, newDiffList)
                .subscribe((data: any) => {
                    this.idCircuitVisa = data.id;
                    this.notify.success(this.lang.entityUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }else{
            this.http.delete(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa)
                .subscribe((data: any) => {
                    this.idCircuitVisa = null;
                    this.notify.success(this.lang.entityUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
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