import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { of } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';

interface Tiles {
    'myLastResources': Tile;
    'basket': Tile;
    // 'searchTemplate': Tile;
    'followedMail': Tile;
    'folder': Tile;
    'externalSignatoryBook': Tile;
    'shortcut': Tile;
}

interface Tile {
    'icon': string; // icon of tile
    'menus': ('delete' | 'view')[]; // action of tile
    'views': TileView[]; // views tile
}

interface TileView {
    'id': 'list' | 'resume' | 'chart'; // identifier
    'route': string; // router when click on tile
    'viewDocRoute'?: string; // router when view a doc (usefull for list view)
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
                    route: '/resources/:resId',
                    viewDocRoute: '/resources/:resId/thumbnail'
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
        basket: {
            icon: 'fa fa-inbox',
            menus: [
                'view',
                'delete'
            ],
            views: [
                {
                    id: 'list',
                    route: '/process/users/:userId/groups/:groupId/baskets/:basketId/resId/:resId',
                    viewDocRoute: '/resources/:resId/thumbnail'
                },
                {
                    id: 'resume',
                    route: '/basketList/users/:userId/groups/:groupId/baskets/:basketId'
                },
                {
                    id: 'chart',
                    route: '/basketList/users/:userId/groups/:groupId/baskets/:basketId'
                }
            ]
        },
        /*searchTemplate : {
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
        },*/
        followedMail : {
            icon: 'fa fa-star',
            menus : [
                'view',
                'delete'
            ],
            views: [
                {
                    id: 'list',
                    route: '/resources/:resId',
                    viewDocRoute: '/resources/:resId/thumbnail'
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
                    route: '/resources/:resId',
                    viewDocRoute: '/resources/:resId/thumbnail'
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
                    route: ':maarchParapheurUrl/dist/documents/:id',
                    viewDocRoute: null
                },
                {
                    id: 'resume',
                    route: ':maarchParapheurUrl/dist/home'
                }
            ]
        },
        shortcut : {
            icon: null,
            menus : [
                'delete'
            ],
            views: [
                {
                    id: 'resume',
                    route: ':privRoute'
                }
            ]
        },
    };

    constructor(
        public http: HttpClient,
        public translate: TranslateService,
        private notify: NotificationService
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
            'doctype',
            'status'
        ];
    }

    get_list(tileId: number, extraParams: any): Promise<any[]> {
        return new Promise((resolve) => {
            this.http.get(`../rest/tiles/${tileId}`).pipe(
                tap((data: any) => {
                    const resources = data.tile.resources.map((resource: any) => {
                        let contactLabel = '';
                        let contactTitle = '';
                        if (resource.senders.length > 0) {
                            if (resource.senders.length === 1) {
                                contactLabel = resource.senders[0];
                                contactTitle = this.translate.instant('lang.sender') + ': ' + resource.senders[0];
                            } else {
                                contactLabel = resource.senders.length + ' ' + this.translate.instant('lang.senders');
                                contactTitle = resource.senders;
                            }
                        } else if (resource.recipients.length > 0) {
                            if (resource.recipients.length === 1) {
                                contactLabel = resource.recipients[0];
                                contactTitle = this.translate.instant('lang.sender') + ': ' + resource.recipients[0];
                            } else {
                                contactLabel = resource.recipients.length + ' ' + this.translate.instant('lang.recipients');
                                contactTitle = resource.recipients;
                            }
                        }
                        delete resource.recipients;
                        delete resource.senders;
                        return {
                            ...resource,
                            contactLabel: contactLabel,
                            contactTitle: contactTitle
                        };
                    });
                    resolve(resources);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    get_resume(tileId: number, extraParams: any): Promise<number> {
        return new Promise((resolve) => {
            this.http.get(`../rest/tiles/${tileId}`).pipe(
                tap((data: any) => {
                    const countResources = data.tile.resourcesNumber;
                    resolve(countResources);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    get_chart(tileId: number, extraParams: any): Promise<any[]> {
        return new Promise((resolve) => {
            this.http.get(`../rest/tiles/${tileId}`).pipe(
                tap((data: any) => {
                    const resources = data.tile.resources;
                    resolve(resources);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getFormatedRoute(route: string, data: any) {
        const regex = /:\w*/g;
        const res = route.match(regex);

        let formatedRoute = route;
        let errors = [];

        if (res !== null) {
            let routeIdValue = null;
            errors = res.slice();

            res.forEach((routeId: any) => {
                routeIdValue = data[routeId.replace(':', '')];
                if (routeIdValue !== undefined) {
                    formatedRoute = formatedRoute.replace(routeId, routeIdValue);
                    errors.splice(errors.indexOf(routeId), 1);
                }
            });
        }

        if (errors.length === 0) {
            return formatedRoute;
        } else {
            this.notify.error(errors + ' not found');
            return false;
        }
    }
}
