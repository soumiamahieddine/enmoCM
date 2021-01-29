import { Injectable } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { LatinisePipe } from 'ngx-pipes';

interface Tiles {
    'myLastResources': Tile;
    /*'basket': Tile;
    'searchTemplate': Tile;
    'followedMail': Tile;
    'folder': Tile;
    'externalSignatoryBook': Tile;
    'shortcut': Tile;*/
}

interface Tile {
    'icon': string; // icon of tile
    'menus': ('delete' | 'view')[]; // action of tile
    'views': TileView[]; // views tile
}

interface TileView {
    'id': 'list' | 'resume' | 'chart'; // identifier
    'route': string; // router when click on tile
}

@Injectable()
export class DashboardService {

    tileTypes: Tiles = {
        myLastResources : {
            icon: 'fa fa-history',
            menus : [
                'view',
                'delete'
            ],
            views: [
                {
                    id: 'list',
                    route: '/resources/:resId'
                },
                {
                    id: 'resume',
                    route: null
                },
                {
                    id: 'chart',
                    route: null
                }
            ]
        },
        /*basket : {
            icon: 'fa fa-inbox',
            menus : [
                'view',
                'delete'
            ],
            views: [
                {
                    id: 'list',
                    route: '/process/users/:userId/groups/:groupId/baskets/:basketId/resId/:resId'
                },
                {
                    id: 'resume',
                    route: '/process/users/:userId/groups/:groupId/baskets/:basketId'
                },
                {
                    id: 'chart',
                    route: '/process/users/:userId/groups/:groupId/baskets/:basketId'
                }
            ]
        },
        searchTemplate : {
            icon: 'fa fa-search',
            menus : [
                'view',
                'delete'
            ],
            views: [
                {
                    id: 'list',
                    route: '/resources/:resId'
                },
                {
                    id: 'resume',
                    route: '/search'
                },
                {
                    id: 'chart',
                    route: '/search'
                }
            ]
        },
        followedMail : {
            icon: 'fa fa-star',
            menus : [
                'view',
                'delete'
            ],
            views: [
                {
                    id: 'list',
                    route: '/resources/:resId'
                },
                {
                    id: 'resume',
                    route: '/followed'
                },
                {
                    id: 'chart',
                    route: '/followed'
                }
            ]
        },
        folder : {
            icon: 'fa fa-folder',
            menus : [
                'view',
                'delete'
            ],
            views: [
                {
                    id: 'list',
                    route: '/resources/:resId'
                },
                {
                    id: 'resume',
                    route: '/folders/:folderId'
                },
                {
                    id: 'chart',
                    route: '/folders/:folderId'
                }
            ]
        },
        externalSignatoryBook : {
            icon: 'fas fa-pen-nib',
            menus : [
                'view',
                'delete'
            ],
            views: [
                {
                    id: 'list',
                    route: ':signatoryBookPathresources/dist/documents/:resId'
                },
                {
                    id: 'resume',
                    route: ':signatoryBookPathresources/dist/home'
                },
                {
                    id: 'chart',
                    route: ':signatoryBookPathresources/dist/home'
                }
            ]
        },
        shortcut : {
            icon: null,
            menus : [
                'delete'
            ],
            views: []
        },*/
    };

    constructor(
        public translate: TranslateService,
        private latinisePipe: LatinisePipe,
    ) { }

    getTile(id: string) {
        return this.tileTypes[id];
    }

    getTileTypes() {
        return Object.keys(this.tileTypes);
    }

    getViewsByTileType(tileType: string) {
        return this.tileTypes[tileType].views;
    }

    getChartMode() {
        return [
            'type',
            'status'
        ];
    }
}
