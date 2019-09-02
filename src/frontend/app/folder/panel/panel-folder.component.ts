import { Component, OnInit, Input, ViewChild, Output, EventEmitter } from '@angular/core';
import { LANG } from '../../translate.component';
import { FolderTreeComponent } from '../folder-tree.component';
import { Router } from '@angular/router';

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
    
    @Output('refreshEvent') refreshEvent = new EventEmitter<string>();
    
    constructor(private router: Router) { }

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

    refreshDocList() {
        this.refreshEvent.emit();
    }

    refreshFoldersTree() {
        this.folderTree.getFolders();
    }

    goTo(folder: any) {
        this.router.navigate(["/folders/" + folder.id]);
    }
}
