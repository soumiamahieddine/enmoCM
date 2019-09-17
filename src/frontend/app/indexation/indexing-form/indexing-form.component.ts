import { Component, OnInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService } from '../../../service/header.service';
import { MatDialog } from '@angular/material/dialog';
import { AppService } from '../../../service/app.service';
import { tap, catchError, finalize, exhaustMap } from 'rxjs/operators';
import { of } from 'rxjs';
import { SortPipe } from '../../../plugins/sorting.pipe';
import { CdkDragDrop, moveItemInArray, transferArrayItem } from '@angular/cdk/drag-drop';

@Component({
    selector: 'app-indexing-form',
    templateUrl: "indexing-form.component.html",
    styleUrls: ['indexing-form.component.scss'],
    providers: [NotificationService, AppService, SortPipe]
})

export class IndexingFormComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = true;

    @Input('indexingFormId') indexingFormId: number;

    fieldCategories: any[] = ['mail', 'contact', 'process', 'classement'];

    indexingModelsCore: any[] = [
        {
            identifier: 'category_id',
            label: this.lang.category_id,
            unit: 'mail',
            type: 'select',
            system: true,
            mandatory: true,
            values: []
        },
        {
            identifier: 'doctype',
            label: this.lang.doctype,
            unit: 'mail',
            type: 'select',
            system: true,
            mandatory: true,
            values: []
        },
        {
            identifier: 'docDate',
            label: this.lang.docDate,
            unit: 'mail',
            type: 'date',
            system: true,
            mandatory: true,
            values: []
        },
        {
            identifier: 'arrivalDate',
            label: this.lang.arrivalDate,
            unit: 'mail',
            type: 'date',
            system: true,
            mandatory: true,
            values: []
        },
        {
            identifier: 'subject',
            label: this.lang.subject,
            unit: 'mail',
            type: 'string',
            system: true,
            mandatory: true,
            values: []
        },
        {
            identifier: 'contact',
            label: this.lang.getSenders,
            unit: 'contact',
            type: 'string',
            system: true,
            mandatory: true,
            values: []
        },
        {
            identifier: 'destination',
            label: this.lang.destination,
            unit: 'process',
            type: 'select',
            system: true,
            mandatory: true,
            values: []
        },
        {
            identifier: 'folder',
            label: this.lang.folder,
            unit: 'classement',
            type: 'string',
            system: true,
            mandatory: true,
            values: []
        }
    ];

    indexingModels_mail: any[] = [];
    indexingModels_contact: any[] = [];
    indexingModels_process: any[] = [];
    indexingModels_classement: any[] = [];

    indexingModels_mailClone: any[] = [];
    indexingModels_contactClone: any[] = [];
    indexingModels_processClone: any[] = [];
    indexingModels_classementClone: any[] = [];

    indexingModelsCustomFields: any[] = [];

    availableFields: any[] = [
        {
            identifier: 'recipient',
            label: this.lang.getRecipients,
            type: 'string',
            values: []
        },
        {
            identifier: 'priority',
            label: this.lang.priority,
            type: 'select',
            values: []
        },
        {
            identifier: 'confidential',
            label: this.lang.confidential,
            type: 'radio',
            values: ['Oui', 'Non']
        },
        {
            identifier: 'initiator',
            label: this.lang.initiator,
            type: 'select',
            values: []
        },
        {
            identifier: 'processLimitDate',
            label: this.lang.processLimitDate,
            type: 'date',
            values: []
        }
    ];

    availableCustomFields: any[] = []

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public dialog: MatDialog,
        private headerService: HeaderService,
        public appService: AppService,
    ) {

    }

    ngOnInit(): void {

        this.fieldCategories.forEach(category => {
            this['indexingModels_' + category] = [];
        });

        if (this.indexingFormId <= 0) {
            this.http.get("../../rest/customFields").pipe(
                tap((data: any) => {
                    this.availableCustomFields = data.customFields.map((info: any) => {
                        info.identifier = 'indexingCustomField_' + info.id;
                        info.system = false;
                        return info;
                    });
                    this.fieldCategories.forEach(element => {
                        this['indexingModels_' + element] = this.indexingModelsCore.filter((x: any, i: any, a: any) => x.unit === element);
                    });
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.http.get("../../rest/customFields").pipe(
                tap((data: any) => {
                    this.availableCustomFields = data.customFields.map((info: any) => {
                        info.identifier = 'indexingCustomField_' + info.id;
                        info.system = false;
                        return info;
                    });
                }),
                exhaustMap((data) => this.http.get("../../rest/indexingModels/" + this.indexingFormId)),
                tap((data: any) => {
                    let fieldExist: boolean;
                    if (data.indexingModel.fields.length === 0) {
                        this.fieldCategories.forEach(element => {
                            this['indexingModels_' + element] = this.indexingModelsCore.filter((x: any, i: any, a: any) => x.unit === element);
                        });
                        this.notify.error("Champs introuvables! les données de base ont été chargés");
                    } else {
                        data.indexingModel.fields.forEach((field: any) => {
                            fieldExist = false;
                            field.label = this.lang[field.identifier];
                            field.system = false;
                            field.values = [];

                            let indexFound = this.availableFields.map(avField => avField.identifier).indexOf(field.identifier);

                            if (indexFound > -1) {
                                field.label = this.availableFields[indexFound].label;
                                field.values = this.availableFields[indexFound].values;
                                field.type = this.availableFields[indexFound].type;
                                this.availableFields.splice(indexFound, 1);
                                fieldExist = true;
                            }

                            indexFound = this.availableCustomFields.map(avField => avField.identifier).indexOf(field.identifier);

                            if (indexFound > -1) {
                                field.label = this.availableCustomFields[indexFound].label;
                                field.values = this.availableCustomFields[indexFound].values;
                                field.type = this.availableCustomFields[indexFound].type;
                                this.availableCustomFields.splice(indexFound, 1);
                                fieldExist = true;
                            }
                            if (this.indexingModelsCore.map(info => info.identifier).indexOf(field.identifier) > -1) {
                                fieldExist = true;
                                field.system = true;
                            }

                            if (fieldExist) {
                                this['indexingModels_' + field.unit].push(field);
                            } else {
                                this.notify.error("Le champ " + field.identifier + " n'existe pas !");
                            }

                        });
                    }
                    this.fieldCategories.forEach(element => {
                        this['indexingModels_' + element + 'Clone'] = JSON.parse(JSON.stringify(this['indexingModels_' + element]));
                    });
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    drop(event: CdkDragDrop<string[]>) {
        event.item.data.unit = event.container.id.split('_')[1];

        if (event.previousContainer === event.container) {
            moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        } else {

            transferArrayItem(event.previousContainer.data,
                event.container.data,
                event.previousIndex,
                event.currentIndex);
        }
    }

    onSubmit() {
        let arrIndexingModels: any[] = [];
        this.fieldCategories.forEach(category => {
            arrIndexingModels = arrIndexingModels.concat(this['indexingModels_' + category]);
        });
    }

    removeItem(arrTarget: string, item: any, index: number) {
        item.mandatory = false;
        if (item.identifier.indexOf('indexingCustomField') > -1) {
            this.availableCustomFields.push(item);
            this[arrTarget].splice(index, 1);
        } else {
            this.availableFields.push(item);
            this[arrTarget].splice(index, 1);
        }
    }

    getDatas() {
        let arrIndexingModels: any[] = [];
        this.fieldCategories.forEach(category => {
            arrIndexingModels = arrIndexingModels.concat(this['indexingModels_' + category]);
        });
        return arrIndexingModels;
    }

    getAvailableFields() {
        return this.availableFields;
    }

    getAvailableCustomFields() {
        return this.availableCustomFields;
    }

    isModified() {
        let state = false;
        let compare: string = '';
        let compareClone: string = '';

        this.fieldCategories.forEach(category => {

            compare = JSON.stringify((this['indexingModels_' + category]));
            compareClone = JSON.stringify((this['indexingModels_' + category + 'Clone']));

            if (compare !== compareClone) {
                state = true;
            }
        });
        return state;
    }

    setModification() {
        this.fieldCategories.forEach(element => {
            this['indexingModels_' + element + 'Clone'] = JSON.parse(JSON.stringify(this['indexingModels_' + element]));
        });
    }

    cancelModification() {
        this.fieldCategories.forEach(element => {
            this['indexingModels_' + element] = JSON.parse(JSON.stringify(this['indexingModels_' + element + 'Clone']));
        });
    }
}