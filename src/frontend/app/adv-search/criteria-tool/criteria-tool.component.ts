import { Component, OnInit, ViewChild, ElementRef, EventEmitter, Output, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '../../../service/app.service';
import { FunctionsService } from '../../../service/functions.service';
import { Observable } from 'rxjs';
import { FormControl } from '@angular/forms';
import { startWith, map } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { MatExpansionPanel } from '@angular/material/expansion';
import { IndexingFieldsService } from '../../../service/indexing-fields.service';

@Component({
    selector: 'app-criteria-tool',
    templateUrl: 'criteria-tool.component.html',
    styleUrls: ['criteria-tool.component.scss']
})
export class CriteriaToolComponent implements OnInit {

    criteria: any = [];

    currentCriteria: any = [];

    filteredCriteria: Observable<string[]>;

    searchTermControl = new FormControl();
    searchCriteria = new FormControl();

    @Input() searchTerm: string = 'Foo';
    @Input() defaultCriteria: any = [];

    @Output() searchUrlGenerated = new EventEmitter<string>();

    @ViewChild('criteriaTool', { static: false }) criteriaTool: MatExpansionPanel;
    @ViewChild('searchCriteriaInput', { static: false }) searchCriteriaInput: ElementRef;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
        public functions: FunctionsService,
        public indexingFields: IndexingFieldsService,
        private latinisePipe: LatinisePipe) { }

    async ngOnInit(): Promise<void> {
        // console.log('getAllFields()', await this.indexingFields.getAllFields());

        this.searchTermControl.setValue(this.searchTerm);

        this.criteria = await this.indexingFields.getAllFields();

        this.criteria.forEach((element: any) => {
            if (this.defaultCriteria.indexOf(element.identifier) > -1) {
                element.control = new FormControl('');
                this.currentCriteria.push(element);
            }
        });

        this.filteredCriteria = this.searchCriteria.valueChanges
        .pipe(
            startWith(''),
            map(value => this._filter(value))
        );

        this.searchTermControl.valueChanges
        .pipe(
            startWith(''),
            map(value => {
                if (typeof value === 'string') {
                    this.searchTerm = value;
                }
            })
        ).subscribe();
    }

    private _filter(value: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.criteria.filter((option: any) => this.latinisePipe.transform(option['label'].toLowerCase()).includes(filterValue));
        } else {
            return this.criteria;
        }
    }

    isCurrentCriteria(criteriaId: string) {
        return this.currentCriteria.filter((currCrit: any) => currCrit.identifier === criteriaId).length > 0;
    }

    addCriteria(criteria: any) {
        criteria.control = new FormControl('');
        this.currentCriteria.push(criteria);
        this.searchTermControl.setValue(this.searchTerm);
        // this.searchCriteriaInput.nativeElement.blur();
    }

    removeCriteria(index: number) {
        this.currentCriteria.splice(index, 1);
    }

    getSearchUrl() {
        let arrUrl: any[] = [];
        this.currentCriteria.forEach((crit: any) => {
            if (!this.functions.empty(crit.control.value)) {
                arrUrl.push(`${crit.id}=${crit.control.value}`);
            }
        });
        this.criteriaTool.close();
        this.searchUrlGenerated.emit('&' + arrUrl.join('&'));
    }

    getFilterControl() {
        return this.searchCriteria;
    }

    getCriterias() {
        return this.criteria;
    }

    getFilteredCriterias() {
        return this.filteredCriteria;
    }
}
