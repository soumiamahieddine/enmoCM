import { Component, OnInit, Input, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService } from '../../../service/header.service';
import { MatDialog } from '@angular/material/dialog';
import { AppService } from '../../../service/app.service';
import { tap, catchError, finalize, exhaustMap, map, filter } from 'rxjs/operators';
import { of, forkJoin } from 'rxjs';
import { SortPipe } from '../../../plugins/sorting.pipe';
import { CdkDragDrop, moveItemInArray, transferArrayItem } from '@angular/cdk/drag-drop';
import { FormControl, Validators, FormGroup, ValidationErrors, ValidatorFn, AbstractControl } from '@angular/forms';
import { DiffusionsListComponent } from '../../diffusions/diffusions-list.component';

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
    @Input('resId') resId: number = null;
    @Input('groupId') groupId: number;
    @Input('admin') adminMode: boolean;
    @Input('canEdit') canEdit: boolean = true;
    @Input('mode') mode: string = 'indexation';

    @Input('hideDiffusionList') hideDiffusionList: boolean = false;

    @ViewChild('appDiffusionsList', { static: false }) appDiffusionsList: DiffusionsListComponent;

    fieldCategories: any[] = ['mail', 'contact', 'process', 'classifying'];

    indexingModelsCore: any[] = [
        {
            identifier: 'doctype',
            label: this.lang.doctype,
            unit: 'mail',
            type: 'select',
            system: true,
            mandatory: true,
            default_value: '',
            values: []
        },
        {
            identifier: 'subject',
            label: this.lang.subject,
            unit: 'mail',
            type: 'string',
            system: true,
            mandatory: true,
            default_value: '',
            values: []
        },
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
            identifier: 'recipients',
            label: this.lang.getRecipients,
            type: 'autocomplete',
            default_value: null,
            values: []
        },
        {
            identifier: 'priority',
            label: this.lang.priority,
            type: 'select',
            default_value: null,
            values: []
        },
        {
            identifier: 'confidentiality',
            label: this.lang.confidential,
            type: 'radio',
            default_value: null,
            values: [{ 'id': true, 'label': this.lang.yes }, { 'id': false, 'label': this.lang.no }]
        },
        {
            identifier: 'initiator',
            label: this.lang.initiatorEntityAlt,
            type: 'select',
            default_value: null,
            values: []
        },
        {
            identifier: 'departureDate',
            label: this.lang.departureDate,
            type: 'date',
            default_value: null,
            values: []
        },
        {
            identifier: 'processLimitDate',
            label: this.lang.processLimitDate,
            type: 'date',
            default_value: null,
            values: []
        },
        {
            identifier: 'tags',
            label: this.lang.tags,
            type: 'autocomplete',
            default_value: null,
            values: ['/rest/autocomplete/tags', '/rest/tags']
        },
        {
            identifier: 'senders',
            label: this.lang.getSenders,
            type: 'autocomplete',
            default_value: null,
            values: ['/rest/autocomplete/correspondents']
        },
        {
            identifier: 'destination',
            label: this.lang.destination,
            type: 'select',
            default_value: null,
            values: []
        },
        {
            identifier: 'folders',
            label: this.lang.folders,
            type: 'autocomplete',
            default_value: null,
            values: ['/rest/autocomplete/folders', '/rest/folders']
        },
        {
            identifier: 'documentDate',
            label: this.lang.docDate,
            unit: 'mail',
            type: 'date',
            default_value: null,
            values: []
        },
        {
            identifier: 'arrivalDate',
            label: this.lang.arrivalDate,
            unit: 'mail',
            type: 'date',
            default_value: null,
            values: []
        },
    ];
    availableFieldsClone: any[] = [];

    availableCustomFields: any[] = [];
    availableCustomFieldsClone: any[] = null;

    indexingFormGroup: FormGroup;

    arrFormControl: any = {};

    currentCategory: string = '';
    currentPriorityColor: string = '';

    currentResourceValues: any = null;

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
                        info.default_value = null;
                        info.values = info.values.length > 0 ? info.values.map((custVal: any) => {
                            return {
                                id: custVal,
                                label: custVal
                            }
                        }) : info.values;
                        return info;
                    });
                    this.fieldCategories.forEach(element => {
                        this['indexingModels_' + element] = this.indexingModelsCore.filter((x: any, i: any, a: any) => x.unit === element);
                        this['indexingModels_' + element].forEach((field: any) => {
                            this.initValidator(field);
                        });
                    });
                    this.initElemForm();
                }),
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
            this.initValidator(event.item.data);
            transferArrayItem(event.previousContainer.data,
                event.container.data,
                event.previousIndex,
                event.currentIndex);
            if (['destination', 'priority'].indexOf(event.item.data.identifier) > -1) {
                this.initElemForm();
            }

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

    getDatas(withDiffusionList = true) {
        let arrIndexingModels: any[] = [];
        this.fieldCategories.forEach(category => {
            arrIndexingModels = arrIndexingModels.concat(this['indexingModels_' + category]);
        });
        arrIndexingModels.forEach(element => {

            if (element.type === 'date' && this.arrFormControl[element.identifier].value !== null) {
                
                if (element.today === true) {
                    if (!this.adminMode) {
                        const now = new Date();
                        element.default_value = ('00' + now.getDate()).slice(-2) + '-' + ('00' + (now.getMonth() + 1)).slice(-2) + '-' + now.getFullYear();
                    } else {
                        element.default_value = '_TODAY';
                    }
                } else {
                    let day = this.arrFormControl[element.identifier].value.getDate();
                    let month = this.arrFormControl[element.identifier].value.getMonth() + 1;
                    let year = this.arrFormControl[element.identifier].value.getFullYear();
                    if (element.identifier === 'processLimitDate') {
                        element.default_value = ('00' + day).slice(-2) + '-' + ('00' + month).slice(-2) + '-' + year + ' 23:59:59';
                    } else {
                        element.default_value = ('00' + day).slice(-2) + '-' + ('00' + month).slice(-2) + '-' + year;
                    }
                }   
            } else {
                element.default_value = this.arrFormControl[element.identifier].value === '' ? null : this.arrFormControl[element.identifier].value;
            }

            if (element.identifier === "destination" && !this.adminMode && withDiffusionList) {
                arrIndexingModels.push({
                    identifier: 'diffusionList',
                    default_value: this.arrFormControl['diffusionList'].value
                });
            }

        });

        if (!this.adminMode) {
            arrIndexingModels.push({
                identifier: 'modelId',
                default_value: this.indexingFormId
            });

            if (this.mode === 'indexation') {
                arrIndexingModels.push({
                    identifier: 'followed',
                    default_value: this.arrFormControl['mail­tracking'].value
                });
            }
        }

        return arrIndexingModels;
    }

    saveData(userId: number, groupId: number, basketId: number) {
        const formatdatas = this.formatDatas(this.getDatas());

        this.http.put(`../../rest/resources/${this.resId}?userId=${userId}&groupId=${groupId}&basketId=${basketId}`, formatdatas ).pipe(
            tap(() => {
                this.currentResourceValues = JSON.parse(JSON.stringify(this.getDatas(false))); ;
                this.notify.success(this.lang.dataUpdated);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    formatDatas(datas: any) {
        let formatData: any = {};
        const regex = /indexingCustomField_[.]*/g;

        formatData['customFields'] = {};

        datas.forEach((element: any) => {

            if (element.identifier.match(regex) !== null) {

                formatData['customFields'][element.identifier.split('_')[1]] = element.default_value;

            } else {
                formatData[element.identifier] = element.default_value;
            }
        });
        return formatData;
    }

    getCategory() {
        return this.currentCategory;
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

    isResourceModified() {
        if (this.loading || JSON.stringify(this.currentResourceValues) === JSON.stringify(this.getDatas(false))) {
            return false;
        } else {
            return true;
        }
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

        const myObservable = of(42);

        myObservable.pipe(
            exhaustMap(() => this.initializeRoutes()),
            tap((data) => {
                if (!this.adminMode) {
                    this.arrFormControl['mail­tracking'].setValue(false);
                }
                this.currentPriorityColor = '';

                this.fieldCategories.forEach(element => {
                    this['indexingModels_' + element].forEach((elem: any) => {
                        if (elem.identifier === 'documentDate') {
                            elem.startDate = '';
                            elem.endDate = '_TODAY';

                            this.fieldCategories.forEach(element => {
                                if (this['indexingModels_' + element].filter((field: any) => field.identifier === 'departureDate').length > 0) {
                                    elem.endDate = 'departureDate';
                                }
                            });

                        } else if (elem.identifier === 'destination') {
                            if (this.adminMode) {
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

                            } else {
                                let title = '';

                                let defaultVal = data.entities.filter((entity: any) => entity.enabled === true && entity.id === elem.default_value);
                                elem.default_value = defaultVal.length > 0 ? defaultVal[0].id : null;
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
                                elem.event = 'loadDiffusionList';
                                if (elem.default_value !== null && !this.adminMode) {
                                    this.loadDiffusionList(elem, elem.default_value);
                                }
                                elem.allowedEntities = elem.values.filter((val: any) => val.disabled === false).map((entities: any) => entities.id);
                            }
                        } else if (elem.identifier === 'arrivalDate') {
                            elem.startDate = 'documentDate';
                            elem.endDate = '_TODAY';

                        } else if (elem.identifier === 'initiator' && !this.adminMode) {
                            elem.values = this.headerService.user.entities.map((entity: any) => {
                                return {
                                    id: entity.id,
                                    label: entity.entity_label
                                }
                            });

                        } else if (elem.identifier === 'processLimitDate') {
                            elem.startDate = '_TODAY';
                            elem.endDate = '';
                            elem.event = 'setPriorityColorByLimitDate';

                        } else if (elem.identifier === 'departureDate') {
                            elem.startDate = 'documentDate';
                            elem.endDate = '';

                        } else if (elem.identifier === 'folders') {
                            elem.values = null;

                        } else if (elem.identifier === 'category_id') {
                            elem.values = data.categories;

                        } else if (elem.identifier === 'priority') {
                            elem.values = data.priorities;
                            elem.event = 'calcLimitDateByPriority';
                            if (elem.default_value !== null) {
                                this.calcLimitDateByPriority(elem, elem.default_value);
                            }
                        } else if (elem.identifier === 'doctype') {
                            let title = '';
                            let arrValues: any[] = [];
                            data.structure.forEach((doctype: any) => {
                                if (doctype['doctypes_second_level_id'] === undefined) {
                                    arrValues.push({
                                        id: doctype.doctypes_first_level_id,
                                        label: doctype.doctypes_first_level_label,
                                        title: doctype.doctypes_first_level_label,
                                        disabled: true,
                                        isTitle: true,
                                        color: doctype.css_style
                                    });
                                } else if (doctype['description'] === undefined) {
                                    arrValues.push({
                                        id: doctype.doctypes_second_level_id,
                                        label: '&nbsp;&nbsp;&nbsp;&nbsp;' + doctype.doctypes_second_level_label,
                                        title: doctype.doctypes_second_level_label,
                                        disabled: true,
                                        isTitle: true,
                                        color: doctype.css_style
                                    });

                                    arrValues = arrValues.concat(data.structure.filter((info: any) => info.doctypes_second_level_id === doctype.doctypes_second_level_id && info.description !== undefined).map((info: any) => {
                                        return {
                                            id: info.type_id,
                                            label: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + info.description,
                                            title: info.description,
                                            disabled: false,
                                            isTitle: false,
                                        }
                                    }));
                                }
                            });
                            elem.values = arrValues;
                            elem.event = 'calcLimitDate';
                            if (elem.default_value !== null && !this.adminMode) {
                                this.calcLimitDate(elem, elem.default_value);
                            }

                        }
                    });
                });
            }),
            filter(() => this.resId !== null),
            exhaustMap(() => this.http.get(`../../rest/resources/${this.resId}`)),
            tap((data: any) => {
                this.fieldCategories.forEach(element => {
                    this['indexingModels_' + element].forEach((elem: any) => {
                        const customId: any = Object.keys(data.customFields).filter(index => index === elem.identifier.split('indexingCustomField_')[1])[0];
                        
                        if (Object.keys(data).indexOf(elem.identifier) > -1 || customId !== undefined) {
                            let fieldValue: any = '';
                            
                            if (customId !== undefined) {
                                fieldValue = data.customFields[customId]; 
                            } else {
                                fieldValue = data[elem.identifier];  
                            }
                                                       
                            if (elem.type === 'date') {
                                fieldValue = new Date(fieldValue);
                            }
                            
                            if (elem.identifier === 'priority') {
                                this.setPriorityColor(null, fieldValue);
                            }

                            if (elem.identifier === 'destination') {
                                if (this.mode === 'process') {
                                    this.arrFormControl[elem.identifier].disable();
                                }
                                this.arrFormControl['diffusionList'].disable();
                            }
                            
                            this.arrFormControl[elem.identifier].setValue(fieldValue);
                        }
                        if (!this.canEdit) {
                            this.arrFormControl[elem.identifier].disable();
                        }
                    });
                });
                this.arrFormControl['mail­tracking'].setValue(data.followed);
            }),
            tap(() => {
                this.currentResourceValues = JSON.parse(JSON.stringify(this.getDatas(false)));
            }),
            finalize(() => this.loading = false)
        ).subscribe();
    }

    initializeRoutes() {
        let arrayRoutes: any = [];
        let mergedRoutesDatas: any = {};

        this.fieldCategories.forEach(element => {
            this['indexingModels_' + element].forEach((elem: any) => {
                if (elem.identifier === 'destination') {
                    if (this.adminMode || this.mode !== 'indexation') {
                        arrayRoutes.push(this.http.get('../../rest/indexingModels/entities'));

                    } else {
                        arrayRoutes.push(this.http.get('../../rest/indexing/groups/' + this.groupId + '/entities'));
                    }
                } else if (elem.identifier === 'category_id') {
                    arrayRoutes.push(this.http.get('../../rest/categories'));

                } else if (elem.identifier === 'priority') {
                    arrayRoutes.push(this.http.get('../../rest/priorities'));

                } else if (elem.identifier === 'doctype') {
                    arrayRoutes.push(this.http.get('../../rest/doctypes'));
                }
            });
        });
        return forkJoin(arrayRoutes).pipe(
            map(data => {
                let objectId = '';
                let index = '';
                for (var key in data) {

                    index = key;

                    objectId = Object.keys(data[key])[0];

                    mergedRoutesDatas[Object.keys(data[key])[0]] = data[index][objectId]
                }
                return mergedRoutesDatas;
            })
        )
    }



    createForm() {
        this.indexingFormGroup = new FormGroup(this.arrFormControl);
    }

    loadForm(indexModelId: number) {
        this.indexingFormId = indexModelId;
        Object.keys(this.arrFormControl).forEach(element => {
            delete this.arrFormControl[element];
        });

        this.loading = true;

        this.availableFields = JSON.parse(JSON.stringify(this.availableFieldsClone));

        if (!this.adminMode) {
            this.arrFormControl['mail­tracking'] = new FormControl({ value: '', disabled: this.adminMode ? true : false });
        }

        this.fieldCategories.forEach(category => {
            this['indexingModels_' + category] = [];
        });


        let arrayRoutes: any = [];
        let mergedRoutesDatas: any = {};


        if (this.availableCustomFieldsClone === null) {
            arrayRoutes.push(this.http.get("../../rest/customFields"));
        } else {
            this.availableCustomFields = JSON.parse(JSON.stringify(this.availableCustomFieldsClone));
        }

        arrayRoutes.push(this.http.get(`../../rest/indexingModels/${indexModelId}`));

        forkJoin(arrayRoutes).pipe(
            map(data => {
                let objectId = '';
                let index = '';
                for (var key in data) {
                    index = key;
                    objectId = Object.keys(data[key])[0];
                    mergedRoutesDatas[Object.keys(data[key])[0]] = data[index][objectId]
                }
                return mergedRoutesDatas;
            }),
            tap((data: any) => {
                if (data.customFields !== undefined) {
                    this.availableCustomFields = data.customFields.map((info: any) => {
                        info.identifier = 'indexingCustomField_' + info.id;
                        info.system = false;
                        info.default_value = null;
                        info.values = info.values.length > 0 ? info.values.map((custVal: any) => {
                            return {
                                id: custVal,
                                label: custVal
                            }
                        }) : info.values;
                        return info;
                    });
                    this.availableCustomFieldsClone = JSON.parse(JSON.stringify(this.availableCustomFields));
                }
                this.currentCategory = data.indexingModel.category;
                let fieldExist: boolean;
                if (data.indexingModel.fields.length === 0) {
                    this.fieldCategories.forEach(element => {
                        this['indexingModels_' + element] = this.indexingModelsCore.filter((x: any, i: any, a: any) => x.unit === element);
                        this.indexingModelsCore.forEach(field => {
                            this.initValidator(field);
                        });
                    });
                    this.notify.error(this.lang.noFieldInModelMsg);
                } else {
                    data.indexingModel.fields.forEach((field: any) => {
                        fieldExist = false;
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

                        indexFound = this.indexingModelsCore.map(info => info.identifier).indexOf(field.identifier);

                        if (indexFound > -1) {
                            field.label = this.indexingModelsCore[indexFound].label;
                            field.values = this.indexingModelsCore[indexFound].values;
                            field.type = this.indexingModelsCore[indexFound].type;
                            fieldExist = true;
                            field.system = true;
                        }

                        if (field.type === 'date' && field.default_value === '_TODAY') {
                            field.today = true;
                            field.default_value = new Date();
                        }

                        if (fieldExist) {
                            this['indexingModels_' + field.unit].push(field);
                            this.initValidator(field);
                        } else {
                            this.notify.error(this.lang.fieldNotExist + ': ' + field.identifier);
                        }

                    });
                }

                this.initElemForm();
                this.createForm();

            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    initValidator(field: any) {
        let valArr: ValidatorFn[] = [];
        this.arrFormControl[field.identifier] = new FormControl({ value: field.default_value, disabled: (field.today && this.adminMode) ? true : false });

        if (field.type === 'integer') {
            valArr.push(this.regexValidator(new RegExp('[+-]?([0-9]*[.])?[0-9]+'), { 'floatNumber': '' }));
        }

        if (field.mandatory && !this.adminMode) {
            valArr.push(Validators.required);
        }

        this.arrFormControl[field.identifier].setValidators(valArr);

        if (field.identifier === 'destination') {
            let valArr: ValidatorFn[] = [];
            if (field.mandatory) {
                valArr.push(Validators.required);
            }

            this.arrFormControl['diffusionList'] = new FormControl({ value: null, disabled: false });

            this.arrFormControl['diffusionList'].setValidators(valArr);

            this.arrFormControl['diffusionList'].setValue([]);

        }
    }

    regexValidator(regex: RegExp, error: ValidationErrors): ValidatorFn {
        return (control: AbstractControl): { [key: string]: any } => {
            if (!control.value) {
                return null;
            }
            const valid = regex.test(control.value);
            return valid ? null : error;
        };
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

    isEmptyField(field: any) {

        if (this.arrFormControl[field.identifier].value === null) {
            return true;

        } else if (Array.isArray(this.arrFormControl[field.identifier].value)) {
            if (this.arrFormControl[field.identifier].value.length > 0) {
                return false;
            } else {
                return true;
            }
        } else if (String(this.arrFormControl[field.identifier].value) !== '') {
            return false;
        } else {
            return true;
        }
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
            this.arrFormControl[field.identifier].setValue('');
            this.arrFormControl[field.identifier].enable();
        }
    }

    toggleMailTracking() {
        this.arrFormControl['mail­tracking'].setValue(!this.arrFormControl['mail­tracking'].value);

        if (this.mode !== 'indexation') {
            if (this.arrFormControl['mail­tracking'].value) {
                this.http.post(`../../rest/resources/${this.resId}/follow`, {}).pipe(
                    catchError((err: any) => {
                        this.notify.handleErrors(err);
                        return of(false);
                    })
                ).subscribe();
            } else {
                this.http.request('DELETE', '../../rest/resources/unfollow' , { body: { resources: [this.resId] } }).pipe(
                    catchError((err: any) => {
                        this.notify.handleErrors(err);
                        return of(false);
                    })
                ).subscribe();
            }
        }
    }

    changeCategory(categoryId: string) {
        this.currentCategory = categoryId;
    }

    changeDestination(entityIds: number[], allowedEntities: number[]) {

        if (entityIds.indexOf(this.arrFormControl['destination'].value) === -1) {
            this.arrFormControl['destination'].setValue(entityIds[0]);
        }
    }

    launchEvent(value: any, field: any) {
        if (field.event !== undefined && value !== null && !this.adminMode) {
            this[field.event](field, value);
        }
    }

    calcLimitDate(field: any, value: any) {
        let limitDate: Date = null;
        if (this.arrFormControl['processLimitDate'] !== undefined) {
            this.http.get("../../rest/indexing/processLimitDate", { params: { "doctype": value } }).pipe(
                tap((data: any) => {
                    limitDate = new Date(data.processLimitDate);
                    this.arrFormControl['processLimitDate'].setValue(limitDate);
                }),
                filter(() => this.arrFormControl['priority'] !== undefined),
                exhaustMap(() => this.http.get('../../rest/indexing/priority', { params: { "processLimitDate": limitDate.toDateString() } })),
                tap((data: any) => {
                    this.arrFormControl['priority'].setValue(data.priority);
                    this.setPriorityColor(null, data.priority);

                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    calcLimitDateByPriority(field: any, value: any) {
        let limitDate: Date = null;

        if (this.arrFormControl['processLimitDate'] !== undefined) {
            this.http.get("../../rest/indexing/processLimitDate", { params: { "priority": value } }).pipe(
                tap((data: any) => {
                    limitDate = new Date(data.processLimitDate);
                    this.arrFormControl['processLimitDate'].setValue(limitDate);
                    this.setPriorityColor(field, value);
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.setPriorityColor(field, value);
        }
    }

    setPriorityColor(field: any, value: any) {
        if (field !== null) {
            this.currentPriorityColor = field.values.filter((fieldVal: any) => fieldVal.id === value).map((fieldVal: any) => fieldVal.color)[0];
        } else {
            this.fieldCategories.forEach(element => {
                if (this['indexingModels_' + element].filter((field: any) => field.identifier === 'priority').length > 0) {
                    this.currentPriorityColor = this['indexingModels_' + element].filter((field: any) => field.identifier === 'priority')[0].values.filter((fieldVal: any) => fieldVal.id === value).map((fieldVal: any) => fieldVal.color)[0]
                }
            });
        }

    }

    setPriorityColorByLimitDate(field: any, value: any) {

        const limitDate = new Date(value.value);

        this.http.get("../../rest/indexing/priority", { params: { "processLimitDate": limitDate.toDateString() } }).pipe(
            tap((data: any) => {
                this.arrFormControl['priority'].setValue(data.priority);
                this.setPriorityColor(null, data.priority);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();

    }

    loadDiffusionList(field: any, value: any) {
        setTimeout(() => {
            this.appDiffusionsList.loadListModel(value);
        }, 0);
    }
}
