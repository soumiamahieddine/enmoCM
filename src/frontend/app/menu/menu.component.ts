import { Component, OnInit } from '@angular/core';
import { LANG } from '../translate.component';

@Component({
    selector: 'menu-app',
    templateUrl: 'menu.component.html',
})
export class MenuComponent implements OnInit {

    lang: any = LANG;

    constructor() {
    }

    ngOnInit(): void { }
}
