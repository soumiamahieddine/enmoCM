import { Injectable } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { LatinisePipe } from 'ngx-pipes';

@Injectable()
export class DashboardService {

    tileTypes: any = {
        lastViewMails : {
            menus : [
                'delete',
                'view'
            ],
            views: [
                'list',
                'resume',
                'chart'
            ]
        },
        basket : {
            menus : [
                'delete',
                'view'
            ],
            views: [
                'list',
                'resume',
                'chart'
            ]
        },
        savedQuery : {
            menus : [
                'delete',
                'view'
            ],
            views: [
                'list',
                'resume',
                'chart'
            ]
        },
        followedMails : {
            menus : [
                'delete',
                'view'
            ],
            views: [
                'list',
                'resume',
                'chart'
            ]
        },
        folder : {
            menus : [
                'delete',
                'view'
            ],
            views: [
                'list',
                'resume',
                'chart'
            ]
        },
        externalSignatureBook : {
            menus : [
                'delete',
                'view'
            ],
            views: [
                'list',
                'resume',
                'chart'
            ]
        },
        shortcut : {
            menus : [
                'delete'
            ],
            views: []
        },
    };

    constructor(
        public translate: TranslateService,
        private latinisePipe: LatinisePipe,
    ) { }

    getTile(id: string) {
        return this.tileTypes[id];
    }
}
