import { Component, OnInit, ViewChild, EventEmitter, Input, Output } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../../../translate.component';
import { NotificationService } from '../../../../notification.service';
import { HeaderService } from '../../../../../service/header.service';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../../../../service/app.service';
import { Observable, merge, Subject, of as observableOf, of } from 'rxjs';
import { MatPaginator, MatSort, MatDialog } from '@angular/material';
import { startWith, switchMap, map, catchError, filter, exhaustMap, tap, debounceTime, distinctUntilChanged, finalize, count } from 'rxjs/operators';
import { FormControl, Validators, ValidatorFn, FormGroup, FormBuilder } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
    selector: 'app-contact-form',
    templateUrl: "contacts-form.component.html",
    styleUrls: ['contacts-form.component.scss'],
    providers: [NotificationService, AppService]
})
export class ContactsFormComponent implements OnInit {

    @ViewChild('snav', { static: true }) public sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;

    lang: any = LANG;
    loading: boolean = false;

    @Input() creationMode: boolean = true;
    @Input() contactId: number = null;

    @Output() onSubmitEvent = new EventEmitter<number>();

    contactUnit = [
        {
            id: 'mainInfo',
            label: this.lang.denomination
        },
        {
            id: 'address',
            label: this.lang.address
        },
        {
            id: 'complement',
            label: this.lang.additionals
        }
    ];

    contactForm: any[] = [
        {
            id: 'company',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_company,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: true,
            filling: false,
            values: []
        },
        {
            id: 'firstname',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_firstname,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        },
        {
            id: 'lastname',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_lastname,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        },
        {
            id: 'function',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_function,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        },
        {
            id: 'department',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_department,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        },
        {
            id: 'email',
            unit: 'mainInfo',
            label: this.lang.email,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: true,
            filling: false,
            values: []
        },
        {
            id: 'phone',
            unit: 'mainInfo',
            label: this.lang.phoneNumber,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: true,
            filling: false,
            values: []
        },
        {
            id: 'addressNumber',
            unit: 'address',
            label: this.lang.contactsParameters_addressNumber,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        },
        {
            id: 'addressStreet',
            unit: 'address',
            label: this.lang.contactsParameters_addressStreet,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        },
        {
            id: 'addressAdditional1',
            unit: 'address',
            label: this.lang.contactsParameters_addressAdditional1,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        },
        {
            id: 'addressAdditional2',
            unit: 'address',
            label: this.lang.contactsParameters_addressAdditional2,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        },
        {
            id: 'addressPostcode',
            unit: 'address',
            label: this.lang.contactsParameters_addressPostcode,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        },
        {
            id: 'addressTown',
            unit: 'address',
            label: this.lang.contactsParameters_addressTown,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        },
        {
            id: 'addressCountry',
            unit: 'address',
            label: this.lang.contactsParameters_addressCountry,
            type: 'string',
            control: new FormControl(),
            required: false,
            display: false,
            filling: false,
            values: []
        }
    ];

    addressBANInfo: string = '';
    addressBANMode: boolean = true;
    addressBANControl = new FormControl();
    addressBANLoading: boolean = false;
    addressBANResult: any[] = [];
    addressBANFilteredResult: Observable<string[]>;
    addressBANCurrentDepartment: string = '75';
    departmentList: any[] = [];

    fillingParameters: any = null;
    fillingRate: any = {
        class: 'warn',
        value: 0
    }

    companyFound: any = null;

    constructor(
        public http: HttpClient,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService,
        private headerService: HeaderService,
        public appService: AppService,
        public dialog: MatDialog
    ) { }

