import { Component, OnInit, ViewChild, ElementRef, EventEmitter, Output } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { AppService } from '../../../service/app.service';
import { FunctionsService } from '../../../service/functions.service';
import { Observable } from 'rxjs';
import { FormControl } from '@angular/forms';
import { startWith, map } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { MatExpansionPanel } from '@angular/material';

@Component({
    selector: 'app-criteria-tool',
    templateUrl: "criteria-tool.component.html",
    styleUrls: ['criteria-tool.component.scss'],
    providers: [AppService]
})
export class CriteriaToolComponent implements OnInit {

    lang: any = LANG;

    criteria: any = {
        resource: [
            {
                id : 'resourceField',
                label: 'Recherche par sujet / numéro chrono',
                control : new FormControl()
            },
            {
                id : 'contactField',
                label: 'Recherche par contact',
                control : new FormControl()
            },
        ],
        attachment: [],
        contact: []
    }

    currentCriteria: any = [];

    filteredCriteria: any = {};

    searchCriteria = new FormControl();

    @Output() searchUrlGenerated = new EventEmitter<string>();

    @ViewChild('criteriaTool', { static: false }) criteriaTool: MatExpansionPanel;
    @ViewChild('searchCriteriaInput', { static: false }) searchCriteriaInput: ElementRef;

    constructor(
        public http: HttpClient,
        public appService: AppService,
        public functions: FunctionsService,
        private latinisePipe: LatinisePipe) { }

    ngOnInit(): void {
        this.currentCriteria.push(this.criteria.resource[0]);

        Object.keys(this.criteria).forEach(keyVal => {
            this.filteredCriteria[keyVal] = {};
            this.filteredCriteria[keyVal] = new Observable<string[]>();
            this.filteredCriteria[keyVal] = this.searchCriteria.valueChanges
            .pipe(
                startWith(''),
                map(value => this._filter(value, keyVal))
            );
        });
    }

    private _filter(value: string, type: string): string[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.criteria[type].filter((option: any) => this.latinisePipe.transform(option['label'].toLowerCase()).includes(filterValue));
        } else {
            return this.criteria[type];
        }
    }

    isCurrentCriteria(criteriaId: string) {
        return this.currentCriteria.filter((currCrit: any) => currCrit.id === criteriaId).length > 0;
    }

    addCriteria(criteria: any) {
        this.currentCriteria.push(criteria);
        this.searchCriteria.reset();
        this.searchCriteriaInput.nativeElement.blur();
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
}