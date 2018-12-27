import { Component, OnInit, ViewEncapsulation, Input, EventEmitter, Output, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { MatSidenav, MatMenu, MatMenuTrigger, MatAutocompleteSelectedEvent, MatInput, MatAutocompleteTrigger } from '@angular/material';
import { FiltersListService } from '../../../service/filtersList.service';
import { Observable } from 'rxjs';
import { FormBuilder, FormGroup } from '@angular/forms';
import { startWith, map } from 'rxjs/operators';

declare function $j(selector: any): any;

export interface StateGroup {
    letter: string;
    names: any[];
}

export const _filter = (opt: string[], value: string): string[] => {

    const filterValue = value.toLowerCase();

    return opt.filter(item => item['label'].toLowerCase().indexOf(filterValue) != -1);
};
@Component({
    selector: 'app-filters-tool',
    templateUrl: 'filters-tool.component.html',
    styleUrls: ['filters-tool.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class FiltersToolComponent implements OnInit {

    lang: any = LANG;

    stateForm: FormGroup = this.fb.group({
        stateGroup: '',
    });

    displayColsOrder = [
        { 'id': 'dest_user' },
        { 'id': 'creation_date' },
        { 'id': 'process_limit_date' },
        { 'id': 'destination' },
        { 'id': 'subject' },
        { 'id': 'alt_identifier' },
        { 'id': 'priority' },
        { 'id': 'status' },
        { 'id': 'type_id' }
    ];

    @ViewChild(MatAutocompleteTrigger) autocomplete: MatAutocompleteTrigger;

    priorities: any[] = [];
    categories: any[] = [];
    entitiesList: any[] = [];
    statuses: any[] = [];

    stateGroups: StateGroup[] = [];
    stateGroupOptions: Observable<StateGroup[]>;

    isLoading: boolean = false;

    @Input('listProperties') listProperties: any;
    @Input('currentBasketInfo') currentBasketInfo: any;

    @Input('snavR') sidenavRight: MatSidenav;

    @Output('refreshEvent') refreshEvent = new EventEmitter<string>();

    constructor(public http: HttpClient, private filtersListService: FiltersListService, private fb: FormBuilder) { }

    ngOnInit(): void {

    }

    private _filterGroup(value: string): StateGroup[] {
        if (value) {
            return this.stateGroups
                .map(group => ({ letter: group.letter, names: _filter(group.names, value) }))
                .filter(group => group.names.length > 0);
        }

        return this.stateGroups;
    }

    changeOrderDir() {
        if (this.listProperties.orderDir == 'ASC') {
            this.listProperties.orderDir = 'DESC';
        } else {
            this.listProperties.orderDir = 'ASC';
        }
        this.updateFilters();
    }

    updateFilters() {
        this.listProperties.page = 0;

        this.filtersListService.updateListsProperties(this.listProperties);

        this.refreshEvent.emit();
    }

    setFilters(e: any, id: string) {
        this.listProperties[id] = e.source.checked;
        this.updateFilters();
    }


    selectFilter(e: MatAutocompleteSelectedEvent) {
        this.listProperties[e.option.value.id].push({
            'id': e.option.value.value,
            'label': e.option.value.label
        });
        $j('.metaSearch').blur();
        this.stateForm.controls['stateGroup'].reset();
        this.updateFilters();
    }

    metaSearch(e: any) {
        this.listProperties.search = e.target.value;
        $j('.metaSearch').blur();
        this.stateForm.controls['stateGroup'].reset();
        this.autocomplete.closePanel();
        this.updateFilters();
    }

    removeFilter(id: string, i: number) {
        this.listProperties[id].splice(i, 1);
        this.updateFilters();
    }

    setInputSearch(value: string) {
        $j('.metaSearch').focus();
        setTimeout(() => {
            this.stateForm.controls['stateGroup'].setValue(value);
        }, 200);

    }

    initFilters() {
        this.isLoading = true;

        this.stateForm.controls['stateGroup'].reset();
        this.stateGroups = [
            {
                letter: this.lang.categories,
                names: []
            },
            {
                letter: this.lang.priorities,
                names: []
            },
            {
                letter: this.lang.statuses,
                names: []
            },
            {
                letter: this.lang.entities,
                names: []
            },
        ];

        this.http.get('../../rest/resourcesList/users/' + this.currentBasketInfo.ownerId + '/groups/' + this.currentBasketInfo.groupId + '/baskets/' + this.currentBasketInfo.basketId + '/filters')
            .subscribe((data: any) => {
                data.categories.forEach((element: any) => {
                    if (this.listProperties.categories.map((category: any) => (category.id)).indexOf(element.id) === -1) {
                        this.stateGroups[0].names.push(
                            {
                                id: 'categories',
                                value: element.id,
                                label: (element.label !== null ? element.label : '_UNDEFINED'),
                                count: element.count
                            }
                        )
                    }
                });
                data.priorities.forEach((element: any) => {
                    if (this.listProperties.priorities.map((priority: any) => (priority.id)).indexOf(element.id) === -1) {
                        this.stateGroups[1].names.push(
                            {
                                id: 'priorities',
                                value: element.id,
                                label: (element.label !== null ? element.label : '_UNDEFINED'),
                                count: element.count
                            }
                        )
                    }
                });
                data.statuses.forEach((element: any) => {
                    if (this.listProperties.statuses.map((status: any) => (status.id)).indexOf(element.id) === -1) {
                        this.stateGroups[2].names.push(
                            {
                                id: 'statuses',
                                value: element.id,
                                label: (element.label !== null ? element.label : '_UNDEFINED') ,
                                count: element.count
                            }
                        )
                    }

                });

                data.entities.forEach((element: any) => {
                    if (this.listProperties.entities.map((entity: any) => (entity.id)).indexOf(element.id) === -1) {
                        this.stateGroups[3].names.push(
                            {
                                id: 'entities',
                                value: element.entityId,
                                label: (element.label !== null ? element.label : '_UNDEFINED') ,
                                count: element.count
                            }
                        )
                    }

                });
                this.isLoading = false;
            });

        this.stateGroupOptions = this.stateForm.get('stateGroup')!.valueChanges
            .pipe(
                startWith(''),
                map(value => this._filterGroup(value))
            );
    }
}