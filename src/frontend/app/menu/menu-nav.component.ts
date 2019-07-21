import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';

declare function $j(selector: any) : any;

@Component({
    selector: 'menu-nav',
    templateUrl :   "menuNav.component.html",
})
export class MenuNavComponent implements OnInit {

    lang: any = LANG;
    router :any;
    user       : any       = {};

    constructor(
        public http: HttpClient, 
        private _router: Router, 
        private activatedRoute:ActivatedRoute
    ) {
        this.router = _router;
    }

    ngOnInit(): void { }

    backClicked() {
        this.router.navigate(['../'],{ relativeTo: this.activatedRoute });
    }
}