    ngOnInit(): void {

        this.loading = true;

        this.initBanSearch();

        if (this.contactId === null) {

            this.creationMode = true;

            this.http.get("../../rest/contactsCustomFields").pipe(
                tap((data: any) => {
                    this.initCustomElementForm(data);
                }),
                exhaustMap(() => this.http.get("../../rest/contactsParameters")),
                tap((data: any) => {
                    this.fillingParameters = data.contactsFilling;
                    this.initElemForm(data);
                    this.initAutocompleteAddressBan();
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.creationMode = false;

            this.contactForm.forEach(element => {
                element.display = false;
            });

            this.http.get("../../rest/contactsCustomFields").pipe(
                tap((data: any) => {
                    this.initCustomElementForm(data);
                }),
                exhaustMap(() => this.http.get("../../rest/contactsParameters")),
                tap((data: any) => {
                    this.fillingParameters = data.contactsFilling;
                    this.initElemForm(data);
                    this.initAutocompleteAddressBan();
                }),
                exhaustMap(() => this.http.get("../../rest/contacts/" + this.contactId)),
                tap((data) => {
                    this.setContactData(data);
                }),
                filter((data: any) => data.customFields !== null),
                tap((data: any) => {
                    this.setContactCustomData(data);
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    initElemForm(data: any) {
        let valArr: ValidatorFn[] = [];

        data.contactsParameters.forEach((element: any) => {
            valArr = [];

            if ((element.mandatory || element.filling) && this.creationMode) {
                this.contactForm.filter(contact => contact.id === element.identifier)[0].display = true;
            }

            if (element.filling) {
                this.contactForm.filter(contact => contact.id === element.identifier)[0].filling = true;
            }

            if (element.identifier === 'email') {
                valArr.push(Validators.email);
            } else if (element.identifier === 'phone') {
                valArr.push(Validators.pattern(/\+?((|\ |\.|\(|\)|\-)?(\d)*)*\d$/));
            }

            if (element.mandatory) {
                this.contactForm.filter(contact => contact.id === element.identifier)[0].required = true;
                valArr.push(Validators.required);
            }

            if (this.contactForm.filter(contact => contact.id === element.identifier)[0] !== undefined) {
                this.contactForm.filter(contact => contact.id === element.identifier)[0].control.setValidators(valArr);
            }

        });
    }

    initCustomElementForm(data: any) {
        let valArr: ValidatorFn[] = [];

        let field: any = {};

        data.customFields.forEach((element: any) => {
            valArr = [];

            field = {
                id: `customField_${element.id}`,
                unit: 'complement',
                label: element.label,
                type: element.type,
                control: new FormControl({ value: '', disabled: false }),
                required: false,
                display: false,
                values: element.values.map((val: any) => { return { id: val, label: val } })
            };

            if (element.type === 'integer') {
                valArr.push(Validators.pattern(/^[+-]?([0-9]+([.][0-9]*)?|[.][0-9]+)$/));
                field.control.setValidators(valArr);
            }
            this.contactForm.push(field);
        });
    }

    setContactData(data: any) {
        let indexField = -1;
        Object.keys(data).forEach(element => {
            indexField = this.contactForm.map(field => field.id).indexOf(element);
            if (!this.isEmptyValue(data[element]) && indexField > -1) {
                this.contactForm[indexField].control.setValue(data[element]);
                this.contactForm[indexField].display = true;
            }
        });
        this.checkFilling();
    }

    setContactCustomData(data: any) {
        let indexField = -1;
        Object.keys(data.customFields).forEach(element => {
            indexField = this.contactForm.map(field => field.id).indexOf('customField_' + element);
            if (!this.isEmptyValue(data[element]) && indexField > -1) {
                this.contactForm[indexField].control.setValue(data.customFields[element]);
                this.contactForm[indexField].display = true;
            }
        });
        this.checkFilling();
    }

    initBanSearch() {
        this.http.get("../../rest/ban/availableDepartments").pipe(
            tap((data: any) => {
                if (data.default !== null && data.departments.indexOf(data.default.toString()) !== - 1) {
                    this.addressBANCurrentDepartment = data.default;
                }
                this.departmentList = data.departments;
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    isValidForm() {
        let state = true;

        this.contactForm.filter(contact => contact.display).forEach(element => {
            if (element.control.status !== 'VALID') {
                state = false;
            }
            element.control.markAsTouched()
        });

        return state;
    }

    onSubmit() {
        this.checkFilling();
        if (this.addressBANMode && this.emptyAddress()) {
            this.notify.error('Choisissez une BAN');
        } else if (this.isValidForm()) {
            if (this.contactId !== null) {
                this.updateContact();
            } else {
                this.createContact();
            }
        } else {
            this.notify.error('Veuillez corriger les erreurs');
        }

    }

    createContact() {
        this.http.post("../../rest/contacts", this.formatContact()).pipe(
            tap((data:any) => {
                this.onSubmitEvent.emit(data.id);
                this.notify.success(this.lang.contactAdded);
            }),
            //finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    updateContact() {
        this.http.put(`../../rest/contacts/${this.contactId}`, this.formatContact()).pipe(
            tap(() => {
                this.onSubmitEvent.emit(this.contactId);
                this.notify.success(this.lang.contactUpdated);
            }),
            //finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    formatContact() {
        let contact: any = {};
        contact['customFields'] = {};
        const regex = /customField_[.]*/g;

        this.contactForm.filter(field => field.display).forEach(element => {
            if (element.id.match(regex) !== null) {
                contact['customFields'][element.id.split('_')[1]] = element.control.value;
            } else {
                contact[element.id] = element.control.value;
            }
        });
        return contact;
    }

    isEmptyUnit(id: string) {
        if (this.contactForm.filter(field => field.display && field.unit === id).length === 0) {
            return true;
        } else {
            return false;
        }
    }

    initForm() {
        this.contactForm.forEach(element => {
            element.control = new FormControl({ value: '', disabled: false });
        });
    }

    toogleAllFieldsUnit(idUnit: string) {
        this.contactForm.filter(field => field.unit === idUnit).forEach((element: any) => {
            element.display = true;
        });
    }

    noField(id: string) {
        if (this.contactForm.filter(field => !field.display && field.unit === id).length === 0) {
            return true;
        } else {
            return false;
        }
    }

    isEmptyValue(value: string) {

        if (value === null) {
            return true;

        } else if (Array.isArray(value)) {
            if (value.length > 0) {
                return false;
            } else {
                return true;
            }
        } else if (String(value) !== '') {
            return false;
        } else {
            return true;
        }
    }

    checkCompany(field: any) {
        if (field.id === 'company' && (this.companyFound === null || this.companyFound.company !== field.control.value)) {

            this.http.get(`../../rest/autocomplete/contacts/company?search=${field.control.value}`).pipe(
                tap(() => this.companyFound = null),
                filter((data: any) => data.length > 0),
                tap((data) => {
                    this.companyFound = data[0];
                }),
                //finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    setAddress(contact: any, disableBan: boolean = true) {
        let indexField = -1;
        Object.keys(contact).forEach(element => {
            indexField = this.contactForm.map(field => field.id).indexOf(element);
            if (!this.isEmptyValue(contact[element]) && indexField > -1 && ['company', 'addressNumber', 'addressStreet', 'addressAdditional2', 'addressPostcode', 'addressTown', 'addressCountry'].indexOf(element) > -1) {
                this.contactForm[indexField].control.setValue(contact[element]);
                this.contactForm[indexField].display = true;
            }
        });
        this.checkFilling();

        this.addressBANMode = disableBan ? false : true;
    }

    canDelete(field: any) {
        if (field.id === "company") {
            const lastname = this.contactForm.filter(contact => contact.id === 'lastname')[0];
            if (lastname.display && !this.isEmptyValue(lastname.control.value)) {
                let valArr: ValidatorFn[] = [];
                field.control.setValidators(valArr);
                field.required = false;
                return true;
            } else {
                let valArr: ValidatorFn[] = [];
                valArr.push(Validators.required);
                field.control.setValidators(valArr);
                field.required = true;
                return false;
            }
        } else if (field.id === "lastname") {
            const company = this.contactForm.filter(contact => contact.id === 'company')[0];
            if (company.display && !this.isEmptyValue(company.control.value)) {
                let valArr: ValidatorFn[] = [];
                field.control.setValidators(valArr);
                field.required = false;
                return true;
            } else {
                let valArr: ValidatorFn[] = [];
                valArr.push(Validators.required);
                field.control.setValidators(valArr);
                field.required = true;
                return false;
            }
        } else if (field.required) {
            return false;
        } else {
            return true;
        }
    }

    removeField(field: any) {
        field.display = !field.display;
        field.control.reset();
        this.checkFilling();
    }

    initAutocompleteAddressBan() {
        this.addressBANInfo = this.lang.autocompleteInfo;
        this.addressBANResult = [];
        this.addressBANControl.valueChanges
            .pipe(
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                tap(() => this.addressBANLoading = true),
                switchMap((data: any) => this.http.get('../../rest/autocomplete/banAddresses', { params: { "address": data, 'department': this.addressBANCurrentDepartment } })),
                tap((data: any) => {
                    if (data.length === 0) {
                        this.addressBANInfo = this.lang.noAvailableValue;
                    } else {
                        this.addressBANInfo = '';
                    }
                    this.addressBANResult = data;
                    this.addressBANFilteredResult = of(this.addressBANResult);
                    this.addressBANLoading = false;
                })
            ).subscribe();
    }

    resetAutocompleteAddressBan() {
        this.addressBANResult = [];
        this.addressBANInfo = this.lang.autocompleteInfo;
    }

    selectAddressBan(ev: any) {
        let contact = {
            addressNumber: ev.option.value.number,
            addressStreet: ev.option.value.afnorName,
            addressPostcode: ev.option.value.postalCode,
            addressTown: ev.option.value.city,
            addressCountry: 'FRANCE'
        };
        this.setAddress(contact, false);
        this.addressBANControl.setValue('');
    }

    getValue(identifier: string) {
        return this.contactForm.filter(contact => contact.id === identifier)[0].control.value;
    }

    emptyAddress() {
        if (this.contactForm.filter(contact => this.isEmptyValue(contact.control.value) && ['addressNumber', 'addressStreet', 'addressPostcode', 'addressTown', 'addressCountry'].indexOf(contact.id) > -1).length > 0) {
            return true;
        } else {
            return false;
        }
    }

    goTo() {
        const contact = {
            addressNumber: this.contactForm.filter(contact => contact.id === 'addressNumber')[0].control.value,
            addressStreet: this.contactForm.filter(contact => contact.id === 'addressStreet')[0].control.value,
            addressPostcode: this.contactForm.filter(contact => contact.id === 'addressPostcode')[0].control.value,
            addressTown: this.contactForm.filter(contact => contact.id === 'addressTown')[0].control.value,
            addressCountry: this.contactForm.filter(contact => contact.id === 'addressCountry')[0].control.value
        };
        window.open(`https://www.google.com/maps/search/${contact.addressNumber}+${contact.addressStreet},+${contact.addressPostcode}+${contact.addressTown},+${contact.addressCountry}`, '_blank')
    }

    switchAddressMode() {
        let valArr: ValidatorFn[] = [];
        if (this.addressBANMode) {

            valArr.push(Validators.required);

            this.contactForm.filter(contact => ['addressNumber', 'addressStreet', 'addressPostcode', 'addressTown', 'addressCountry'].indexOf(contact.id) > -1).forEach((element: any) => {
                if (element.mandatory) {
                    element.control.setValidators(valArr);
                }
            });
            this.addressBANMode = !this.addressBANMode;
        } else {
            this.contactForm.filter(contact => ['addressNumber', 'addressStreet', 'addressPostcode', 'addressTown', 'addressCountry'].indexOf(contact.id) > -1).forEach((element: any) => {
                if (element.mandatory) {
                    element.control.setValidators(valArr);
                }
            });
            this.addressBANMode = !this.addressBANMode;
        }
    }

    getErrorMsg(error: any) {
        if (error.required !== undefined) {
            return this.lang.requiredField;
        } else if (error.pattern !== undefined || error.email !== undefined) {
            return this.lang.badFormat;
        } else {
            return 'unknow validator';
        }
    }

    checkFilling() {
        const countFilling = this.contactForm.filter(contact => contact.filling).length;
        const countValNotEmpty = this.contactForm.filter(contact => !this.isEmptyValue(contact.control.value) && contact.filling).length;

        this.fillingRate.value = Math.round((countValNotEmpty * 100) / countFilling);

        if (this.fillingRate.value <= this.fillingParameters.first_threshold) {
            this.fillingRate.class = 'warn';
        } else if (this.fillingRate.value <= this.fillingParameters.second_threshold) {
            this.fillingRate.class = 'primary';
        } else {
            this.fillingRate.class = 'accent';
        }
    }
}
