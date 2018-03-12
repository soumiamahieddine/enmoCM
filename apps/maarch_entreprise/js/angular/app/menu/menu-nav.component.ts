import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {Location} from '@angular/common';
import { LANG } from '../translate.component';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    selector: 'menu-nav',
    templateUrl :   angularGlobals["menuNavView"],
})
export class MenuNavComponent implements OnInit {

    lang: any = LANG;
    coreUrl                     : string;

    constructor(public http: HttpClient, private _location: Location) {
    }

    ngOnInit(): void {        
        this.coreUrl = angularGlobals.coreUrl;
    }

    backClicked() {
        this._location.back();
    }
}
