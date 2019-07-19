import { Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Input, EventEmitter, Output, ViewChild, ElementRef } from '@angular/core';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';

@Component({
    selector: 'plugin-autocomplete',
    templateUrl: 'autocomplete.component.html',
    styleUrls: ['autocomplete.component.scss'],
})
export class PluginAutocomplete implements OnInit {
    myControl = new FormControl();

    @Input('datas') options: any;
    @Input('labelPlaceholder') placeholder: string;
    @Input('labelList') optGroupLabel: string;
    @Input('targetSearchKey') key: string;
    @Output('triggerEvent') selectedOpt = new EventEmitter();

    @ViewChild('autoCompleteInput') autoCompleteInput: ElementRef;

    filteredOptions: Observable<string[]>;

    constructor(private latinisePipe: LatinisePipe
    ) { }

    ngOnInit() {
        this.optGroupLabel = this.optGroupLabel === undefined ? 'Valeurs disponibles' : this.optGroupLabel;
        this.placeholder = this.placeholder === undefined ? 'Choisissez une valeur' : this.placeholder;

        this.filteredOptions = this.myControl.valueChanges
            .pipe(
                startWith(''),
                map(value => this._filter(value))
            );
    }

    selectOpt(ev: any) {
        this.myControl.setValue('');
        this.autoCompleteInput.nativeElement.blur();
        this.selectedOpt.emit(ev.option.value);
    }

    private _filter(value: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.options.filter((option: any) => this.latinisePipe.transform(option.label_action.toLowerCase()).includes(filterValue));
        } else {
            return this.options;
        }
    }
}