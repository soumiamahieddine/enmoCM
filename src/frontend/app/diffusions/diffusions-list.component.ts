import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { CdkDragDrop, moveItemInArray, transferArrayItem, CdkDrag } from '@angular/cdk/drag-drop';
import { AutoCompletePlugin } from '../../plugins/autocomplete.plugin';

declare function $j(selector: any): any;

@Component({
    selector: 'app-diffusions-list',
    templateUrl: 'diffusions-list.component.html',
    styleUrls: ['diffusions-list.component.scss'],
    providers: [NotificationService]
})
export class DiffusionsListComponent extends AutoCompletePlugin implements OnInit {

    lang: any = LANG;
    listinstance: any = [];
    roles: any = [];
    loading: boolean = true;
    availableRoles: any[] = [];
    keepRoles: any[] = [];

    diffList: any = {};

    @Input('injectDatas') injectDatas: any;

    constructor(public http: HttpClient, private notify: NotificationService) {
        super(http, ['usersAndEntities']);
    }

    ngOnInit(): void {
        this.http.get("../../rest/listTemplates/types/entity_id/roles")
            .subscribe((data: any) => {
                data['roles'].forEach((element: any) => {
                    if (element.id == 'cc') {
                        element.id = 'copy';
                    }
                    if (element.available) {
                        this.availableRoles.push(element);
                        this.diffList[element.id] = {
                            'label': element.label,
                            'items': []
                        };
                    }
                    if (element.keepInListInstance) {
                        this.keepRoles.push(element.id);
                    }
                });
                /*if (this.injectDatas.entityId) {
                    this.loadListModel(this.injectDatas.entityId);
                } else if (this.injectDatas.resId > 0) {
                    this.loadListinstance(this.injectDatas.resId);
                }*/
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }

    drop(event: CdkDragDrop<string[]>) {
        if (event.previousContainer === event.container) {
            //moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        } else if (event.container.id != 'dest') {
            transferArrayItem(event.previousContainer.data,
                event.container.data,
                event.previousIndex,
                event.currentIndex);
        }
    }

    noReturnPredicate() {
        return false;
    }

    allPredicate() {
        return true;
    }

    loadListModel(entityId: number) {
        this.loading = true;

        this.availableRoles.forEach(element => {
            this.diffList[element.id].items = [];
        });

        this.http.get("../../rest/listTemplates/entities/" + entityId)
            .subscribe((data: any) => {
                data.listTemplate.forEach((element: any) => {
                    if (element.item_mode == 'cc') {
                        this.diffList['copy'].items.push(element);
                    } else {
                        this.diffList[element.item_mode].items.push(element);
                    }
                });
                if (this.keepRoles.length > 0 && this.injectDatas.resId > 0) {
                    this.injectListinstanceToKeep();
                } else {
                    this.loading = false;
                }
            });
    }

    loadListinstance(resId: number) {
        this.loading = true;
        this.availableRoles.forEach(element => {
            this.diffList[element.id].items = [];
        });
        this.http.get("../../rest/resources/" + resId + "/listInstance").subscribe((data: any) => {
            data.listInstance.forEach((element: any) => {
                if (element.item_mode == 'cc') {
                    this.diffList['copy'].items.push(element);
                } else {
                    this.diffList[element.item_mode].items.push(element);
                }
            });
            this.loading = false;
        }, (err: any) => {
            this.notify.handleErrors(err);
        });
    }

    injectListinstanceToKeep() {
        this.http.get("../../rest/resources/" + this.injectDatas.resId + "/listInstance").subscribe((data: any) => {
            data.listInstance.forEach((element: any) => {
                if (element.item_mode == 'cc') {
                    element.item_mode = 'copy';
                }
                if (this.keepRoles.indexOf(element.item_mode) > -1 && this.diffList[element.item_mode].items.map((e: any) => { return e.item_id; }).indexOf(element.item_id) == -1) {
                    this.diffList[element.item_mode].items.push(element);
                }
                if (this.injectDatas.keepInListinstance && element.item_mode == "dest" && this.diffList["copy"].items.map((e: any) => { return e.item_id; }).indexOf(element.item_id) == -1) {
                    this.diffList["copy"].items.push(element);
                }
            });
            this.loading = false;
        }, (err: any) => {
            this.notify.handleErrors(err);
        });
    }

    deleteItem(roleId: string, index: number) {
        this.diffList[roleId].items.splice(index, 1);
    }

    getListinstance() {
        let listInstanceFormatted: any = [];

        Object.keys(this.diffList).forEach(role => {
            if (this.diffList[role].items.length > 0) {
                this.diffList[role].items.forEach((element:any) => {
                    listInstanceFormatted.push({
                        difflist_type : element.difflist_type !== undefined ? element.difflist_type : element.object_type,
                        item_id : element.item_id,
                        item_mode : role == 'copy' ? 'cc' : role,
                        item_type : element.item_type,
                        process_date : element.process_date !== undefined ? element.process_date : null,
                        process_comment : element.process_comment,    
                    });
                });
            }
        });

        return listInstanceFormatted;
    }

    getDestUser() {
        if (this.diffList['dest']) {
            return this.diffList['dest'].items;
        } else {
            return false;
        }
    }

    addElem(element: any) {

        if (this.diffList["copy"].items.map((e: any) => { return e.item_id; }).indexOf(element.id) == -1) {
            let itemType = '';
            if (element.type == 'user') {
                itemType = 'user_id';
            } else {
                itemType = 'entity_id';
            }
    
            const newElemListModel = {
                difflist_type: "entity_id",
                item_type: itemType,
                item_id: element.id,
                labelToDisplay: element.idToDisplay,
                descriptionToDisplay: element.otherInfo,
                item_mode: "copy"
            };
            this.diffList['copy'].items.unshift(newElemListModel);
        }
        
        $j('.userDiffList').val('');
        $j('.userDiffList').blur();
        
    }
}