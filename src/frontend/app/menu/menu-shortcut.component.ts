import { Component, OnInit }    from '@angular/core';
import { Router }               from '@angular/router';
import { HttpClient }           from '@angular/common/http';
import { Location }             from '@angular/common';
import { LANG }                 from '../translate.component';
import { ShortcutMenuService }  from '../../service/shortcut-menu.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    selector: 'menu-shortcut',
    templateUrl : "menu-shortcut.component.html",
})
export class MenuShortcutComponent implements OnInit {

    coreUrl     : string;
    lang        : any       = LANG;
    mobileMode  : boolean   = false;
    router      : any;

    constructor(public http: HttpClient, private _location: Location, private _router: Router, public shortcut: ShortcutMenuService) {
        this.mobileMode = angularGlobals.mobileMode;
        this.router = _router;
    }

    ngOnInit(): void {      
        this.coreUrl = angularGlobals.coreUrl;
    }

    gotToMenu(link: string, angularMode: string) {
        if (angularMode == 'true') {
            this.router.navigate([link]);
        } else{
            location.href = link;
        }
    }
}
