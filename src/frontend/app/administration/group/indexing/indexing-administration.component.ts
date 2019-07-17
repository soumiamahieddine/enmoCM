import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../../../translate.component';
import { NotificationService } from '../../../notification.service';
import { tap } from 'rxjs/internal/operators/tap';
import { exhaustMap, startWith, map } from 'rxjs/operators';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { MatAutocompleteSelectedEvent } from '@angular/material';

declare function $j(selector: any): any;

@Component({
    selector: 'app-indexing-administration',
    templateUrl: 'indexing-administration.component.html',
    styleUrls: ['indexing-administration.component.scss'],
    providers: [NotificationService]
})
export class IndexingAdministrationComponent implements OnInit {

    mobileQuery: MediaQueryList;

    coreUrl: string;
    lang: any = LANG;
    loading: boolean = false;

    keywordEntities: any[] = [];
    actionList: any[] = [];

    myControl = new FormControl();

    indexingInfo: any = {
        actions: []
    };
    filteredActionList: Observable<any[]>;

    constructor(public http: HttpClient,
        private notify: NotificationService,
    ) {

        this.keywordEntities = [{
            id: 'ALL_ENTITIES',
            keyword: 'ALL_ENTITIES',
            parent: '#',
            icon: 'fa fa-hashtag',
            allowed: true,
            text: this.lang.allEntities
        }, {
            id: 'ENTITIES_JUST_BELOW',
            keyword: 'ENTITIES_JUST_BELOW',
            parent: '#',
            icon: 'fa fa-hashtag',
            allowed: true,
            text: this.lang.immediatelyBelowMyPrimaryEntity
        }, {
            id: 'ENTITIES_BELOW',
            keyword: 'ENTITIES_BELOW',
            parent: '#',
            icon: 'fa fa-hashtag',
            allowed: true,
            text: this.lang.belowAllMyEntities
        }, {
            id: 'ALL_ENTITIES_BELOW',
            keyword: 'ALL_ENTITIES_BELOW',
            parent: '#',
            icon: 'fa fa-hashtag',
            allowed: true,
            text: this.lang.belowMyPrimaryEntity
        }, {
            id: 'MY_ENTITIES',
            keyword: 'MY_ENTITIES',
            parent: '#',
            icon: 'fa fa-hashtag',
            allowed: true,
            text: this.lang.myEntities
        }, {
            id: 'MY_PRIMARY_ENTITY',
            keyword: 'MY_PRIMARY_ENTITY',
            parent: '#',
            icon: 'fa fa-hashtag',
            allowed: true,
            text: this.lang.myPrimaryEntity
        }, {
            id: 'SAME_LEVEL_ENTITIES',
            keyword: 'SAME_LEVEL_ENTITIES',
            parent: '#',
            icon: 'fa fa-hashtag',
            allowed: true,
            text: this.lang.sameLevelMyPrimaryEntity
        }, {
            id: 'ENTITIES_JUST_UP',
            keyword: 'ENTITIES_JUST_UP',
            parent: '#',
            icon: 'fa fa-hashtag',
            allowed: true,
            text: this.lang.immediatelySuperiorMyPrimaryEntity
        }];
    }

    ngOnInit(): void {
        this.filteredActionList = this.myControl.valueChanges
            .pipe(
                startWith(''),
                map(value => this._filter(value))
            );

        this.getActions().pipe(
            exhaustMap(() => this.getSelectedActionsId()),
            tap((data: any) => this.setSelectedActions(data)),
            exhaustMap(() => this.getEntities()),
            tap((data: any) => this.initEntitiesTree(data))
        ).subscribe();

    }

    initEntitiesTree(entities: any) {
        $j('#jstree').jstree({
            "checkbox": {
                "three_state": false //no cascade selection
            },
            'core': {
                'themes': {
                    'name': 'proton',
                    'responsive': true
                },
                'data': entities
            },
            "plugins": ["checkbox", "search"]
        });
    }

    getEntities() {
        return this.http.get('../../rest/entities').pipe(
            map((data: any) => {
                data.entities = this.keywordEntities.concat(data.entities);
                data.entities.forEach((entity: any) => {
                    entity.state = { "opened": true, "selected": false };
                });
                return data.entities;
            })
        )
    }

    getActions() {
        return this.http.get('../../rest/entities').pipe(
            tap((data: any) => {
                // FOR TEST
                this.actionList = [
                    {
                        id: 1,
                        label: 'toto'
                    },
                    {
                        id: 2,
                        label: 'tata'
                    },
                    {
                        id: 9,
                        label: 'titi'
                    }
                ];
            })
        );
    }

    getSelectedActionsId() {
        return this.http.get('../../rest/entities').pipe(
            map((data: any) => {
                // FOR TEST
                data = [1, 2];
                return data;
            })
        )
    }

    setSelectedActions(actionsId: number[]) {
        let index = -1;
        actionsId.forEach((actionId: any) => {
            index = this.actionList.findIndex(action => action.id === actionId);
            if (index > -1) {
                this.indexingInfo.actions.push(this.actionList[index]);
                this.actionList.splice(index, 1);
            };
        });
    }

    addAction(actionOpt: MatAutocompleteSelectedEvent) {
        const index = this.actionList.findIndex(action => action.id === actionOpt.option.value);
        this.indexingInfo.actions.push(this.actionList[index]);
        this.actionList.splice(index, 1);
    }

    private _filter(value: string): any[] {
        if (typeof value === 'string') {
            const filterValue = value.toLowerCase();
            return this.actionList.filter(option => option.label.toLowerCase().includes(filterValue));
        } else {
            return this.actionList;
        }
    }
}
