import { Component, OnInit, Input } from '@angular/core';
import { LANG } from '../../translate.component';

declare function $j(selector: any): any;

@Component({
    selector: 'panel-folder',
    templateUrl: "panel-folder.component.html",
    styleUrls: ['panel-folder.component.scss'],
})
export class PanelFolderComponent implements OnInit {

    lang: any = LANG;

    @Input('selectedId') id: number;
    
    constructor() { }

    ngOnInit(): void { }
}
