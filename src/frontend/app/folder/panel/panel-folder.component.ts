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
    @ViewChild('folderTree', { static: true }) folderTree: FolderTreeComponent;
    
    constructor() { }

    ngOnInit(): void { }

    getDragIds() {
        if (this.folderTree !== undefined) {
            return this.folderTree.getDragIds();
        } else {
            return [];
        }
    }

    initTree() {
        this.folderTree.openTree(this.id);
    }
}
