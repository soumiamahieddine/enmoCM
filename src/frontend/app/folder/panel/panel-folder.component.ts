import { Component, OnInit, Input, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { FolderTreeComponent } from '../folder-tree.component';

declare function $j(selector: any): any;

@Component({
    selector: 'panel-folder',
    templateUrl: "panel-folder.component.html",
    styleUrls: ['panel-folder.component.scss'],
})
export class PanelFolderComponent implements OnInit {

    lang: any = LANG;

    @Input('selectedId') id: number;
    @ViewChild('folderTree', { static: false }) folderTree: FolderTreeComponent;
    
    constructor() { }

    ngOnInit(): void { }

    getDragIds() {
        return this.folderTree.getDragIds();
    }
}
