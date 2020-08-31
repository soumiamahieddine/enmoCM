import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup, Validators, FormBuilder } from '@angular/forms';
import { Observable } from 'rxjs/internal/Observable';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '../../../../service/notification/notification.service';
import { tap, catchError, debounceTime, filter, distinctUntilChanged, switchMap } from 'rxjs/operators';
import { of } from 'rxjs/internal/observable/of';

@Component({
    selector: 'app-registered-mail-recipient-input',
    templateUrl: './recipient-input.component.html',
    styleUrls: ['./recipient-input.component.scss']
})
export class RegisteredMailRecipientInputComponent implements OnInit {

    adminFormGroup: FormGroup;
    manualAddress: boolean = false;

    addressBANInfo: string = '';
    addressBANMode: boolean = true;
    addressBANControl = new FormControl();
    addressBANLoading: boolean = false;
    addressBANResult: any[] = [];
    addressBANFilteredResult: Observable<string[]>;
    addressBANCurrentDepartment: string = '75';
    departmentList: any[] = [];

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private _formBuilder: FormBuilder,
        private notify: NotificationService,
    ) { }

    ngOnInit(): void {
        this.initBanSearch();
        this.initAutocompleteAddressBan();
        this.adminFormGroup = this._formBuilder.group({
            company: ['', Validators.required],
            civility: ['', Validators.required],
            firstname: ['', Validators.required],
            lastname: ['', Validators.required],
            addressNumber: ['', Validators.required],
            addressStreet: ['', Validators.required],
            addressAdditional1: [''],
            addressAdditional2: [''],
            addressPostcode: ['', Validators.required],
            addressTown: ['', Validators.required],
            addressCountry: ['']
        });
    }

    initBanSearch() {
        this.http.get('../rest/ban/availableDepartments').pipe(
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

    initAutocompleteAddressBan() {
        this.addressBANInfo = this.translate.instant('lang.autocompleteInfo');
        this.addressBANResult = [];
        this.addressBANControl.valueChanges
            .pipe(
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                tap(() => this.addressBANLoading = true),
                switchMap((data: any) => this.http.get('../rest/autocomplete/banAddresses', { params: { 'address': data, 'department': this.addressBANCurrentDepartment } })),
                tap((data: any) => {
                    if (data.length === 0) {
                        this.addressBANInfo = this.translate.instant('lang.noAvailableValue');
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
        this.addressBANInfo = this.translate.instant('lang.autocompleteInfo');
    }

    selectAddressBan(ev: any) {
        this.adminFormGroup.controls['addressNumber'].setValue(ev.option.value.number);
        this.adminFormGroup.controls['addressStreet'].setValue(ev.option.value.afnorName);
        this.adminFormGroup.controls['addressPostcode'].setValue(ev.option.value.postalCode);
        this.adminFormGroup.controls['addressTown'].setValue(ev.option.value.city);
        this.adminFormGroup.controls['addressCountry'].setValue('FRANCE');
        this.addressBANControl.setValue('');
    }

    getFormatedAdress() {
        const formatedAddress = {};
        Object.keys(this.adminFormGroup.controls).forEach(key => {
            formatedAddress[key] = this.adminFormGroup.controls[key].value;
        });
        return formatedAddress;
    }
}
