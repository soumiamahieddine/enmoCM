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
import { AddVisaModelModalComponent } from './addVisaModel/add-visa-model-modal.component';
import { ConfirmComponent } from '../../plugins/modal/confirm.component';

declare function $j(selector: any): any;

@Component({
    selector: 'app-visa-workflow',
    templateUrl: 'visa-workflow.component.html',
    styleUrls: ['visa-workflow.component.scss']
})
export class VisaWorkflowComponent implements OnInit {

    lang: any = LANG;
    visaWorkflow: any = {
        roles: ['sign', 'visa'],
        items: []
    };
    visaWorkflowClone: any = null;
    visaTemplates: any = {
        private: [],
        public: []
    };

    signVisaUsers: any = [];
    filteredSignVisaUsers: Observable<string[]>;
    filteredPublicModels: Observable<string[]>;
    filteredPrivateModels: Observable<string[]>;

    loading: boolean = false;
    visaModelListNotLoaded: boolean = true;
    data: any;

    @Input('injectDatas') injectDatas: any;
    @Input('adminMode') adminMode: boolean;
    @Input('resId') resId: number = null;

    @Input('linkedToMaarchParapheur') linkedToMaarchParapheur: boolean = false;

    @ViewChild('searchVisaSignUserInput', { static: false }) searchVisaSignUserInput: ElementRef;

