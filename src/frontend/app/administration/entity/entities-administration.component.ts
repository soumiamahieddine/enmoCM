import { Component, OnInit, ViewChild, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSidenav } from '@angular/material/sidenav';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ConfirmModalComponent } from '../../confirmModal.component';
import { NotificationService } from '../../notification.service';
import { HeaderService }        from '../../../service/header.service';
import { Router } from '@angular/router';
import { AppService } from '../../../service/app.service';

declare function $j(selector: any): any;

@Component({
    templateUrl: "entities-administration.component.html",
    styleUrls: ['entities-administration.component.css'],
    providers: [NotificationService, AppService]
})
export class EntitiesAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav', { static: true }) public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight  : MatSidenav;

    dialogRef                       : MatDialogRef<any>;

    lang                            : any       = LANG;
    loading                         : boolean   = false;

    entities                        : any[]     = [];
    listTemplateRoles               : any[]     = [];
    entityTypeList                  : any[]     = [];
    currentEntity                   : any       = {};
    isDraggable                     : boolean   = true;
    newEntity                       : boolean   = false;
    creationMode                    : boolean   = false;
    listDiffModified                : boolean   = false;
    visaCircuitModified             : boolean   = false;
    opinionCircuitModified          : boolean   = false;
    idVisaCircuit                   : number;
    idOpinionCircuit                : number;
    config                          : any       = {};

    dataSourceUsers             = new MatTableDataSource(this.currentEntity.users);
    dataSourceTemplates         = new MatTableDataSource(this.currentEntity.templates);
    displayedColumnsUsers       = ['firstname', 'lastname'];
    displayedColumnsTemplates   = ['template_label', 'template_target'];


    @ViewChild('paginatorUsers', { static: false }) paginatorUsers: MatPaginator;
    @ViewChild('paginatorTemplates', { static: false }) paginatorTemplates: MatPaginator;
    @ViewChild('tableUsers', { static: true }) sortUsers: MatSort;
    @ViewChild('tableTemplates', { static: true }) sortTemplates: MatSort;
    applyFilterUsers(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.dataSourceUsers.filter = filterValue;
    }

    applyFilterTemplates(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.dataSourceTemplates.filter = filterValue;
    }

    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        public dialog: MatDialog, 
        private headerService: HeaderService, 
        private router: Router,
        public appService: AppService
    ) {
        $j("link[href='merged_css.php']").remove();
    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.administration + ' ' + this.lang.entities);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = true;
        this.http.get("../../rest/entityTypes")
            .subscribe((data: any) => {
                this.entityTypeList = data['types'];
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
        this.http.get("../../rest/listTemplates/types/entity_id/roles")
            .subscribe((data: any) => {
                this.listTemplateRoles = data['roles'];
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });

        this.http.get("../../rest/entities")
            .subscribe((data: any) => {
                this.entities = data['entities'];
                this.loading = false;

                setTimeout(() => {
                    $j('#jstree').jstree({
                        "checkbox": {
                            'deselect_all': true,
                            "three_state": false //no cascade selection
                        },
                        'core': {
                            force_text : true,
                            'themes': {
                                'name': 'proton',
                                'responsive': true
                            },
                            'multiple': false,
                            'data': this.entities,
                            "check_callback": function (operation: any, node: any, node_parent: any, node_position: any, more: any) {
                                if (operation == 'move_node') {
                                    if (node_parent.id == '#') {
                                        return false;
                                    } else if (!node_parent.original.allowed) {
                                        return false;
                                    } else {
                                        return true;
                                    }
                                }
                            }
                        },
                        "dnd": {
                            is_draggable: function (nodes: any) {
                                var i = 0;
                                var j = nodes.length;
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
                            if (this.sidenavRight.opened == false) {
                                this.sidenavRight.open();
                            }
                            if (this.creationMode == true) {
                                this.currentEntity.parent_entity_id = data.node.id;
                            } else {
                                if (this.newEntity == true) {
                                    this.loadEntity(this.currentEntity.entity_id);
                                    this.newEntity = false;
                                } else {
                                    this.loadEntity(data.node.id);
                                }
                            }

                        }).on('deselect_node.jstree', (e: any, data: any) => {

                            this.sidenavRight.close();

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
            }, () => {
                location.href = "index.php";
            });
    }

    loadEntity(entity_id: any) {
        this.listDiffModified = false;
        this.visaCircuitModified = false;
        this.opinionCircuitModified = false;
        this.http.get("../../rest/entities/" + entity_id + '/details')
            .subscribe((data: any) => {
                this.currentEntity = data['entity'];
                if (this.currentEntity.visaCircuit) {
                    this.idVisaCircuit = this.currentEntity.visaCircuit.id;
                } else {
                    this.idVisaCircuit = null;
                }
                if (this.currentEntity.opinionCircuit) {
                    this.idOpinionCircuit = this.currentEntity.opinionCircuit.id;
                } else {
                    this.idOpinionCircuit = null;
                }
                this.dataSourceUsers = new MatTableDataSource(this.currentEntity.users);
                this.dataSourceUsers.paginator = this.paginatorUsers;
                this.dataSourceUsers.sort = this.sortUsers;

                this.dataSourceTemplates = new MatTableDataSource(this.currentEntity.templates);
                this.dataSourceTemplates.paginator = this.paginatorTemplates;
                this.dataSourceTemplates.sort = this.sortTemplates;

                if (!this.currentEntity.listTemplate.items) {
                    this.currentEntity.listTemplate.items = [];
                }
                this.listTemplateRoles.forEach((role: any) => {
                    if (role.available && !this.currentEntity.listTemplate.items[role.id]) {
                        this.currentEntity.listTemplate.items[role.id] = [];
                    }
                });
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    addElemListModel(element: any) {
        this.listDiffModified = true;
        var inListModel = false;
        var newElemListModel: any = {};

        if (!this.currentEntity.listTemplate.items) {
            this.currentEntity.listTemplate.items = [];
        }

        this.listTemplateRoles.forEach((role: any) => {
            if (role.available == true) {
                if (this.currentEntity.listTemplate.items[role.id]) {
                    this.currentEntity.listTemplate.items[role.id].forEach((listModel: any) => {
                        if (listModel.id == element.id) {
                            inListModel = true;
                        }
                    });
                }
            }
        });
        if (!inListModel) {
            if (!this.currentEntity.listTemplate.items.dest) {
                this.currentEntity.listTemplate.items.dest = [];
            }
            if (this.currentEntity.listTemplate.items.dest.length == 0 && element.type == 'user') {
                newElemListModel = {
                    "id": element.serialId,
                    "type": "user",
                    "mode": "dest",
                    "labelToDisplay": element.idToDisplay,
                    "descriptionToDisplay": element.otherInfo
                };
                this.currentEntity.listTemplate.items.dest.unshift(newElemListModel);

            } else {
                if (!this.currentEntity.listTemplate.items.cc) {
                    this.currentEntity.listTemplate.items.cc = [];
                }
                const itemType = element.type == 'user' ? 'user' : 'entity';

                newElemListModel = {
                    "id": element.serialId,
                    "type": itemType,
                    "mode": "cc",
                    "labelToDisplay": element.idToDisplay,
                    "descriptionToDisplay": element.otherInfo
                };
                this.currentEntity.listTemplate.items.cc.unshift(newElemListModel);
            }
        }
    }

    addElemListModelVisa(element: any) {
        this.visaCircuitModified = true;
        let newElemListModel = {
            "id": element.id,
            "type": 'user',
            "mode": "sign",
            "idToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo
        };

        if (!this.currentEntity.visaCircuit.items) {
            this.currentEntity.visaCircuit.items = [];
        }
        this.currentEntity.visaCircuit.items.push(newElemListModel);
        if (this.currentEntity.visaCircuit.items.length > 1) {
            this.currentEntity.visaCircuit.items[this.currentEntity.visaCircuit.items.length-2].mode = 'visa';
        }
    }

    addElemListModelOpinion(element: any) {
        this.opinionCircuitModified = true;
        let newElemListModel = {
            "id": element.id,
            "type": 'user',
            "mode": "avis",
            "idToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo
        };

        if (!this.currentEntity.opinionCircuit.items) {
            this.currentEntity.opinionCircuit.items = [];
        }
        this.currentEntity.opinionCircuit.items.push(newElemListModel);
    }

    saveEntity() {
        if (this.currentEntity.parent_entity_id == '#') {
            this.currentEntity.parent_entity_id = '';
        }

        var r = true;
        if (this.currentEntity.parent_entity_id == '') {
            r = confirm(this.lang.entityWithoutParentMessage);
        }

        if (r) {
            if (this.creationMode) {
                this.http.post("../../rest/entities", this.currentEntity)
                    .subscribe((data: any) => {
                        this.currentEntity.listTemplate = [];
                        this.entities = data['entities'];
                        this.creationMode = false;
                        this.newEntity = true;
                        $j('#jstree').jstree(true).settings.core.data = this.entities;
                        $j('#jstree').jstree(true).settings.select_node = this.currentEntity;
                        $j('#jstree').jstree(true).refresh();
                        $j('#jstree').on("refresh.jstree", (e:any) => {
                            $j('#jstree').jstree('deselect_all');
                            $j('#jstree').jstree('select_node', this.currentEntity.entity_id);
                          });
                        this.notify.success(this.lang.entityAdded);
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            } else {
                this.http.put("../../rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
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
    }

    moveEntity() {
        this.http.put("../../rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
            .subscribe(() => {
                this.notify.success(this.lang.entityUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    readMode() {
        this.creationMode = false;
        this.isDraggable = true;
        $j('#jstree').jstree('deselect_all');
        if (this.currentEntity.parent_entity_id) {
            for (let i = 0; i < this.entities.length; i++) {
                if (this.entities[i].entity_id == this.currentEntity.parent_entity_id) {
                    $j('#jstree').jstree('select_node', this.entities[i]);
                    break;
                }
            }
        } else {
            this.sidenavRight.close();
        }
    }

    selectParentEntity(entity_id: any) {
        if (this.creationMode) {
            $j('#jstree').jstree('deselect_all');
            $j('#jstree').jstree('select_node', entity_id);
        }
    }

    removeEntity() {
        if (this.currentEntity.documents > 0 || this.currentEntity.redirects > 0 || this.currentEntity.instances > 0 || this.currentEntity.users.length > 0 || this.currentEntity.templates.length > 0) {
            this.config = { data: { entity: this.currentEntity } };
            this.dialogRef = this.dialog.open(EntitiesAdministrationRedirectModalComponent, this.config);
            this.dialogRef.afterClosed().subscribe((result: any) => {
                if (result) {
                    if (this.currentEntity.listTemplate.id) {
                        this.http.delete("../../rest/listTemplates/" + this.currentEntity.listTemplate.id)
                        .subscribe((data: any) => {
                            this.currentEntity.listTemplate.id = data.id;
                            this.http.get("../../rest/listTemplates/types/entity_id/roles")
                                .subscribe((data: any) => {
                                    this.listTemplateRoles = data['roles'];
                                }, (err) => {
                                    this.notify.error(err.error.errors);
                                });
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                    }

                    if (this.idVisaCircuit) {
                        this.http.delete("../../rest/listTemplates/" + this.idVisaCircuit)
                            .subscribe(() => {
                                this.idVisaCircuit = null;
                            }, (err) => {
                                this.notify.error(err.error.errors);
                            });
                    }
                    
                    this.http.put("../../rest/entities/" + result.entity_id + "/reassign/" + result.redirectEntity, {})
                        .subscribe((data: any) => {
                            this.entities = data['entities'];
                            $j('#jstree').jstree(true).settings.core.data = this.entities;
                            $j('#jstree').jstree("refresh");
                            this.sidenavRight.close();

                            if (typeof data['deleted'] !== "undefined" && !data['deleted']) {
                                this.notify.success(this.lang.entityDeletedButAnnuaryUnreachable);
                            } else {
                                this.notify.success(this.lang.entityDeleted);
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
                if (this.currentEntity.listTemplate.id) {
                    this.http.delete("../../rest/listTemplates/" + this.currentEntity.listTemplate.id)
                    .subscribe((data: any) => {
                        this.currentEntity.listTemplate.id = data.id;
                        this.http.get("../../rest/listTemplates/types/entity_id/roles")
                            .subscribe((data: any) => {
                                this.listTemplateRoles = data['roles'];
                            }, (err) => {
                                this.notify.error(err.error.errors);
                            });
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
                }

                if (this.idVisaCircuit) {
                    this.http.delete("../../rest/listTemplates/" + this.idVisaCircuit)
                        .subscribe(() => {
                            this.idVisaCircuit = null;
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                }
                
                this.http.delete("../../rest/entities/" + this.currentEntity.entity_id)
                    .subscribe((data: any) => {
                        this.entities = data['entities'];
                        $j('#jstree').jstree(true).settings.core.data = this.entities;
                        $j('#jstree').jstree("refresh");
                        this.sidenavRight.close();
                        if (typeof data['deleted'] !== "undefined" && !data['deleted']) {
                            this.notify.success(this.lang.entityDeletedButAnnuaryUnreachable);
                        } else {
                            this.notify.success(this.lang.entityDeleted);
                        }
                    }, (err: any) => {
                        this.notify.error(err.error.errors);
                    });
            }
        }

    }

    prepareEntityAdd() {
        this.creationMode = true;
        this.isDraggable = false;
        if (this.currentEntity.entity_id) {
            for (let i = 0; i < this.entities.length; i++) {
                if (this.entities[i].entity_id == this.currentEntity.entity_id) {
                    this.currentEntity = { "entity_type": this.entityTypeList[0].id };
                    this.currentEntity.parent_entity_id = this.entities[i].entity_id;
                    break;
                }
            }
        } else {
            this.currentEntity = { "entity_type": this.entityTypeList[0].id };
            $j('#jstree').jstree('deselect_all');
            this.sidenavRight.open();
            /*for (let i = 0; i < this.entities.length; i++) {
                if (this.entities[i].allowed == true) {
                    $j('#jstree').jstree('select_node', this.entities[i]);
                    break;
                }
            }*/
        }
    }

    updateStatus(entity: any, method: string) {
        this.http.put("../../rest/entities/" + entity['entity_id'] + "/status", { "method": method })
            .subscribe((data: any) => {
                this.notify.success("");
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updateDiffList(template: any, role: any): any {
        this.listDiffModified = true;
        if (role == 'dest' && this.currentEntity.listTemplate.items.dest.length > 0) {
            this.currentEntity.listTemplate.items.dest.forEach((listModel: any) => {
                if (listModel.id != template.id) {
                    this.currentEntity.listTemplate.items.cc.push(listModel);
                }
            });
            this.currentEntity.listTemplate.items.dest = [template];
        }
    }

    saveDiffList() {
        this.listDiffModified = false;
        var newDiffList = {
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "type": "diffusionList",
            "entityId": this.currentEntity.id,
            "items": Array()
        };

        this.listTemplateRoles.forEach((role: any) => {
            if (role.available == true) {
                if (this.currentEntity.listTemplate.items[role.id]) {
                    this.currentEntity.listTemplate.items[role.id].forEach((listModel: any) => {
                        newDiffList.items.push({
                            "id": listModel.id,
                            "type": listModel.type,
                            "mode": role.id
                        })
                    });
                }
            }
        });

        if (newDiffList.items.length == 0) {
            this.http.delete("../../rest/listTemplates/" + this.currentEntity.listTemplate.id)
                .subscribe((data: any) => {
                    this.currentEntity.listTemplate.id = null;
                    this.http.get("../../rest/listTemplates/types/entity_id/roles")
                        .subscribe((data: any) => {
                            this.listTemplateRoles = data['roles'];
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                    this.notify.success(this.lang.diffusionModelDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else if (this.currentEntity.listTemplate.id) {
            this.http.put("../../rest/listTemplates/" + this.currentEntity.listTemplate.id, newDiffList)
                .subscribe(() => {
                    this.http.get("../../rest/listTemplates/types/entity_id/roles")
                        .subscribe((data: any) => {
                            this.listTemplateRoles = data['roles'];
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.post("../../rest/listTemplates", newDiffList)
                .subscribe((data: any) => {
                    this.currentEntity.listTemplate.id = data.id;
                    this.http.get("../../rest/listTemplates/types/entity_id/roles")
                        .subscribe((data: any) => {
                            this.listTemplateRoles = data['roles'];
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    updateDiffListVisa(template: any): any {
        this.visaCircuitModified = true;
        this.currentEntity.visaCircuit.items.forEach((listModel: any, i: number) => {
            listModel.sequence = i;
            if (i == (this.currentEntity.visaCircuit.items.length - 1)) {
                listModel.mode = "sign";
            } else {
                listModel.mode = "visa";
            }
        });
    }

    saveDiffListVisa() {
        this.visaCircuitModified = false;
        const newDiffList = {
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "type": "visaCircuit",
            "entityId": this.currentEntity.id,
            "items": Array()
        };
        if (this.idVisaCircuit == null) {
            this.currentEntity.visaCircuit.items.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (i == (this.currentEntity.visaCircuit.items.length - 1)) {
                    listModel.mode = "sign";
                } else {
                    listModel.mode = "visa";
                }
                newDiffList.items.push(listModel);
            });
            this.http.post("../../rest/listTemplates", newDiffList)
                .subscribe((data: any) => {
                    this.idVisaCircuit = data.id;
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else if (this.currentEntity.visaCircuit.items.length > 0) {
            this.currentEntity.visaCircuit.items.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (i == (this.currentEntity.visaCircuit.items.length - 1)) {
                    listModel.mode = "sign";
                } else {
                    listModel.mode = "visa";
                }
                newDiffList.items.push(listModel);
            });
            this.http.put("../../rest/listTemplates/" + this.idVisaCircuit, newDiffList)
                .subscribe(() => {
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.delete("../../rest/listTemplates/" + this.idVisaCircuit)
            .subscribe(() => {
                this.idVisaCircuit = null;
                this.notify.success(this.lang.diffusionModelDeleted);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        }
    }

    saveDiffListOpinion() {
        this.opinionCircuitModified = false;
        const newDiffList = {
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "type": "opinionCircuit",
            "entityId": this.currentEntity.id,
            "items": Array()
        };
        if (this.idOpinionCircuit == null) {
            newDiffList.items = this.currentEntity.opinionCircuit.items;
            this.http.post("../../rest/listTemplates", newDiffList)
                .subscribe((data: any) => {
                    this.idOpinionCircuit = data.id;
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else if (this.currentEntity.opinionCircuit.items.length > 0) {
            newDiffList.items = this.currentEntity.opinionCircuit.items;
            this.http.put("../../rest/listTemplates/" + this.idOpinionCircuit, newDiffList)
                .subscribe(() => {
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.delete("../../rest/listTemplates/" + this.idOpinionCircuit)
                .subscribe(() => {
                    this.idOpinionCircuit = null;
                    this.notify.success(this.lang.diffusionModelDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    removeDiffList(i: number, role: string): any {
        this.listDiffModified = true;
        this.currentEntity.listTemplate.items[role].splice(i, 1);
    }

    removeDiffListVisa(template: any, i: number): any {
        this.visaCircuitModified = true;
        this.currentEntity.visaCircuit.items.splice(i, 1);

        if (this.currentEntity.visaCircuit.items.length > 0) {
            this.currentEntity.visaCircuit.items.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (i == (this.currentEntity.visaCircuit.items.length - 1)) {
                    listModel.mode = "sign";
                } else {
                    listModel.mode = "visa";
                }
            });
        }
    }

    removeDiffListOpinion(template: any, i: number): any {
        this.opinionCircuitModified = true;
        this.currentEntity.opinionCircuit.items.splice(i, 1);
    }

    toggleRole(role: any) {
        if (role.usedIn.length > 0) {
            this.config = { data: { msg: this.lang.confirmAction, warn: this.lang.roleUsedInTemplateInfo + " : <b>" + role.usedIn.join(', ') + '</b><br/>' + this.lang.roleUsedInTemplateInfo2 } };
            let dialogRef = this.dialog.open(ConfirmModalComponent, this.config);
            dialogRef.afterClosed().subscribe(result => {
                if (result === "ok") {
                    role.available = !role.available;
                    this.http.put("../../rest/listTemplates/types/entity_id/roles", { "roles": this.listTemplateRoles })
                        .subscribe(() => {
                            role.usedIn = [];
                            if (this.currentEntity.listTemplate) {
                                this.currentEntity.listTemplate.items[role.id] = [];
                            }
                            this.notify.success(this.lang.listTemplatesRolesUpdated);
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                }
            });
        } else {
            role.available = !role.available;
            this.http.put("../../rest/listTemplates/types/entity_id/roles", { "roles": this.listTemplateRoles })
                .subscribe(() => {
                    if (this.currentEntity.listTemplate) {
                        this.currentEntity.listTemplate.items[role.id] = [];
                        this.http.get("../../rest/listTemplates/types/entity_id/roles")
                            .subscribe((data: any) => {
                                this.listTemplateRoles = data['roles'];
                            }, (err) => {
                                this.notify.error(err.error.errors);
                            });
                    }
                    this.notify.success(this.lang.listTemplatesRolesUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    linkUser(newUser:any) {
        let entity = {
            "entityId"  : this.currentEntity.entity_id,
            "role"      : ''
        };

        this.http.post("../../rest/users/" + newUser.id + "/entities", entity)
            .subscribe((data: any) => {
                var displayName = newUser.idToDisplay.split(" ");
                var user = {
                    id : newUser.id,
                    user_id : newUser.otherInfo,
                    firstname : displayName[0],
                    lastname : displayName[1]
                };
                this.currentEntity.users.push(user);
                this.dataSourceUsers = new MatTableDataSource(this.currentEntity.users);
                this.dataSourceUsers.paginator = this.paginatorUsers;
                this.dataSourceUsers.sort = this.sortUsers;
                this.notify.success(this.lang.userAdded);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    showTemplate(templateId:any) {
        if(this.currentEntity.canAdminTemplates) {
            this.router.navigate(['/administration/templates/' + templateId]);
        }
    }

    addEntityToAnnuary() {
        this.http.put("../../rest/entities/" + this.currentEntity.id + "/annuaries", this.currentEntity)
            .subscribe((data: any) => {
                this.currentEntity.business_id = data['entitySiret'];
                if (typeof data['synchronized'] === "undefined") {
                    this.notify.success(this.lang.siretGenerated);
                } else {
                    if (data['synchronized']) {
                        this.notify.success(this.lang.siretGeneratedAndSynchronizationDone);
                    } else {
                        this.notify.success(this.lang.siretGeneratedButAnnuaryUnreachable);
                    }
                }
            }, (err: any) => {
                this.notify.handleErrors(err);
            });
    }
}
@Component({
    templateUrl: "entities-administration-redirect-modal.component.html"
})
export class EntitiesAdministrationRedirectModalComponent {
    lang: any = LANG;

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<EntitiesAdministrationRedirectModalComponent>) {
        console.log(this.data.entity.redirectEntity);
    }

    setRedirectEntity(entity: any) {
        this.data.entity.redirectEntity = entity.id;
    }
}
