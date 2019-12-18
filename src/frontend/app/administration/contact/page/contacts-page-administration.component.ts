import { Component, OnInit, ViewChild, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../../translate.component';
import { NotificationService } from '../../../notification.service';
import { HeaderService } from '../../../../service/header.service';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../../../service/app.service';
import { Observable, merge, Subject, of as observableOf, of } from 'rxjs';
import { MatPaginator, MatSort, MatDialog } from '@angular/material';
import { takeUntil, startWith, switchMap, map, catchError, filter, exhaustMap, tap, debounceTime, distinctUntilChanged, finalize } from 'rxjs/operators';
import { ConfirmComponent } from '../../../../plugins/modal/confirm.component';
import { FormControl } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
    templateUrl: "contacts-page-administration.component.html",
    styleUrls: ['contacts-page-administration.component.scss'],
    providers: [NotificationService, AppService]
})
export class ContactsPageAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;

    lang: any = LANG;
    loading: boolean = false;

    creationMode: boolean = true;

    contactId: number = null;

    contactUnit = [
        {
            id: 'mainInfo',
            label: 'Denomination'
        },
        {
            id: 'address',
            label: 'Adresse'
        },
        {
            id: 'complement',
            label: 'Complement'
        }
    ];

    contactForm = [
        {
            id: 'company',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_company,
            type: 'string',
            control: new FormControl(),
            default: true
        },
        {
            id: 'firstname',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_firstname,
            type: 'string',
            control: new FormControl(),
            default: false
        },
        {
            id: 'lastname',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_lastname,
            type: 'string',
            control: new FormControl(),
            default: false
        },
        {
            id: 'function',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_function,
            type: 'string',
            control: new FormControl(),
            default: false
        },
        {
            id: 'department',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_department,
            type: 'string',
            control: new FormControl(),
            default: false
        },
        {
            id: 'addressAdditional1',
            unit: 'mainInfo',
            label: this.lang.contactsParameters_address_additional1,
            type: 'string',
            control: new FormControl(),
            default: false
        },
        {
            id: 'email',
            unit: 'mainInfo',
            label: this.lang.email,
            type: 'string',
            control: new FormControl(),
            default: true
        },
        {
            id: 'phone',
            unit: 'mainInfo',
            label: this.lang.phoneNumber,
            type: 'string',
            control: new FormControl(),
            default: true
        },
        {
            id: 'addressNumber',
            unit: 'address',
            label: this.lang.contactsParameters_address_number,
            type: 'string',
            control: new FormControl(),
            default: false
        },
        {
            id: 'addressStreet',
            unit: 'address',
            label: this.lang.contactsParameters_address_street,
            type: 'string',
            control: new FormControl(),
            default: false
        },
        {
            id: 'addressAdditional2',
            unit: 'address',
            label: this.lang.contactsParameters_address_additional2,
            type: 'string',
            control: new FormControl(),
            default: false
        },
        {
            id: 'addressPostcode',
            unit: 'address',
            label: this.lang.contactsParameters_address_postcode,
            type: 'string',
            control: new FormControl(),
            default: false
        },
        {
            id: 'addressTown',
            unit: 'address',
            label: this.lang.contactsParameters_address_town,
            type: 'string',
            control: new FormControl(),
            default: false
        },
        {
            id: 'addressCountry',
            unit: 'address',
            label: this.lang.contactsParameters_address_country,
            type: 'string',
            control: new FormControl(),
            default: false
        }
    ];

    companyFound:any = null;

    constructor(
        public http: HttpClient,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService,
        private headerService: HeaderService,
        public appService: AppService,
        public dialog: MatDialog) { }

    ngOnInit(): void {
        this.loading = true;

        this.route.params.subscribe((params: any) => {
            if (typeof params['id'] == "undefined") {
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.headerService.setHeader(this.lang.contactCreation);
                this.creationMode = true;

                this.http.get("../../rest/contactsCustomFields").pipe(
                    tap((data: any) => {
                        data.customFields.forEach((element: any) => {
                            this.contactForm.push(
                                {
                                    id: `customField_${element.id}`,
                                    unit: 'complement',
                                    label: element.label,
                                    type: element.type,
                                    control: new FormControl({ value: '', disabled: false }),
                                    default: false
                                }
                            );
                        });                        
                    }),
                    finalize(() => this.loading = false),
                    catchError((err: any) => {
                        this.notify.handleErrors(err);
                        return of(false);
                    })
                ).subscribe();


                this.loading = false;
            } else {
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(this.sidenavRight);

                this.creationMode = false;
                this.contactForm.forEach(element => {
                    element.default = false;
                });
                this.http.get("../../rest/contacts/" + params['id']).pipe(
                    tap((data) => {
                        this.contactId = params['id'];
                        let indexField = -1;
                        Object.keys(data).forEach(element => {
                            indexField = this.contactForm.map(field => field.id).indexOf(element);
                            if (!this.isEmptyValue(data[element]) && indexField > -1) {
                                this.contactForm[indexField].control.setValue(data[element]);
                                this.contactForm[indexField].default = true;
                            }
                        });
                    }),
                    finalize(() => this.loading = false),
                    catchError((err: any) => {
                        this.notify.handleErrors(err);
                        return of(false);
                    })
                ).subscribe();
            }
        });
    }

    onSubmit() {
        if (this.contactId !== null) {
            this.updateContact();
        } else {
            this.createContact();
        }
    }

    createContact() {
        let contact: any = {};
        let customFiel:any = {};
        contact['customFields'] = [];
        const regex = /customField_[.]*/g;

        this.contactForm.filter(field => field.default).forEach(element => {
            if (element.id.match(regex) !== null) {
                customFiel[element.id.split('_')[1]] = element.control.value;
                contact['customFields'].push(customFiel);
            } else {
                contact[element.id] = element.control.value;
            }   
        });
        this.http.post("../../rest/contacts", contact).pipe(
            tap(() => {
                this.router.navigate(["/administration/contacts"]);
                this.notify.success(this.lang.contactAdded);
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    updateContact() {
        let contact: any = {};
        this.contactForm.filter(field => field.default).forEach(element => {
            contact[element.id] = element.control.value;
        });
        this.http.put(`../../rest/contacts/${this.contactId}`, contact).pipe(
            tap(() => {
                this.router.navigate(["/administration/contacts"]);
                this.notify.success(this.lang.contactUpdated);
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    isEmptyUnit(id: string) {
        if (this.contactForm.filter(field => field.default && field.unit === id).length === 0) {
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
            element.default = true;
        });
    }

    noField(id: string) {
        if (this.contactForm.filter(field => !field.default && field.unit === id).length === 0) {
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

            this.http.get(`../../rest/contacts?search=${field.control.value}`).pipe(
                tap(() => this.companyFound = null),
                filter((data:any) => data.contacts.length > 0),
                tap((data) => {
                    this.companyFound = data.contacts[0];
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    setAddress(contact: any) {
        let indexField = -1;
        Object.keys(contact).forEach(element => {
            indexField = this.contactForm.map(field => field.id).indexOf(element);
            if (!this.isEmptyValue(contact[element]) && indexField > -1 && ['company', 'addressNumber', 'addressStreet', 'addressAdditional2', 'addressPostcode', 'addressTown', 'addressCountry'].indexOf(element) > -1) {
                this.contactForm[indexField].control.setValue(contact[element]);
                this.contactForm[indexField].default = true;
            }
        });
    }

    canDelete(field: any) {
        if (field.id === "company") {
            const lastname = this.contactForm.filter(contact => contact.id === 'lastname')[0];
            if (lastname.default && !this.isEmptyValue(lastname.control.value)) {
                return true;
            } else {
                return false;
            }
        } else if (field.id === "lastname") {
            const company = this.contactForm.filter(contact => contact.id === 'company')[0];
            if (company.default && !this.isEmptyValue(company.control.value)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}