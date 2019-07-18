import { Component, OnInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../../../translate.component';
import { NotificationService } from '../../../notification.service';
import { tap } from 'rxjs/internal/operators/tap';
import { exhaustMap, startWith, map } from 'rxjs/operators';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { MatAutocompleteSelectedEvent } from '@angular/material';
import { LatinisePipe } from 'ngx-pipes';

declare function $j(selector: any): any;

@Component({
    selector: 'app-indexing-administration',
    templateUrl: 'indexing-administration.component.html',
    styleUrls: ['indexing-administration.component.scss'],
    providers: [NotificationService]
})
export class IndexingAdministrationComponent implements OnInit {

    mobileQuery: MediaQueryList;

    lang: any = LANG;
    loading: boolean = false;

    @Input('groupId') groupId: number;

    keywordEntities: any[] = [];
    actionList: any[] = [];

    myControl = new FormControl();

    indexingInfo: any = {
        canIndex: false,
        actions: [],
        keywords: [],
        entities: []
    };
    filteredActionList: Observable<any[]>;

    constructor(public http: HttpClient,
        private notify: NotificationService,
        private latinisePipe: LatinisePipe
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

        this.getIndexingInformations().pipe(
            tap((data: any) => this.indexingInfo.canIndex = data.group.canIndex),
            tap((data: any) => this.getActions(data.actions)),
            tap((data: any) => this.getSelectedActions(data.group.indexationParameters.actions)),
            map((data: any) => this.getEntities(data)),
            map((data: any) => this.getSelectedEntities(data)),
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

        $j('#jstree')
            // listen for event
            .on('select_node.jstree', (e: any, data: any) => {
                if (isNaN(data.node.id)) {
                    this.addKeyword(data.node.id);
                } else {
                    this.addEntity(data.node.id);
                }

            }).on('deselect_node.jstree', (e: any, data: any) => {
                if (isNaN(data.node.id)) {
                    this.removeKeyword(data.node.id);
                } else {
                    this.removeEntity(data.node.id);
                }
            })
            // create the instance
            .jstree();
    }

    getEntities(data: any) {
        data.entities = this.keywordEntities.concat(data.entities);
        return data;
    }

    getSelectedEntities(data: any) {
        this.indexingInfo.entities = [...data.group.indexationParameters.entities];
        data.entities.forEach((entity: any) => {
            if (this.indexingInfo.entities.indexOf(entity.id) > -1 ) {
                entity.state = { "opened": true, "selected": true };
            } else {
                entity.state = { "opened": true, "selected": false };
            }
            
        });
        return data.entities;
    }

    getActions(data: any) {
        this.actionList = data;
    }

    getIndexingInformations() {
        return this.http.get('../../rest/groups/' + this.groupId + '/indexing');
    }

    getSelectedActions(data: any) {
        let index = -1;
        data.forEach((actionId: any) => {
            index = this.actionList.findIndex(action => action.id === actionId);
            if (index > -1) {
                this.indexingInfo.actions.push(this.actionList[index]);
                this.actionList.splice(index, 1);
            };
        });
    }

    addEntity(entityId: number) {
        this.indexingInfo.entities.push(entityId);
        console.log(this.indexingInfo.entities);
    }

    removeEntity(entityId: number) {
        const index = this.indexingInfo.entities.indexOf(entityId);
        this.indexingInfo.entities.splice(index, 1);
        console.log(this.indexingInfo.entities);
    }

    addKeyword(keyword: string) {
        this.indexingInfo.keywords.push(keyword);
        console.log(this.indexingInfo.keywords);
    }

    removeKeyword(keyword: string) {
        const index = this.indexingInfo.keywords.indexOf(keyword);
        this.indexingInfo.keywords.splice(index, 1);
        console.log(this.indexingInfo.keywords);
    }

    addAction(actionOpt: MatAutocompleteSelectedEvent) {
        const index = this.actionList.findIndex(action => action.id === actionOpt.option.value);
        const action = {...this.actionList[index]};
        this.indexingInfo.actions.push(action);
        this.actionList.splice(index, 1);
        $j('#addAction').blur();
    }

    removeAction(index: number) {
        const action = {...this.indexingInfo.actions[index]};
        this.actionList.push(action);
        this.indexingInfo.actions.splice(index, 1);
    }

    private _filter(value: string): any[] {
        if (typeof value === 'string') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.actionList.filter(option => this.latinisePipe.transform(option.label_action.toLowerCase()).includes(filterValue));
        } else {
            return this.actionList;
        }
    }
}
