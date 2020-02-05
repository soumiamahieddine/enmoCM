import { Component, OnInit, Input, ViewChild, ElementRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService } from '../../../service/header.service';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { AppService } from '../../../service/app.service';
import { SortPipe } from '../../../plugins/sorting.pipe';
import { FormControl } from '@angular/forms';
import { Observable, of } from 'rxjs';
import { debounceTime, filter, distinctUntilChanged, tap, switchMap, exhaustMap, catchError } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';

@Component({
    selector: 'app-address-ban-input',
    templateUrl: "address-ban-autocomplete.component.html",
    styleUrls: [
        'address-ban-autocomplete.component.scss',
        '../../indexation/indexing-form/indexing-form.component.scss'
    ],
    providers: [NotificationService, AppService, SortPipe]
})

export class AddressBanAutocompleteComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = false;

    key: string = 'address';

    canAdd: boolean = true;

    listInfo: string;
    myControl = new FormControl();
    filteredOptions: Observable<string[]>;
    options: any;
    valuesToDisplay: any = {};
    dialogRef: MatDialogRef<any>;
    addressBANCurrentDepartment: string = '75';
    departmentList: any[] = [];


    /**
     * FormControl used when autocomplete is used in form and must be catched in a form control.
     */
    @Input('control') controlAutocomplete: FormControl;

    @ViewChild('autoCompleteInput', { static: true }) autoCompleteInput: ElementRef;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public dialog: MatDialog,
        private headerService: HeaderService,
        public appService: AppService,
        private latinisePipe: LatinisePipe
    ) {

    }

    ngOnInit() {
        this.controlAutocomplete.setValue(this.controlAutocomplete.value === null || this.controlAutocomplete.value === '' ? [] : this.controlAutocomplete.value);
        this.initFormValue();
        this.initBanSearch();
        this.initAutocompleteRoute();
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
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    initAutocompleteRoute() {
        this.listInfo = this.lang.autocompleteInfo;
        this.options = [];
        this.myControl.valueChanges
            .pipe(
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                tap(() => this.loading = true),
                switchMap((data: any) => this.getDatas(data)),
                tap((data: any) => {
                    if (data.length === 0) {
                        this.listInfo = this.lang.noAvailableValue;
                    } else {
                        this.listInfo = '';
                    }
                    this.options = data;
                    this.filteredOptions = of(this.options);
                    this.loading = false;
                })
            ).subscribe();
    }

    getDatas(data: string) {
        return this.http.get('../../rest/autocomplete/banAddresses', { params: { "address": data, 'department': this.addressBANCurrentDepartment } });
    }

    selectOpt(ev: any) {
        const objAddress = {
            id: ev.option.value.banId,
            addressNumber: ev.option.value.number,
            addressStreet: ev.option.value.afnorName,
            addressPostcode : ev.option.value.postalCode,
            addressTown: ev.option.value.city,
            longitude: ev.option.value.lon,
            latitude: ev.option.value.lat
        }
        console.log(ev.option.value);
        
        this.setFormValue(objAddress);

        this.myControl.setValue('');
    }

    initFormValue() {
        this.controlAutocomplete.value.forEach((address: any) => {
            this.valuesToDisplay[address.id] = `${address.addressNumber} ${address.addressStreet}, ${address.addressTown} (${address.addressPostcode})`;
        });
    }

    setFormValue(item: any) {    
        this.valuesToDisplay[item['id']] = `${item.addressNumber} ${item.addressStreet}, ${item.addressTown} (${item.addressPostcode})`;
        this.controlAutocomplete.setValue([item]);
        console.log(this.valuesToDisplay);
    }

    resetAutocomplete() {
        this.options = [];
        this.listInfo = this.lang.autocompleteInfo;
    }

    unsetValue() {
        this.controlAutocomplete.setValue('');
        this.myControl.setValue('');
        this.myControl.enable();
    }

    removeItem(index: number) {
        let arrValue = this.controlAutocomplete.value;
        this.controlAutocomplete.value.splice(index, 1);
        this.controlAutocomplete.setValue(arrValue);
    }

    goTo(item: any) {
        window.open(`https://www.google.com/maps/search/${item.latitude},${item.longitude}`, '_blank')
    }
}