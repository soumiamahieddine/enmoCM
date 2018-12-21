import { Component, OnInit, Input, ViewChild, EventEmitter, Output, ViewEncapsulation } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { FiltersListService } from '../../../service/filtersList.service';
import { MatSelectionList, MatExpansionPanel, MatInput } from '@angular/material';

declare function $j(selector: any): any;

@Component({
    selector: 'app-filters-list',
    templateUrl: 'filters-list.component.html',
    styleUrls: ['filters-list.component.scss'],
    providers: [NotificationService],
    encapsulation: ViewEncapsulation.None
})
export class FiltersListComponent implements OnInit {

    lang: any = LANG;
    prioritiesList: any[] = [];
    inPrioritiesProperty = false;
    categoriesList: any[] = [];
    inCategoriesProperty = false;
    entitiesList: any[] = [];
    inEntitiesProperty = false;
    statusesList: any[] = [];
    inStatusesProperty = false;

    loading: boolean = false;

    @Input('listProperties') listProperties: any;

    @ViewChild('categoriesPan') categoriesPan: MatExpansionPanel;
    @ViewChild('prioritiesPan') prioritiesPan: MatExpansionPanel;
    @ViewChild('entitiesPan') entitiesPan: MatExpansionPanel;
    @ViewChild('subjectPan') subjectPan: MatExpansionPanel;
    @ViewChild('referencePan') referencePan: MatExpansionPanel;
    @ViewChild('statusesPan') statusesPan: MatExpansionPanel;

    @Output('refreshEvent') refreshEvent = new EventEmitter<string>();


    constructor(public http: HttpClient, private filtersListService: FiltersListService) { }

    ngOnInit(): void {
        this.loading = true;
        this.http.get("../../rest/priorities")
            .subscribe((data: any) => {
                this.prioritiesList = data.priorities;
                this.prioritiesList.forEach((element) => {
                    element.selected = false;
                    this.listProperties.priorities.forEach((listPropertyPrio: any) => {
                        if (element.id === listPropertyPrio.id) {
                            element.selected = true;
                            this.inPrioritiesProperty = true;
                        }
                    });
                });

                this.http.get("../../rest/categories")
                    .subscribe((data: any) => {
                        this.categoriesList = data.categories;
                        this.categoriesList.forEach(element => {
                            element.selected = false;
                            this.listProperties.categories.forEach((listPropertyCat: any) => {
                                if (element.id === listPropertyCat.id) {
                                    element.selected = true;
                                    this.inCategoriesProperty = true;
                                }
                            });
                        });

                        this.http.get("../../rest/statuses")
                            .subscribe((data: any) => {
                                this.statusesList = data.statuses;
                                this.statusesList.forEach(element => {
                                    element.selected = false;
                                    this.listProperties.statuses.forEach((listPropertyStatus: any) => {
                                        if (element.id === listPropertyStatus.id) {
                                            element.selected = true;
                                            this.inStatusesProperty = true;   
                                        }
                                    });
                                });

                                this.loading = false;

                                setTimeout(() => {
                                    if (this.listProperties.reference.length > 0) {
                                        this.referencePan.open();
                                    }
                                    if (this.listProperties.subject.length > 0) {
                                        this.subjectPan.open();
                                    }
                                    if (this.inPrioritiesProperty) {
                                        this.prioritiesPan.open();
                                    }
                                    if (this.inCategoriesProperty) {
                                        this.categoriesPan.open();
                                    }
                                    if (this.inStatusesProperty) {
                                        this.statusesPan.open();
                                    }
                                }, 200);
                                
                            });
                    });
            });
    }

    setFilters(e: MatSelectionList, id: string) {
        this.listProperties[id] = [];
        e.selectedOptions.selected.forEach(element => {
            this.listProperties[id].push({
                'id': element.value,
                'label': element._text.nativeElement.innerText
            });
        });
        this.updateFilters();
    }

    updateFilters() {
        this.listProperties.page = 0;

        this.filtersListService.updateListsProperties(this.listProperties);

        this.refreshEvent.emit();
    }

    setFocus(elem: MatInput) {
        setTimeout(() => {
            elem.focus();
        }, 200);
    }
}