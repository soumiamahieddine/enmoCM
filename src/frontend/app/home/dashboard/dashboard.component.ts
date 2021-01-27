import { Component, OnInit, AfterViewInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '@service/app.service';

@Component({
    selector: 'app-dashboard',
    templateUrl: 'dashboard.component.html',
    styleUrls: ['dashboard.component.scss']
})
export class DashboardComponent implements OnInit, AfterViewInit {

    tiles: any = [
        {
            id: 1,
            type: 'lastViewMails',
            view: 'resume',
            menus: [
                'delete',
                'view'
            ],
            views: [
                'list',
                'resume',
                'chart'
            ]
        },
        {
            id: null,
            type: null,
            view: null,
            menus: null,
            views: null
        },
        {
            id: 2,
            type: 'lastViewMails',
            view: 'list',
            menus: [
                'delete',
                'view'
            ],
            views: [
                'list',
                'resume',
                'chart'
            ]
        },
        {
            id: 3,
            type: 'lastViewMails',
            view: 'resume',
            menus: [
                'delete',
                'view'
            ],
            views: [
                'list',
                'resume',
                'chart'
            ]
        },
        {
            id: null,
            type: null,
            view: null,
            menus: null,
            views: null
        },
        {
            id: 4,
            type: 'lastViewMails',
            view: 'list',
            menus: [
                'delete',
                'view'
            ],
            views: [
                'list',
                'resume',
                'chart'
            ]
        },
    ];
    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
    ) { }

    ngOnInit(): void { }

    ngAfterViewInit(): void { }

    enterTile(tile: any, index: number) {
        this.tiles.forEach((element: any, indexTile: number) => {
            element.editMode = indexTile === index ? true : false;
        });
    }
}
