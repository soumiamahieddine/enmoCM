import { Component, OnInit, Input, EventEmitter, Output, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../service/app.service';
import { tap, finalize } from 'rxjs/operators';
import { MatExpansionPanel } from '@angular/material';

declare function $j(selector: any) : any;

@Component({
    selector: 'basket-home',
    templateUrl: "basket-home.component.html",
    styleUrls: ['basket-home.component.scss'],
})
export class BasketHomeComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = true;

    homeData: any = null;
    @Input('currentBasketInfo') currentBasketInfo: any = {
        ownerId: 0,
        groupId: 0,
        basketId: ''
    };
    @Input() snavL: MatSidenav;
    @Output('refreshEvent') refreshEvent = new EventEmitter<string>();
    @ViewChild('basketPanel', { static: true }) basketPanel: MatExpansionPanel;

    constructor(
        public http: HttpClient,
        public appService: AppService
    ) {
    }

    ngOnInit(): void {
        this.getMyBaskets();
    }

    getMyBaskets() {
        this.loading = true;

        this.http.get("../../rest/home").pipe(
            tap((data: any) => {
                this.homeData = data;
            }),
            finalize(() => {
                this.loading = false;
            })
        ).subscribe();
    }

    closePanelLeft() {
        if(this.appService.getViewMode()) {
            this.snavL.close();
        }
    }

    refreshDatas(basket: any) {
        this.refreshBasketHome();

        // AVOID DOUBLE REQUEST IF ANOTHER BASKET IS SELECTED
        if (this.currentBasketInfo.basketId == basket.id) {
            this.refreshEvent.emit();
        }
    }

    refreshBasketHome(){
        this.http.get("../../rest/home")
            .subscribe((data: any) => {
                this.homeData = data;
            });
    }

    togglePanel(state: boolean) {
        if (state) {
            this.basketPanel.open();
        } else {
            this.basketPanel.close();
        }
    }
}
