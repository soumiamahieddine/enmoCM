import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { DiffusionsListComponent } from '../../diffusions/diffusions-list.component';

declare function $j(selector: any): any;

@Component({
    templateUrl: "redirect-action.component.html",
    styleUrls: ['redirect-action.component.scss'],
    providers: [NotificationService],
})
export class RedirectActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    entities: any[] = [];
    injectDatasParam = {
        resId: 0,
        editable: true,
        keepRoles: ['copy', 'avis'],
    };
    destUser: any = null;
    currentEntity: any = {
        'entity_label': ''
    };
    redirectMode = '';

    @ViewChild('appDiffusionsList') appDiffusionsList: DiffusionsListComponent;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<RedirectActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void { }

    loadEntities() {
        this.redirectMode = 'entity';
        if (this.data.selectedRes.length == 1) {
            this.injectDatasParam.resId = this.data.selectedRes[0];
        }
        this.http.get("../../rest/resourcesList/users/" + this.data.currentBasketInfo.ownerId + "/groups/" + this.data.currentBasketInfo.groupId + "/baskets/" + this.data.currentBasketInfo.basketId + "/actions/" + this.data.action.id + "/getRedirect")
            .subscribe((data: any) => {

                this.entities = data['entities'];

                this.entities.forEach(entity => {
                    if (entity.entity_id == 'COU') {
                        //if (entity.state.selected) {
                        this.currentEntity = entity;
                    }
                });
                this.loading = false;
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
                        },
                        "plugins": ["checkbox", "search", "sort"]
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
                            this.selectEntity();
                            this.appDiffusionsList.loadListModel(this.currentEntity.serialId);

                        }).on('deselect_node.jstree', (e: any, data: any) => {


                        })
                        // create the instance
                        .jstree();
                }, 0);
                setTimeout(() => {
                    $j('#jstree').jstree('select_node', this.currentEntity);
                    this.selectEntity();

                }, 200);

            }, () => {
                location.href = "index.php";
            });
    }

    loadDestUser() {
        this.redirectMode = 'user';
        this.loading = true;
        this.http.get("../../rest/res/" + this.data.selectedRes[0] + "/listinstance").subscribe((data: any) => {
            Object.keys(data).forEach(diffusionRole => {
                data[diffusionRole].forEach((line: any) => {
                    if (line.item_mode == 'dest') {
                        this.destUser = line;
                    }
                });
            });
            console.log(this.destUser);
            this.loading = false;
        }, (err: any) => {
            this.notify.handleErrors(err);
        });
    }

    selectEntity() {
        const ind = this.entities.map((e: any) => { return e.entity_id; }).indexOf($j('#jstree').jstree(true).get_selected()[0]);
        this.currentEntity = this.entities[ind];
    }

    onSubmit(): void {
        this.loading = true;
        /*this.http.put('../../rest/resourcesList/users/' + this.data.currentBasketInfo.ownerId + '/groups/' + this.data.currentBasketInfo.groupId + '/baskets/' + this.data.currentBasketInfo.basketId + '/actions/' + this.data.action.id, {resources : this.data.selectedRes})
            .subscribe((data: any) => {
                this.loading = false;
                this.dialogRef.close('success');
            }, (err: any) => {
                this.notify.handleErrors(err);
                this.loading = false;
            });*/
    }
}
