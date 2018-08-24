import { Component, OnInit, Input, NgZone, ViewChild  } from '@angular/core';
import { LANG } from '../translate.component';
import { MatSidenav } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';

declare function $j(selector: any) : any;
declare var angularGlobals : any;


@Component({
    selector    : "main-header",
    templateUrl : "../../../../Views/main-header.component.html",
})
export class MainHeaderComponent implements OnInit {

    coreUrl    : string;
    lang       : any       = LANG;
    user       : any       = {};
    mobileMode : boolean   = false;
    titleHeader: string;
    router :any;

    snav : MatSidenav;
    snav2 : MatSidenav;

    constructor(public http: HttpClient, private zone: NgZone, private _router: Router) {
        this.router = _router;
        this.mobileMode = angularGlobals.mobileMode;
        window['MainHeaderComponent'] = {
            refreshTitle: (value: string) => this.setTitle(value),
            setSnav: (value: MatSidenav) => this.getSnav(value),
            setSnavRight: (value: MatSidenav) => this.getSnavRight(value),
        };
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.user = angularGlobals.user;
        this.http.get(this.coreUrl + 'rest/home')
            .subscribe((data: any) => {
                this.user.menu = data.menu;
                console.log(this.user.menu);
            }, (err) => {
                console.log(err.error.errors);
            });
    }

    setTitle(title: string) {
        this.zone.run(() => this.titleHeader = title);
    }

    getSnav(snav:MatSidenav) {
        this.zone.run(() => this.snav = snav);
    }

    getSnavRight(snav2:MatSidenav) {
        this.zone.run(() => this.snav2 = snav2);
    }

    gotToMenu(link:string, angularMode:string) {
        if (angularMode == 'true') {
            this.router.navigate([link]);
        } else{
            location.href = link;
        }
    }
}
