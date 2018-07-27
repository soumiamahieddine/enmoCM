import { Component, OnInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';

declare function $j(selector: any) : any;
declare var angularGlobals : any;


@Component({
    selector: 'basket-home',
    templateUrl :   "../../../../Views/basket-home.component.html",
})
export class BasketHomeComponent implements OnInit {

    lang: any = LANG;
    coreUrl : string;
    @Input() homeData: any;

    constructor(public http: HttpClient) {
    }

    ngOnInit(): void {        
        this.coreUrl = angularGlobals.coreUrl;
    }

}
