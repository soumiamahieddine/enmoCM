import { Component, OnInit, NgZone } from '@angular/core';
import { LANG }                 from '../translate.component';
import { MatSidenav }           from '@angular/material';
import { HttpClient }           from '@angular/common/http';
import { Router }               from '@angular/router';
import { ShortcutMenuService }  from '../../service/shortcut-menu.service';


declare function $j(selector: any) : any;
declare var angularGlobals : any;


@Component({
    selector    : "main-header",
    templateUrl : "main-header.component.html"
})
export class MainHeaderComponent implements OnInit {

    coreUrl         : string;
    lang            : any       = LANG;
    user            : any       = {firstname : "",lastname : ""};
    mobileMode      : boolean   = false;
    titleHeader     : string;
    router          : any;

    snav            : MatSidenav;
    snav2           : MatSidenav;


    constructor(public http: HttpClient, private zone: NgZone, private _router: Router, private shortcut: ShortcutMenuService) {
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
        this.http.get(this.coreUrl + 'rest/header')
            .subscribe((data: any) => {
                this.user = data.user;
                this.user.menu = data.menu;

                this.shortcut.shortcutsData.user = data.user;
                this.shortcut.shortcutsData.menu = data.menu;
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
        if (snav2 == null) {
            $j('#snav2Button').hide();
        }else {
            $j('#snav2Button').show();
            this.zone.run(() => this.snav2 = snav2);
        }
    }

    gotToMenu(link:string, angularMode:string) {
        if (angularMode == 'true') {
            this.router.navigate([link]);
        } else{
            location.href = link;
        }
    }
}
