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
        entities: ['DSI'],
        editable: [true]
    };
    destUser: any = {
        item_firstname : 'Patricia',
        item_lastname : 'PETIT',
        item_entity : 'Pôle Jeunesse et Sport',
    };
    currentEntity : any = {
        'entity_label' : ''
    };
    redirectMode = '';

    @ViewChild('appDiffusionsList') appDiffusionsList: DiffusionsListComponent;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<RedirectActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void { }

    loadEntities() {
        this.redirectMode = 'entity';

        this.http.get("../../rest/entities")
            .subscribe((data: any) => {
                this.entities = data['entities'];
                this.loading = false;
                console.log(this.entities);
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
                            this.selectEntity();
                            this.appDiffusionsList.loadListModel('toto');
                            
                        }).on('deselect_node.jstree', (e: any, data: any) => {


                        })
                        // create the instance
                        .jstree();
                }, 0);
                setTimeout(() => {
                    $j('#jstree').jstree('select_node', this.entities[0]);
                    this.selectEntity();
                    
                }, 200);
                
            }, () => {
                location.href = "index.php";
            });
    }

    selectEntity() {
        const ind = this.entities.map((e:any) => { return e.entity_id; }).indexOf($j('#jstree').jstree(true).get_selected()[0]);
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
