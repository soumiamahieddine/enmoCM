import { Component, OnInit, ViewEncapsulation, Input, EventEmitter, Output } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { MatSidenav } from '@angular/material';
import { FiltersListService } from '../../../service/filtersList.service';

declare function $j(selector: any): any;

@Component({
    selector: 'app-filters-tool',
    templateUrl: 'filters-tool.component.html',
    styleUrls: ['filters-tool.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class FiltersToolComponent implements OnInit {

    lang: any = LANG;

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
    ]

    @Input('listProperties') listProperties: any;
    @Input('snavR') sidenavRight: MatSidenav;
    
    @Output('refreshEvent') refreshEvent = new EventEmitter<string>();

    constructor(public http: HttpClient, private filtersListService: FiltersListService) { }

    ngOnInit(): void {

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

    updateFiltersTool(e: any) {
        this.listProperties.delayed = false;
        this.listProperties.page = 0;

        e.value.forEach((element: any) => {
            this.listProperties[element] = true;
        });
        this.filtersListService.updateListsProperties(this.listProperties);

        this.refreshEvent.emit();

    }

    openFilter() {
        this.filtersListService.filterMode = true;
        this.sidenavRight.open();
    }
}