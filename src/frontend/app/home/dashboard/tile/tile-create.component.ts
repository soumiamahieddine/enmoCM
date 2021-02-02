import { Component, Inject, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { DashboardService } from '../dashboard.service';
import { FunctionsService } from '@service/functions.service';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { catchError, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';
import { HeaderService } from '@service/header.service';
import { ColorEvent } from 'ngx-color';

@Component({
    templateUrl: 'tile-create.component.html',
    styleUrls: ['tile-create.component.scss'],
    providers: [DashboardService]
})
export class TileCreateComponent implements OnInit {

    loading: boolean = false;

    tileTypes: any[] = [];
    views: any[] = [];
    baskets: any[] = [];

    position: string = null;
    selectedTileType: string = null;
    selectedView: string = null;
    selectedColor: string = '#90caf9';
    extraParams: any = {};

    colors: string[] = [
        '#ef9a9a',
        '#f48fb1',
        '#ce93d8',
        '#b39ddb',
        '#9fa8da',
        '#90caf9',
        '#81d4fa',
        '#80deea',
        '#80cbc4',
        '#a5d6a7',
        '#c5e1a5',
        '#e6ee9c',
        '#fff59d',
        '#ffe082',
        '#ffcc80',
        '#ffab91',
        '#bcaaa4',
        '#b0bec5',
    ];

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<TileCreateComponent>,
        public dashboardService: DashboardService,
        private functionsService: FunctionsService,
        private notify: NotificationService,
        public headerService: HeaderService
    ) { }

    ngOnInit(): void {
        this.position = this.data.position;
        this.getTileTypes();
    }

    getTileTypes() {
        const tmpTileTypes = this.dashboardService.getTileTypes();
        this.tileTypes = tmpTileTypes.map((tileType: any) => {
            return {
                id: tileType,
                label: this.translate.instant('lang.' + tileType)
            };
        });
    }

    getViews() {
        const tmpViews = this.dashboardService.getViewsByTileType(this.selectedTileType);
        this.views = tmpViews.map((view: any) => {
            return {
                ...view,
                label: this.translate.instant('lang.' + view.id)
            };
        });
        this.selectedView = this.views.length > 0 ? this.views[0].id : null;

        if (this.selectedTileType === 'basket') {
            this.getBaskets();
        }
    }

    getBaskets() {
        if (this.baskets.length === 0) {
            this.http.get('../rest/home').pipe(
                tap((data: any) => {
                    this.baskets = data.regroupedBaskets;
                    console.log(this.baskets[0]);
                    console.log(this.baskets[0].baskets[0]);

                    this.extraParams = {
                        groupId: this.baskets[0].groupSerialId,
                        basketId: this.baskets[0].baskets[0].id
                    };
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    compareBaskets(basket1: any, basket2: any) {
        return (basket1.groupId === basket2.groupId && basket1.basketId === basket2.basketId);
    }

    isValid() {
        return !this.functionsService.empty(this.position) && !this.functionsService.empty(this.selectedTileType) && ((this.views.length > 0 && !this.functionsService.empty(this.selectedView)) || this.views.length === 0);
    }

    formatData() {
        return {
            type: this.selectedTileType,
            view: this.selectedView,
            userId: this.headerService.user.id,
            position: this.position,
            parameters: this.extraParams
        };
    }

    resetExtraParams() {
        if (this.selectedView === 'chart') {
            this.extraParams['chartMode'] = 'doctype';
        } else {
            delete this.extraParams.chartMode;
        }
    }

    handleChange($event: ColorEvent) {
        console.log($event.color);
        this.selectedColor = $event.color.hex;
    }

    onSubmit() {
        const objToSend: any = this.formatData();
        this.http.post('../rest/tiles', objToSend).pipe(
            tap((data: any) => {
                this.dialogRef.close({
                    id: data.id,
                    type: objToSend.type,
                    view: objToSend.view,
                    position: this.position,
                    parameters: objToSend.parameters
                });
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