    searchVisaSignUser = new FormControl();

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public functions: FunctionsService,
        private latinisePipe: LatinisePipe,
        public dialog: MatDialog
    ) { }

    ngOnInit(): void {
        if (this.resId !== null) {
            //this.initFilterVisaModelList();
            this.loadWorkflow(this.resId);
        }
    }

    drop(event: CdkDragDrop<string[]>) {
        if (event.previousContainer === event.container) {
            if (this.functions.empty(this.visaWorkflow.items[event.currentIndex].process_date)) {
                moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
            } else {
                this.notify.error(`${this.lang.moveVisaUserErr1} <b>${this.visaWorkflow.items[event.previousIndex].labelToDisplay}</b> ${this.lang.moveVisaUserErr2}.`);
            }
        }
    }

    loadListModel(entityId: number) {
        this.loading = true;

        this.visaWorkflow.items = [];

        let route = this.linkedToMaarchParapheur === true ? `../../rest/listTemplates/entities/${entityId}?type=visaCircuit&maarchParapheur=true` : `../../rest/listTemplates/entities/${entityId}?type=visaCircuit`;

        this.http.get(route)
            .subscribe((data: any) => {
                if (data.listTemplates[0]) {
                    this.visaWorkflow.items = data.listTemplates[0].items.map((item: any) => {
                        return {
                            ...item,
                            item_entity: item.descriptionToDisplay,
                            requested_signature: item.item_mode !== 'visa'
                        }
                    });
                    this.loading = false;
                }
                this.visaWorkflow.items.forEach((element: any, key: number) => {
                    if (!this.functions.empty(element['externalId'])) {
                        this.getMaarchParapheurUserAvatar(element.externalId.maarchParapheur, key);
                    }
                });
            });
    }

    loadVisaSignUsersList() {
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
                    this.signVisaUsers = data;
                    this.filteredSignVisaUsers = this.searchVisaSignUser.valueChanges
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

    async loadVisaModelList() {
        if (this.resId !== null) {
            await this.loadDefaultModel();
        }

        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/availableCircuits?circuit=visa`).pipe(
                tap((data: any) => {
                    this.visaTemplates.public = this.visaTemplates.public.concat(data.circuits.filter((item: any) => !item.private).map((item: any) => {
                        return {
                            id: item.id,
                            title: item.title,
                            label: item.title,
                            type: 'entity'
                        }
                    }));

                    this.visaTemplates.private = data.circuits.filter((item: any) => item.private).map((item: any) => {
                        return {
                            id: item.id,
                            title: item.title,
                            label: item.title,
                            type: 'entity'
                        }
                    });
                    this.filteredPublicModels = this.searchVisaSignUser.valueChanges
                        .pipe(
                            startWith(''),
                            map(value => this._filterPublicModel(value))
                        );
                    this.filteredPrivateModels = this.searchVisaSignUser.valueChanges
                        .pipe(
                            startWith(''),
                            map(value => this._filterPrivateModel(value))
                        );
                    resolve(true);
                })
            ).subscribe();
        });
    }

    loadDefaultModel() {
        this.visaTemplates.public = [];

        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/resources/${this.resId}/defaultCircuit?circuit=visa`).pipe(
                filter((data: any) => !this.functions.empty(data.circuit)),
                tap((data: any) => {
                    if (!this.functions.empty(data.circuit)) {
                        this.visaTemplates.public.push({
                            id: data.circuit.id,
                            title: data.circuit.title,
                            label: data.circuit.title,
                            type: 'entity'
                        });
                    }
                }),
                finalize(() => resolve(true)),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    async initFilterVisaModelList() {
        if (!this.linkedToMaarchParapheur) {
            if (this.visaModelListNotLoaded) {
                await this.loadVisaSignUsersList();

                await this.loadVisaModelList();
                
                this.searchVisaSignUser.reset();

                this.visaModelListNotLoaded = false;
            }
        }
    }

    private _filter(value: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.signVisaUsers.filter((option: any) => this.latinisePipe.transform(option['title'].toLowerCase()).includes(filterValue));
        } else {
            return this.signVisaUsers;
        }
    }

    private _filterPrivateModel(value: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.visaTemplates.private.filter((option: any) => this.latinisePipe.transform(option['title'].toLowerCase()).includes(filterValue));
        } else {
            return this.visaTemplates.private;
        }
    }

    private _filterPublicModel(value: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.visaTemplates.public.filter((option: any) => this.latinisePipe.transform(option['title'].toLowerCase()).includes(filterValue));
        } else {
            return this.visaTemplates.public;
        }
    }

    loadWorkflow(resId: number) {
        this.resId = resId;
        this.loading = true;
        this.visaWorkflow.items = [];
        return new Promise((resolve, reject) => {
            this.http.get("../../rest/resources/" + resId + "/visaCircuit")
                .subscribe((data: any) => {
                    data.forEach((element: any) => {
                        this.visaWorkflow.items.push(
                            {
                                ...element,
                                difflist_type: 'VISA_CIRCUIT'
                            });
                    });
                    this.visaWorkflowClone = JSON.parse(JSON.stringify(this.visaWorkflow.items))
                    this.loading = false;
                    resolve(true);
                }, (err: any) => {
                    this.notify.handleErrors(err);
                });
        });
    }

    loadDefaultWorkflow(resId: number) {
        this.loading = true;
        this.visaWorkflow.items = [];
        this.http.get("../../rest/resources/" + resId + "/defaultCircuit?circuit=visaCircuit").pipe(
            filter((data: any) => !this.functions.empty(data.circuit)),
            tap((data: any) => {
                data.circuit.items.forEach((element: any) => {
                    this.visaWorkflow.items.push(
                        {
                            ...element,
                            difflist_type: 'VISA_CIRCUIT'
                        });
                });
                this.visaWorkflowClone = JSON.parse(JSON.stringify(this.visaWorkflow.items))
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    loadWorkflowMaarchParapheur(attachmentId: number) {
        this.loading = true;
        this.visaWorkflow.items = [];
        this.http.get(`../../rest/attachments/${attachmentId}/maarchParapheurWorkflow`)
            .subscribe((data: any) => {
                data.workflow.forEach((element: any) => {
                    const user = {
                        'id': element.userId,
                        'labelToDisplay': element.userDisplay,
                        'requested_signature': element.mode === 'visa' ? false : true,
                        'process_date': this.functions.formatFrenchDateToTechnicalDate(element.processDate),
                        'picture': ''
                    }
                    this.visaWorkflow.items.push(user);
                    this.http.get("../../rest/maarchParapheur/user/" + element.userId + "/picture")
                        .subscribe((data: any) => {
                            this.visaWorkflow.items.filter((item: any) => item.id === element.userId)[0].picture = data.picture;
                        }, (err: any) => {
                            this.notify.handleErrors(err);
                        });
                });
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });
    }

    deleteItem(index: number) {
        this.visaWorkflow.items.splice(index, 1);
    }

    getVisaCount() {
        return this.visaWorkflow.items.length;
    }

    changeRole(i: number) {
        this.visaWorkflow.items[i].requested_signature = !this.visaWorkflow.items[i].requested_signature;
    }

    getWorkflow() {
        return this.visaWorkflow.items;
    }

    getCurrentVisaUserIndex() {

        const index = this.visaWorkflow.items.map((item: any) => item.listinstance_id).indexOf(this.getLastVisaUser().listinstance_id);

        return (index + 1);
    }

    getFirstVisaUser() {
        return !this.functions.empty(this.visaWorkflow.items[0]) ? this.visaWorkflow.items[0] : '';
    }

    getCurrentVisaUser() {

        const index = this.visaWorkflow.items.map((item: any) => item.listinstance_id).indexOf(this.getLastVisaUser().listinstance_id);

        return !this.functions.empty(this.visaWorkflow.items[index + 1]) ? this.visaWorkflow.items[index + 1] : '';
    }

    getNextVisaUser() {

        const index = this.visaWorkflow.items.map((item: any) => item.listinstance_id).indexOf(this.getLastVisaUser().listinstance_id);

        return !this.functions.empty(this.visaWorkflow.items[index + 2]) ? this.visaWorkflow.items[index + 2] : '';
    }

    getLastVisaUser() {
        let arrOnlyProcess = this.visaWorkflow.items.filter((item: any) => !this.functions.empty(item.process_date));

        return !this.functions.empty(arrOnlyProcess[arrOnlyProcess.length - 1]) ? arrOnlyProcess[arrOnlyProcess.length - 1] : '';
    }

    checkExternalSignatoryBook() {
        return this.visaWorkflow.items.filter((item: any) => this.functions.empty(item.externalId)).map((item: any) => item.labelToDisplay);
    }

    saveVisaWorkflow(resIds: number[] = [this.resId]) {
        return new Promise((resolve, reject) => {
            if (this.visaWorkflow.items.length === 0) {
                this.http.delete(`../../rest/resources/${resIds[0]}/circuits/visaCircuit`).pipe(
                    tap(() => {
                        this.visaWorkflowClone = JSON.parse(JSON.stringify(this.visaWorkflow.items));
                        this.notify.success(this.lang.visaWorkflowDeleted);
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notify.handleSoftErrors(err);
                        return of(false);
                    })
                ).subscribe();
            } else if (this.isValidWorkflow()) {
                const arrVisa = resIds.map(resId => {
                    return {
                        resId: resId,
                        listInstances: this.visaWorkflow.items
                    }
                });
                this.http.put(`../../rest/circuits/visaCircuit`, { resources: arrVisa }).pipe(
                    tap((data: any) => {
                        this.visaWorkflowClone = JSON.parse(JSON.stringify(this.visaWorkflow.items));
                        this.notify.success(this.lang.visaWorkflowUpdated);
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notify.handleSoftErrors(err);
                        return of(false);
                    })
                ).subscribe();
            } else {
                this.notify.error(this.lang.signUserRequired);
                resolve(false);
            }
        });
    }

    addItemToWorkflow(item: any, maarchParapheurMode = false) {
        if (maarchParapheurMode) {
            this.visaWorkflow.items.push({
                item_id: item.id,
                item_type: 'user',
                item_entity: item.email,
                labelToDisplay: item.idToDisplay,
                externalId: item.externalId,
                difflist_type: 'VISA_CIRCUIT',
                signatory: false,
                requested_signature: false
            });
            if (this.linkedToMaarchParapheur) {
                this.getMaarchParapheurUserAvatar(item.externalId.maarchParapheur, this.visaWorkflow.items.length - 1);
            }
            this.searchVisaSignUser.reset();
            this.searchVisaSignUserInput.nativeElement.blur();
        } else if (item.type === 'user') {


            this.visaWorkflow.items.push({
                item_id: item.id,
                item_type: 'user',
                item_entity: item.entity,
                labelToDisplay: item.label,
                externalId: !this.functions.empty(item.externalId) ? item.externalId : null,
                difflist_type: 'VISA_CIRCUIT',
                signatory: false,
                requested_signature: false
            });

            if (this.linkedToMaarchParapheur) {
                this.getMaarchParapheurUserAvatar(item.externalId.maarchParapheur, this.visaWorkflow.items.length - 1);
            }
            this.searchVisaSignUser.reset();
            this.searchVisaSignUserInput.nativeElement.blur();
        } else if (item.type === 'entity') {
            this.http.get(`../../rest/listTemplates/${item.id}`).pipe(
                tap((data: any) => {
                    this.visaWorkflow.items = this.visaWorkflow.items.concat(
                        data.listTemplate.items.map((itemTemplate: any) => {
                            return {
                                item_id: itemTemplate.item_id,
                                item_type: 'user',
                                labelToDisplay: itemTemplate.idToDisplay,
                                item_entity: itemTemplate.descriptionToDisplay,
                                difflist_type: 'VISA_CIRCUIT',
                                signatory: false,
                                requested_signature: itemTemplate.item_mode === 'sign' ? true : false
                            }
                        })
                    );
                    this.searchVisaSignUser.reset();
                    this.searchVisaSignUserInput.nativeElement.blur();
                })
            ).subscribe();
        }
    }

    isValidWorkflow() {
        if (this.visaWorkflow.items.filter((item: any) => item.requested_signature).length > 0 || this.visaWorkflow.items.length === 0) {
            return true;
        } else {
            return false;
        }
    }

    emptyWorkflow() {
        if (this.visaWorkflow.items.length === 0) {
            return true;
        } else {
            return false;
        }
    }

    workflowEnd() {
        if (this.visaWorkflow.items.filter((item: any) => !this.functions.empty(item.process_date)).length === this.visaWorkflow.items.length) {
            return true;
        } else {
            return false;
        }
    }

    openPromptSaveModel() {
        const dialogRef = this.dialog.open(AddVisaModelModalComponent, { data: { visaWorkflow: this.visaWorkflow.items } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => !this.functions.empty(data)),

            tap((data: any) => {
                this.visaTemplates.private.push({
                    id: data.id,
                    title: data.title,
                    label: data.title,
                    type: 'entity'
                });
                this.searchVisaSignUser.reset();
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
                this.visaTemplates.private = this.visaTemplates.private.filter((template: any) => template.id !== model.id);
                this.searchVisaSignUser.reset();
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
                    this.visaWorkflow.items[key].picture = data.picture;
                }, (err: any) => {
                    this.notify.handleErrors(err);
                });
        }
    }

    isModified() {
        if (this.loading || JSON.stringify(this.visaWorkflow.items) === JSON.stringify(this.visaWorkflowClone)) {
            return false;
        } else {
            return true;
        }
    }
}
