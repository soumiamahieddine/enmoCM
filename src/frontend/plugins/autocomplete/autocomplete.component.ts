import { Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Input, EventEmitter, Output, ViewChild, ElementRef } from '@angular/core';
import { Observable, of, forkJoin } from 'rxjs';
import { map, startWith, debounceTime, filter, distinctUntilChanged, switchMap, tap } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { LANG } from '../../app/translate.component';
import { HttpClient } from '@angular/common/http';

@Component({
    selector: 'plugin-autocomplete',
    templateUrl: 'autocomplete.component.html',
    styleUrls: ['autocomplete.component.scss'],
})
export class PluginAutocomplete implements OnInit {
    lang: any = LANG;
    myControl = new FormControl();
    loading = false;

    listInfo: string;

    type = {
        user : 'fa-user',
        entity : 'fa-sitemap'
    }

    @Input('size') size: string;
    @Input('singleMode') singleMode: boolean;
    @Input('required') required: boolean;
    @Input('datas') options: any;
    @Input('routeDatas') routeDatas: string[];
    @Input('labelPlaceholder') placeholder: string;
    @Input('labelList') optGroupLabel: string;
    @Input('targetSearchKey') key: string;
    @Input('subInfoKey') subInfoKey: string;
    @Output('triggerEvent') selectedOpt = new EventEmitter();

    @ViewChild('autoCompleteInput', { static: true }) autoCompleteInput: ElementRef;

    filteredOptions: Observable<string[]>;

    constructor(
        public http: HttpClient,
        private latinisePipe: LatinisePipe
    ) { }

    ngOnInit() {
        this.optGroupLabel = this.optGroupLabel === undefined ? this.lang.availableValues : this.optGroupLabel;
        this.placeholder = this.placeholder === undefined ? this.lang.chooseValue : this.placeholder;
        this.size = this.size === undefined ? 'default' : 'small';

        if (this.routeDatas !== undefined) {
            this.initAutocompleteRoute();
        } else {
            this.initAutocompleteData();
        }
    }

    initAutocompleteData() {
        this.listInfo = this.lang.noAvailableValue;
        this.filteredOptions = this.myControl.valueChanges
            .pipe(
                startWith(''),
                map(value => this._filter(value))
            );
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
                    this.listInfo = data.length === 0 ? this.lang.noAvailableValue : '';
                    this.options = data;
                    this.filteredOptions = of(this.options);
                    this.loading = false;
                })
            ).subscribe();
    }

    getDatas(data: string) {
        let arrayObs:any = [];
        let test: any = [];
        this.routeDatas.forEach(element => {
            arrayObs.push(this.http.get('../..' + element, { params: { "search": data } }));
        });

        return forkJoin(arrayObs).pipe(
            map(data => {
                data.forEach((element: any) => {
                    element.forEach((element2: any) => {
                        test.push(element2);
                    });
                });
                return test;
            })
        );
    }

    selectOpt(ev: any) {
        if (this.singleMode !== undefined) {
            this.myControl.setValue(ev.option.value[this.key]);
        }
        this.resetAutocomplete();
        this.autoCompleteInput.nativeElement.blur();
        this.selectedOpt.emit(ev.option.value);
    }

    resetAutocomplete() {
        if (this.singleMode === undefined) {
            this.myControl.setValue(''); 
        }
        if (this.routeDatas !== undefined) {
            this.options = [];
            this.listInfo = this.lang.autocompleteInfo;
        }
    }

    private _filter(value: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.options.filter((option: any) => this.latinisePipe.transform(option[this.key].toLowerCase()).includes(filterValue));
        } else {
            return this.options;
        }
    }
}