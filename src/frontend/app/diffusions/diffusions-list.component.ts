import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { CdkDragDrop, moveItemInArray, transferArrayItem } from '@angular/cdk/drag-drop';

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
                console.log(this.diffList);
                if (!this.injectDatas) {
                    this.loadListinstance(100);
                } else {
                    this.loadListModel('COU');
                    
                }
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }

    drop(event: CdkDragDrop<string[]>) {
        if (event.previousContainer === event.container) {
            //moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        } else if (event.container.id != 'dest') {
            console.log(event);
            transferArrayItem(event.previousContainer.data,
                event.container.data,
                event.previousIndex,
                event.currentIndex);
        }
    }

    loadListModel(entityId: string) {
        this.loading = true;

        this.availableRoles.forEach(element => {
            this.diffList[element.id].items = [];
        });

        // TO DO : ADD ROUTE
        /*this.http.get("../../rest/???")
            .subscribe((data: any) => {
                this.loading = false;
            });*/

        this.diffList['dest'].items.push(
            {
                "listinstance_id": 20,
                "sequence": 0,
                "item_mode": "dest",
                "item_id": "bbain",
                "item_type": "user_id",
                "item_firstname": "Barbara",
                "item_lastname": "BAIN",
                "item_entity": "P\u00f4le Jeunesse et Sport",
                "viewed": 0,
                "process_date": null,
                "process_comment": "",
                "signatory": false,
                "requested_signature": false
            });
        this.diffList['copy'].items.push(
            {
                "listinstance_id": 21,
                "sequence": 0,
                "item_mode": "copy",
                "item_id": "DSG",
                "item_type": "entity_id",
                "item_entity": "Secr\u00e9tariat G\u00e9n\u00e9ral",
                "viewed": 0,
                "process_date": null,
                "process_comment": null,
                "signatory": false,
                "requested_signature": false
            }
        );
        this.diffList['copy'].items.push(
            {
                "listinstance_id": 20,
                "sequence": 0,
                "item_mode": "copy",
                "item_id": "bboule",
                "item_type": "user_id",
                "item_firstname": "Bruno",
                "item_lastname": "Boule",
                "item_entity": "Archives",
                "viewed": 0,
                "process_date": null,
                "process_comment": "",
                "signatory": false,
                "requested_signature": false
            });
        this.loading = false;
        //this.roles = Object.keys(this.data);
        //this.listinstance = this.data;
        //this.loading = false;
    }

    loadListinstance(resId: number) {

    }

    deleteItem(roleId:string, index: number) {
        this.diffList[roleId].items.splice(index, 1);
    }
}