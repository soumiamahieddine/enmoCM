import { Component, OnInit, AfterViewInit, QueryList, ViewChildren } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { DashboardService } from './dashboard.service';
import { FunctionsService } from '@service/functions.service';
import { TileCreateComponent } from './tile/tile-create.component';
import { catchError, exhaustMap, filter, tap } from 'rxjs/operators';
import { MatDialog } from '@angular/material/dialog';
import { ConfirmComponent } from '@plugins/modal/confirm.component';
import { of } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';

@Component({
    selector: 'app-dashboard',
    templateUrl: 'dashboard.component.html',
    styleUrls: ['dashboard.component.scss'],
    providers: [DashboardService]
})
export class DashboardComponent implements OnInit, AfterViewInit {

    tiles: any = [];
    hoveredTool: boolean = false;

    @ViewChildren('tileComponent') tileComponent: QueryList<any>;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        public dashboardService: DashboardService,
        private functionsService: FunctionsService,
        public dialog: MatDialog,
    ) { }

    ngOnInit(): void {
        this.getDashboardConfig();
    }

    ngAfterViewInit(): void { }

    enterTile(tile: any, index: number) {
        this.hoveredTool = false;
        this.tiles.forEach((element: any, indexTile: number) => {
            element.editMode = indexTile === index ? true : false;
        });
    }

    leaveTile(tile: any) {
        if (!this.hoveredTool) {
            tile.editMode = false;
        }
    }

    getDashboardConfig() {
        this.http.get('../rest/tiles').pipe(
            tap((data: any) => {
                for (let index = 0; index < 6; index++) {
                    const tmpTile = data.tiles.find((tile: any) => tile.position === index);
                    if (!this.functionsService.empty(tmpTile)) {
                        const objTile = {...this.dashboardService.getTile(tmpTile.type), ...tmpTile};
                        this.tiles.push(objTile);
                    } else {
                        this.tiles.push({
                            id: null,
                            position: index,
                            editMode: false
                        });
                    }
                }
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    changeView(tile: any, view: string, extraParams: any = null) {
        const indexTile = this.tiles.filter((tileItem: any) => tileItem.id !== null).map((tileItem: any) => tileItem.position).indexOf(tile.position);
        this.tileComponent.toArray()[indexTile].changeView(view, extraParams);
        tile.view = view;
        if (extraParams !== null) {
            tile.parameters = extraParams;
        }
        this.http.put(`../rest/tiles/${tile.id}`, tile).pipe(
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    transferDataSuccess() {
        this.tiles.forEach((tile: any, index: number) => {
            tile.position = index;
        });
        this.http.put('../rest/tilesPositions', {tiles : this.tiles.filter((tile: any) => tile.id !== null)}).pipe(
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    addTilePrompt(tile: any) {
        const dialogRef = this.dialog.open(TileCreateComponent, { panelClass: 'maarch-modal', width: '450px', autoFocus: false, disableClose: true, data: { position: tile.position} });

        dialogRef.afterClosed().pipe(
            filter((data: string) => !this.functionsService.empty(data)),
            tap((data: any) => {
                this.tiles[tile.position] = {...this.dashboardService.getTile(data.type), ...data};
            })
        ).subscribe();
    }

    launchAction(action: string, tile: any) {
        this[action](tile);
    }

    delete(tile: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { panelClass: 'maarch-modal', autoFocus: false, disableClose: true, data: { title: this.translate.instant('lang.delete'), msg: this.translate.instant('lang.confirmAction') } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.delete(`../rest/tiles/${tile.id}`)),
            tap(() => {
                this.tiles[tile.position] = {
                    id: null,
                    position: tile.position,
                    editMode: false
                };
            })
        ).subscribe();
    }
}
