import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { DiffusionsListComponent } from '../../diffusions/diffusions-list.component';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { startWith } from 'rxjs/internal/operators/startWith';
import { map } from 'rxjs/operators';

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
        keepDestForRedirection: false
    };
    destUser: any = null;
    oldUser: any = null;
    keepDestForRedirection: boolean = false;
    diffusionListDestRedirect: any = null;
    currentEntity: any = {
        'serialId': 0,
        'entity_label': ''
    };
    redirectMode = '';
    userListRedirect: any[] = [];
    userRedirectCtrl = new FormControl();
    filteredUserRedirect: Observable<any[]>;

    @ViewChild('appDiffusionsList') appDiffusionsList: DiffusionsListComponent;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<RedirectActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        let noEntity = true;
        this.loading = true;
        this.http.get("../../rest/resourcesList/users/" + this.data.currentBasketInfo.ownerId + "/groups/" + this.data.currentBasketInfo.groupId + "/baskets/" + this.data.currentBasketInfo.basketId + "/actions/" + this.data.action.id + "/getRedirect")
            .subscribe((data: any) => {
                console.log(data);
                this.entities = data['entities'];
                this.userListRedirect = data.users;
                this.keepDestForRedirection = data.keepDestForRedirection;

                this.entities.forEach(entity => {
                    if (entity.state.selected) {
                        this.currentEntity = entity;
                    }
                    if (entity.allowed) {
                        noEntity = false;
                    }
                });

                if (this.userListRedirect.length == 0 && noEntity) {
                    this.redirectMode = 'none';
                    this.loading = false;
                } else if (this.userListRedirect.length == 0 && !noEntity) {
                    this.loadEntities();
                } else if (this.userListRedirect.length > 0 && noEntity) {
                    this.loadDestUser();
                } else {
                    this.loading = false;
                }

            }, () => {
                location.href = "index.php";
            });
    }

    loadEntities() {
        this.redirectMode = 'entity';
        if (this.data.selectedRes.length == 1) {
            this.injectDatasParam.resId = this.data.selectedRes[0];
        }
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
                    this.selectEntity(data.node.original);

                }).on('deselect_node.jstree', (e: any, data: any) => {
                    this.currentEntity = {
                        'serialId': 0,
                        'entity_label': ''
                    };
                })
                // create the instance
                .jstree();
        }, 0);
        setTimeout(() => {
            $j('#jstree').jstree('select_node', this.currentEntity);
            this.selectEntity(this.currentEntity);

        }, 200);
    }

    loadDestUser() {
        this.redirectMode = 'user';
        this.loading = true;
        this.filteredUserRedirect = this.userRedirectCtrl.valueChanges
            .pipe(
                startWith(''),
                map(user => user ? this._filterUserRedirect(user) : this.userListRedirect.slice())
            );
        this.http.get("../../rest/resources/" + this.data.selectedRes[0] + "/listInstance").subscribe((data: any) => {
            this.diffusionListDestRedirect = data.listInstance;
            Object.keys(data).forEach(diffusionRole => {
                data[diffusionRole].forEach((line: any) => {
                    if (line.item_mode == 'dest') {
                        this.oldUser = line;
                    }
                });
            });
            this.loading = false;
            $j('.searchUserRedirect').click();
        }, (err: any) => {
            this.notify.handleErrors(err);
        });
    }

    changeDest(event: any) {
        let user = event.option.value;
        this.destUser = {
            difflist_type: "entity_id",
            item_mode: "dest",
            item_type: "user_id",
            item_id: "aackermann",
            labelToDisplay: user.labelToDisplay,
            descriptionToDisplay: user.descriptionToDisplay
        };
        this.diffusionListDestRedirect.splice(this.diffusionListDestRedirect.map((e: any) => { return e.item_mode; }).indexOf('dest'), 1);
        this.diffusionListDestRedirect.push(this.destUser)

        this.userRedirectCtrl.reset();
        $j('.searchUserRedirect').blur();
    }

    private _filterUserRedirect(value: string): any[] {
        if (typeof value === 'string') {
            const filterValue = value.toLowerCase();
            return this.userListRedirect.filter(user => user.labelToDisplay.toLowerCase().indexOf(filterValue) >= 0);
        }
    }

    selectEntity(entity: any) {
        this.currentEntity = entity;
        this.appDiffusionsList.loadListModel(entity.serialId);
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

    checkValidity() {
        if (this.redirectMode == 'entity' && this.appDiffusionsList && this.appDiffusionsList.getDestUser().length > 0 && this.currentEntity.serialId > 0 && !this.loading) {
            return false;
        } if (this.redirectMode == 'user' && this.diffusionListDestRedirect != null && this.destUser != null && !this.loading) {
            return false;
        } else {
            return true;
        }
    }
}
