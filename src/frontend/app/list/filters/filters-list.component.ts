import { Component, OnInit, Input, ViewChild, EventEmitter, Output, ViewEncapsulation } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { FiltersListService } from '../../../service/filtersList.service';
import { MatSelectionList, MatExpansionPanel, MatInput } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    selector: 'app-filters-list',
    templateUrl: 'filters-list.component.html',
    styleUrls: ['filters-list.component.scss'],
    providers: [NotificationService],
    encapsulation: ViewEncapsulation.None
})
export class FiltersListComponent implements OnInit {

    coreUrl: string;
    lang: any = LANG;
    prioritiesList: any[] = [];
    categoriesList: any[] = [];
    entitiesList: any[] = [];

    @Input('listProperties') listProperties: any;

    @ViewChild('categoriesPan') categoriesPan: MatExpansionPanel;
    @ViewChild('prioritiesPan') prioritiesPan: MatExpansionPanel;
    @ViewChild('entitiesPan') entitiesPan: MatExpansionPanel;
    @ViewChild('subjectPan') subjectPan: MatExpansionPanel;
    @ViewChild('referencePan') referencePan: MatExpansionPanel;

    @Output() triggerEvent = new EventEmitter<string>();


    constructor(public http: HttpClient, private filtersListService: FiltersListService) { }

    ngOnInit(): void {
        if (this.listProperties.reference.length > 0) {
            this.referencePan.open();
        }
        if (this.listProperties.subject.length > 0) {
            this.subjectPan.open();
        }
        this.http.get("../../rest/priorities")
            .subscribe((data: any) => {
                this.prioritiesList = data.priorities;
                this.prioritiesList.forEach((element) => {
                    element.selected = false;
                    this.listProperties.priorities.forEach((listPropertyPrio: any) => {
                        if (element.id === listPropertyPrio.id) {
                            element.selected = true;
                            this.prioritiesPan.open();
                        }
                    });
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
                            this.categoriesPan.open();
                        }
                    });
                });
            });
    }
    updateFilters(e: MatSelectionList, id: string) {
        this.listProperties[id] = [];
        e.selectedOptions.selected.forEach(element => {
            this.listProperties[id].push({
                'id' : element.value,
                'label': element._text.nativeElement.innerText
            });
        });
        this.triggerEvent.emit();
    }

    setFocus(elem: MatInput) {
        setTimeout(() => {
            elem.focus();
        }, 200);  
    }
}