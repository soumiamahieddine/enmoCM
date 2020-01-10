import { Component, Input, OnInit, ElementRef, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { FunctionsService } from '../../service/functions.service';
import { tap, exhaustMap, map, startWith, catchError, finalize, filter, debounceTime, switchMap } from 'rxjs/operators';
import { FormControl } from '@angular/forms';
import { LatinisePipe } from 'ngx-pipes';
import { Observable, of } from 'rxjs';
import { MatDialog } from '@angular/material';
import { AddAvisModelModalComponent } from './addAvisModel/add-avis-model-modal.component';
import { ConfirmComponent } from '../../plugins/modal/confirm.component';

declare function $j(selector: any): any;

@Component({
    selector: 'app-avis-workflow',
    templateUrl: 'avis-workflow.component.html',
    styleUrls: ['avis-workflow.component.scss']
})
export class AvisWorkflowComponent implements OnInit {

    lang: any = LANG;
    avisWorkflow: any = {
        roles: ['sign', 'avis'],
        items: []
    };
    avisWorkflowClone: any = null;
    avisTemplates: any = {
        private: [],
        public: []
    };

    signAvisUsers: any = [];
    filteredSignAvisUsers: Observable<string[]>;
    filteredPublicModels: Observable<string[]>;
    filteredPrivateModels: Observable<string[]>;

    loading: boolean = false;
    avisModelListNotLoaded: boolean = true;
    data: any;

    @Input('injectDatas') injectDatas: any;
    @Input('adminMode') adminMode: boolean;
    @Input('resId') resId: number = null;

    @ViewChild('searchAvisSignUserInput', { static: true }) searchAvisSignUserInput: ElementRef;

    searchAvisSignUser = new FormControl();

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public functions: FunctionsService,
        private latinisePipe: LatinisePipe,
        public dialog: MatDialog
    ) { }

    ngOnInit(): void {
        if (this.resId !== null) {
            this.loadWorkflow(this.resId);
        }
    }

    drop(event: CdkDragDrop<string[]>) {
        if (event.previousContainer === event.container) {
            if (this.functions.empty(this.avisWorkflow.items[event.currentIndex].process_date)) {
                moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
            } else {
                this.notify.error(`${this.lang.moveAvisUserErr1} <b>${this.avisWorkflow.items[event.previousIndex].labelToDisplay}</b> ${this.lang.moveAvisUserErr2}.`);
            }
        }
    }

    loadListModel(entityId: number) {
        this.loading = true;

        this.avisWorkflow.items = [];

        this.http.get(`../../rest/listTemplates/entities/${entityId}?type=opinionCircuit`)
            .subscribe((data: any) => {
                if (data.listTemplates[0]) {
                    this.avisWorkflow.items = data.listTemplates[0].items.map((item: any) => {
                        return {
                            ...item,
                            item_entity: item.descriptionToDisplay,
                        }
                    });
                    this.loading = false;
                }
            });
    }

    loadAvisSignUsersList() {
        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/autocomplete/users/circuit`).pipe(
                map((data: any) => {
                    data = data.map((user: any) => {
                        return {
                            id: user.id,
                            title: `${user.idToDisplay} (${user.otherInfo})`,
                            label: user.idToDisplay,
                            entity: user.otherInfo,
                            type: 'user'
                        }
                    });
                    return data;
                }),
                tap((data) => {
                    this.signAvisUsers = data;
                    this.filteredSignAvisUsers = this.searchAvisSignUser.valueChanges
                        .pipe(
                            startWith(''),
                            map(value => this._filter(value))
                        );
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    loadAvisModelListByResource() {
        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/resources/${this.resId}/availableCircuits?circuit=opinion`).pipe(
                tap((data: any) => {
                    this.avisTemplates.public = data.circuits.filter((item: any) => !item.private).map((item: any) => {
                        return {
                            id: item.id,
                            title: item.title,
                            label: item.title,
                            type: 'entity'
                        }
                    });

                    this.avisTemplates.private = data.circuits.filter((item: any) => item.private).map((item: any) => {
                        return {
                            id: item.id,
                            title: item.title,
                            label: item.title,
                            type: 'entity'
                        }
                    });
                    this.filteredPublicModels = this.searchAvisSignUser.valueChanges
                        .pipe(
                            startWith(''),
                            map(value => this._filterPublicModel(value))
                        );
                    this.filteredPrivateModels = this.searchAvisSignUser.valueChanges
                        .pipe(
                            startWith(''),
                            map(value => this._filterPrivateModel(value))
                        );
                    resolve(true);
                }),
            ).subscribe();
        });
    }

    async initFilterAvisModelList() {
        if (this.avisModelListNotLoaded) {
            await this.loadAvisSignUsersList();

            await this.loadAvisModelListByResource();

            this.searchAvisSignUser.reset();

            this.avisModelListNotLoaded = false;
        }
    }

    private _filter(value: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.signAvisUsers.filter((option: any) => this.latinisePipe.transform(option['title'].toLowerCase()).includes(filterValue));
        } else {
            return this.signAvisUsers;
        }
    }

    private _filterPrivateModel(value: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.avisTemplates.private.filter((option: any) => this.latinisePipe.transform(option['title'].toLowerCase()).includes(filterValue));
        } else {
            return this.avisTemplates.private;
        }
    }

    private _filterPublicModel(value: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.avisTemplates.public.filter((option: any) => this.latinisePipe.transform(option['title'].toLowerCase()).includes(filterValue));
        } else {
            return this.avisTemplates.public;
        }
    }

    loadWorkflow(resId: number) {
        this.loading = true;
        this.avisWorkflow.items = [];
        this.http.get("../../rest/resources/" + resId + "/opinionCircuit")
            .subscribe((data: any) => {
                data.forEach((element: any) => {
                    this.avisWorkflow.items.push(
                        {
                            ...element,
                            difflist_type: 'AVIS_CIRCUIT'
                        });
                });
                this.avisWorkflowClone = JSON.parse(JSON.stringify(this.avisWorkflow.items))
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });
    }

    deleteItem(index: number) {
        this.avisWorkflow.items.splice(index, 1);
    }

    getAvisCount() {
        return this.avisWorkflow.items.length;
    }

    changeRole(i: number) {
        this.avisWorkflow.items[i].requested_signature = !this.avisWorkflow.items[i].requested_signature;
    }

    getWorkflow() {
        return this.avisWorkflow.items;
    }

    saveAvisWorkflow() {
        this.http.put(`../../rest/listinstances`, [{ resId: this.resId, listInstances: this.avisWorkflow.items }]).pipe(
            tap((data: any) => {
                this.avisWorkflowClone = JSON.parse(JSON.stringify(this.avisWorkflow.items));
                this.notify.success(this.lang.avisWorkflowUpdated);
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    addItemToWorkflow(item: any) {
        if (item.type === 'user') {
            this.avisWorkflow.items.push({
                item_id: item.id,
                item_type: 'user',
                item_entity: item.entity,
                labelToDisplay: item.label,
                externalId: !this.functions.empty(item.externalId) ? item.externalId : null,
                difflist_type: 'AVIS_CIRCUIT',
                signatory: false,
                requested_signature: false
            });
            this.searchAvisSignUser.reset();
        } else if (item.type === 'entity') {
            this.http.get(`../../rest/listTemplates/${item.id}`).pipe(
                tap((data: any) => {
                    this.avisWorkflow.items = this.avisWorkflow.items.concat(
                        data.listTemplate.items.map((itemTemplate: any) => {
                            return {
                                item_id: itemTemplate.item_id,
                                item_type: 'user',
                                labelToDisplay: itemTemplate.idToDisplay,
                                item_entity: itemTemplate.descriptionToDisplay,
                                difflist_type: 'AVIS_CIRCUIT',
                                signatory: false,
                                requested_signature: false
                            }
                        })
                    );
                    this.searchAvisSignUser.reset();
                })
            ).subscribe();
        }
    }

    openPromptSaveModel() {
        const dialogRef = this.dialog.open(AddAvisModelModalComponent, { data: { avisWorkflow: this.avisWorkflow.items } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => !this.functions.empty(data)),

            tap((data: any) => {
                this.avisTemplates.private.push({
                    id: data.id,
                    title: data.title,
                    label: data.title,
                    type: 'entity'
                });
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    deletePrivateModel(model: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete, msg: this.lang.confirmAction } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.delete(`../../rest/listTemplates/${model.id}`)),
            tap(() => {
                this.avisTemplates.private = this.avisTemplates.private.filter((template: any) => template.id !== model.id);
                this.searchAvisSignUser.reset();
                this.notify.success(this.lang.modelDeleted);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    getMaarchParapheurUserAvatar(externalId: string, key: number) {
        if (!this.functions.empty(externalId)) {
            this.http.get("../../rest/maarchParapheur/user/" + externalId + "/picture")
                .subscribe((data: any) => {
                    this.avisWorkflow.items[key].picture = data.picture;
                }, (err: any) => {
                    this.notify.handleErrors(err);
                });
        }
    }

    isModified() {
        if (this.loading || JSON.stringify(this.avisWorkflow.items) === JSON.stringify(this.avisWorkflowClone)) {
            return false;
        } else {
            return true;
        }
    }
}
