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
import { FormControl, Validators, FormGroup, ValidationErrors } from '@angular/forms';

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
    @Input('groupId') groupId: number;
    @Input('admin') adminMode: boolean;

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
    availableFieldsClone: any[] = [];

    availableCustomFields: any[] = [];
    availableCustomFieldsClone: any[] = []

    indexingFormGroup: FormGroup;

    arrFormControl: any = {};

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public dialog: MatDialog,
        private headerService: HeaderService,
        public appService: AppService,
    ) {

    }

    ngOnInit(): void {
        this.adminMode === undefined ? this.adminMode = false : this.adminMode = true;

        this.availableFieldsClone = JSON.parse(JSON.stringify(this.availableFields));

        this.fieldCategories.forEach(category => {
            this['indexingModels_' + category] = [];
        });

        if (this.indexingFormId <= 0 || this.indexingFormId === undefined) {
            this.http.get("../../rest/customFields").pipe(
                tap((data: any) => {
                    this.availableCustomFields = data.customFields.map((info: any) => {
                        info.identifier = 'indexingCustomField_' + info.id;
                        info.system = false;
                        return info;
                    });
                    this.fieldCategories.forEach(element => {
                        this['indexingModels_' + element] = this.indexingModelsCore.filter((x: any, i: any, a: any) => x.unit === element);
                        this['indexingModels_' + element].forEach((field: any) => {
                            this.arrFormControl[field.identifier] = new FormControl({ value: field.default_value, disabled: (field.system && this.adminMode) }, field.mandatory ? [Validators.required] : []);
                        });
                    });
                }),
                //finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.loadForm(this.indexingFormId);
        }
    }

    drop(event: CdkDragDrop<string[]>) {
        event.item.data.unit = event.container.id.split('_')[1];

        if (event.previousContainer === event.container) {
            moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        } else {
            this.arrFormControl[event.item.data.identifier] = new FormControl({ value: '', disabled: false }, event.item.data.mandatory ? [Validators.required] : []);
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
        arrIndexingModels.forEach(element => {
            if (element.today === true) {
                element.default_value = '_TODAY';
            } else {
                element.default_value = this.arrFormControl[element.identifier].value;
            }
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

    initElemForm() {
        this.loading = true;

        // FAKE LOADER : MERGE ROUTES FOR REAL LOADER
        setTimeout(() => {
            this.loading = false;
        }, 800);

        this.fieldCategories.forEach(element => {
            this['indexingModels_' + element].forEach((elem: any) => {

                if (elem.identifier === 'docDate') {
                    elem.startDate = '';
                    elem.endDate = '_TODAY';
                } else
                    if (elem.identifier === 'destination') {
                        if (this.adminMode) {
                            this.http.get("../../rest/indexingModels/entities").pipe(
                                tap((data: any) => {
                                    let title = '';
                                    elem.values = data.entities.map((entity: any) => {
                                        title = entity.entity_label;

                                        for (let index = 0; index < entity.level; index++) {
                                            entity.entity_label = '&nbsp;&nbsp;&nbsp;&nbsp;' + entity.entity_label;
                                        }
                                        return {
                                            id: entity.id,
                                            title: title,
                                            label: entity.entity_label,
                                            disabled: false
                                        }
                                    });
                                }),
                                //finalize(() => this.loading = false),
                                catchError((err: any) => {
                                    this.notify.handleErrors(err);
                                    return of(false);
                                })
                            ).subscribe();
                        } else {
                            this.http.get("../../rest/indexing/" + this.groupId + "/entities").pipe(
                                tap((data: any) => {
                                    let title = '';

                                    let defaultVal = data.entities.filter((entity: any) => entity.enabled === true && entity.id === elem.default_value);
                                    elem.default_value = defaultVal.length > 0 ? defaultVal[0].id : '';
                                    this.arrFormControl[elem.identifier].setValue(defaultVal.length > 0 ? defaultVal[0].id : '');

                                    elem.values = data.entities.map((entity: any) => {
                                        title = entity.entity_label;

                                        for (let index = 0; index < entity.level; index++) {
                                            entity.entity_label = '&nbsp;&nbsp;&nbsp;&nbsp;' + entity.entity_label;
                                        }
                                        return {
                                            id: entity.id,
                                            title: title,
                                            label: entity.entity_label,
                                            disabled: !entity.enabled
                                        }
                                    });
                                }),
                                //finalize(() => this.loading = false),
                                catchError((err: any) => {
                                    this.notify.handleErrors(err);
                                    return of(false);
                                })
                            ).subscribe();
                        }
                    } else
                        if (elem.identifier === 'arrivalDate') {
                            elem.startDate = 'docDate';
                            elem.endDate = '_TODAY';
                        } else
                            if (elem.identifier === 'category_id') {
                                this.http.get("../../rest/categories").pipe(
                                    tap((data: any) => {
                                        elem.values = data.categories;
                                    }),
                                    //finalize(() => this.loading = false),
                                    catchError((err: any) => {
                                        this.notify.handleErrors(err);
                                        return of(false);
                                    })
                                ).subscribe();
                            } else
                                if (elem.identifier === 'priority') {
                                    this.http.get("../../rest/priorities").pipe(
                                        tap((data: any) => {
                                            elem.values = data.priorities;
                                        }),
                                        //finalize(() => this.loading = false),
                                        catchError((err: any) => {
                                            this.notify.handleErrors(err);
                                            return of(false);
                                        })
                                    ).subscribe();
                                } else
                                    if (elem.identifier === 'category_id') {
                                        this.http.get("../../rest/categories").pipe(
                                            tap((data: any) => {
                                                elem.values = data.categories;
                                            }),
                                            //finalize(() => this.loading = false),
                                            catchError((err: any) => {
                                                this.notify.handleErrors(err);
                                                return of(false);
                                            })
                                        ).subscribe();
                                    } else
                                        if (elem.identifier === 'doctype') {
                                            this.http.get("../../rest/doctypes").pipe(
                                                tap((data: any) => {
                                                    let arrValues: any[] = [];
                                                    data.structure.forEach((doctype: any) => {
                                                        if (doctype['doctypes_second_level_id'] === undefined) {
                                                            arrValues.push({
                                                                id: doctype.doctypes_first_level_id,
                                                                label: doctype.doctypes_first_level_label,
                                                                disabled: true,
                                                                isTitle: true,
                                                                color: doctype.css_style
                                                            });
                                                        } else if (doctype['description'] === undefined) {
                                                            arrValues.push({
                                                                id: doctype.doctypes_second_level_id,
                                                                label: '&nbsp;&nbsp;&nbsp;&nbsp;' + doctype.doctypes_second_level_label,
                                                                disabled: true,
                                                                isTitle: true,
                                                                color: doctype.css_style
                                                            });

                                                            arrValues = arrValues.concat(data.structure.filter((info: any) => info.doctypes_second_level_id === doctype.doctypes_second_level_id && info.description !== undefined).map((info: any) => {
                                                                return {
                                                                    id: info.type_id,
                                                                    label: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + info.description,
                                                                    disabled: false,
                                                                    isTitle: false,
                                                                }
                                                            }));
                                                        }
                                                    });
                                                    elem.values = arrValues;
                                                }),
                                                //finalize(() => this.loading = false),
                                                catchError((err: any) => {
                                                    this.notify.handleErrors(err);
                                                    return of(false);
                                                })
                                            ).subscribe();
                                        }
            });
        });
    }

    createForm() {
        this.indexingFormGroup = new FormGroup(this.arrFormControl);
    }

    loadForm(indexModelId: number) {
        this.loading = true;

        this.availableFields = JSON.parse(JSON.stringify(this.availableFieldsClone));

        this.fieldCategories.forEach(category => {
            this['indexingModels_' + category] = [];
        });

        this.http.get("../../rest/customFields").pipe(
            tap((data: any) => {
                this.availableCustomFields = data.customFields.map((info: any) => {
                    info.identifier = 'indexingCustomField_' + info.id;
                    info.system = false;
                    return info;
                });
            }),
            exhaustMap((data) => this.http.get("../../rest/indexingModels/" + indexModelId)),
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

                        if (field.type === 'date' && field.default_value === '_TODAY') {
                            field.today = true;
                            field.default_value = new Date();
                        }

                        if (fieldExist) {
                            this['indexingModels_' + field.unit].push(field);
                            this.arrFormControl[field.identifier] = new FormControl({ value: field.default_value, disabled: (field.today && this.adminMode) ? true : false }, (field.mandatory && !this.adminMode) ? [Validators.required] : []);
                        } else {
                            this.notify.error("Le champ " + field.identifier + " n'existe pas !");
                        }

                    });
                }

                this.initElemForm();
                this.createForm();
            }),
            //finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    isValidForm() {
        if (!this.indexingFormGroup.valid) {
            Object.keys(this.indexingFormGroup.controls).forEach(key => {

                const controlErrors: ValidationErrors = this.indexingFormGroup.get(key).errors;
                if (controlErrors != null) {
                    this.indexingFormGroup.controls[key].markAsTouched();
                    /*Object.keys(controlErrors).forEach(keyError => {
                        console.log('Key control: ' + key + ', keyError: ' + keyError + ', err value: ', controlErrors[keyError]);
                    });*/
                }
            });
        }
        return this.indexingFormGroup.valid;
    }

    getMinDate(id: string) {
        if (this.arrFormControl[id] !== undefined) {
            return this.arrFormControl[id].value;
        } else if (id === '_TODAY') {
            return new Date();
        } else {
            return '';
        }
    }

    getMaxDate(id: string) {
        if (this.arrFormControl[id] !== undefined) {
            return this.arrFormControl[id].value;
        } else if (id === '_TODAY') {
            return new Date();
        } else {
            return '';
        }
    }

    toggleTodayDate(field: any) {
        field.today = !field.today;
        if (field.today) {
            this.arrFormControl[field.identifier].disable();
            this.arrFormControl[field.identifier].setValue(new Date());
        } else {
            this.arrFormControl[field.identifier].enable();
        }
    }


    // TO DO
    _handleKeydown(event: KeyboardEvent) {
        if (event.keyCode === 32) {
            // do not propagate spaces to MatSelect, as this would select the currently active option
            event.stopPropagation();
        }

    }
}