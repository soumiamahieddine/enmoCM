import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import {Location} from '@angular/common';
import { LANG } from '../translate.component';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    selector: 'menu-shortcut',
    templateUrl :   "../../../../Views/menuShortcut.component.html",
})
export class MenuShortcutComponent implements OnInit {

    lang: any = LANG;
    mobileMode                      : boolean   = false;
    coreUrl                     : string;
    router :any;
    user       : any       = {};

    constructor(public http: HttpClient, private _location: Location, private _router: Router, private activatedRoute:ActivatedRoute) {
        this.mobileMode = angularGlobals.mobileMode;
        this.router = _router;

        this.coreUrl = angularGlobals.coreUrl;

        this.http.get(this.coreUrl + 'rest/header')
        .subscribe((data: any) => {
            this.user = data.user;
            this.user.menu = data.menu;
        }, (err) => {
            console.log(err.error.errors);
        });
    }

    ngOnInit(): void {      
        this.coreUrl = angularGlobals.coreUrl;
    }

    gotToMenu(link:string, angularMode:string) {
        if (angularMode == 'true') {
            this.router.navigate([link]);
        } else{
            location.href = link;
        }
    }

}
