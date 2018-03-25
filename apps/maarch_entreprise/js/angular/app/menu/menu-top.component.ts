import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    selector: 'menu-top',
    templateUrl :   "../../../../Views/menu-top.component.html",
})
export class MenuTopComponent implements OnInit {

    lang: any = LANG;
    coreUrl                     : string;
    user : any = {};

    constructor(public http: HttpClient) {
    }

    ngOnInit(): void {        
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + 'rest/headerInformations')
            .subscribe((data) => {
               this.user = data;
               this.user.firstnameL = this.user.firstname.charAt(0);
            });
    }
}
