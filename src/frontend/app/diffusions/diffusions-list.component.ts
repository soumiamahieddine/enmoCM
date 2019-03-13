import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { CdkDragDrop, moveItemInArray, transferArrayItem, CdkDrag } from '@angular/cdk/drag-drop';

@Component({
    selector: 'app-diffusions-list',
    templateUrl: 'diffusions-list.component.html',
    styleUrls: ['diffusions-list.component.scss'],
    providers: [NotificationService]
})
export class DiffusionsListComponent implements OnInit {

    lang: any = LANG;
    listinstance: any = [];
    visaCircuit: any = [];
    avisCircuit: any = [];
    roles: any = [];
    loading: boolean = true;
    tabVisaCircuit: boolean = false;
    tabAvisCircuit: boolean = false;
    data: any;
    availableRoles: any[] = [];

    diffList: any = {};

    @Input('injectDatas') injectDatas: any;

    constructor(public http: HttpClient, private notify: NotificationService) { }

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
                });
                if (this.injectDatas.resId > 0) {
                    this.loadListinstance(this.injectDatas.resId);
                } else if (this.injectDatas.entityId) {
                    this.loadListModel(this.injectDatas.entityId);
                }
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

        // TO DO : ADD ROUTE
        this.http.get("../../rest/listTemplates/entities/" + entityId)
            .subscribe((data: any) => {
                data.listTemplate.forEach((element: any) => {
                    console.log(element);
                    if (element.item_mode == 'cc') {
                        this.diffList['copy'].items.push(element);
                    } else {
                        this.diffList[element.item_mode].items.push(element);
                    }
                });
                this.loading = false;
            });
    }

    loadListinstance(resId: number) {
        this.loading = true;
        this.http.get("../../rest/res/" + resId + "/listinstance").subscribe((data: any) => {
            this.availableRoles.forEach(element => {
                this.diffList[element.id].items = [];
            });
            Object.keys(data).forEach(diffusionRole => {
                data[diffusionRole].forEach((line: any) => {
                    this.diffList[line.item_mode].items.push(line);
                });
            });
            this.loading = false;
        }, (err: any) => {
            this.notify.handleErrors(err);
        });
    }

    deleteItem(roleId: string, index: number) {
        this.diffList[roleId].items.splice(index, 1);
    }
}