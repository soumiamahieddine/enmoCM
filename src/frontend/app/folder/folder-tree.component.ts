import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { AppService } from '../../service/app.service';
import { map } from 'rxjs/operators';
import { FlatTreeControl } from '@angular/cdk/tree';
import { trigger, transition, style, animate, state } from '@angular/animations';
import { MatTreeFlatDataSource, MatTreeFlattener } from '@angular/material';

declare function $j(selector: any): any;

@Component({
    selector: 'folder-tree',
    templateUrl: "folder-tree.component.html",
    styleUrls: ['folder-tree.component.scss'],
    animations: [
        trigger('hideShow', [
            transition(
                ':enter', [
                    style({ height: '0px' }),
                    animate('200ms', style({ 'height': '30px' }))
                ]
            ),
            transition(
                ':leave', [
                    style({ height: '30px' }),
                    animate('200ms', style({ 'height': '0px' }))
                ]
            )
        ]),
    ],
})
export class FolderTreeComponent implements OnInit {

    lang: any = LANG;
    TREE_DATA: any[] = [];

    private transformer = (node: any, level: number) => {
        return {
            expandable: !!node.children && node.children.length > 0,
            id: node.id,
            parent: node.parent,
            label: node.label,
            level: level,
        };
    }

    treeControl = new FlatTreeControl<any>(
        node => node.level, node => node.expandable);

    treeFlattener = new MatTreeFlattener(
        this.transformer, node => node.level, node => node.expandable, node => node.children);

    dataSource = new MatTreeFlatDataSource(this.treeControl, this.treeFlattener);

    @ViewChild('tree') tree: any;

    constructor(
        public http: HttpClient,
        public appService: AppService
    ) {

        this.http.get("../../rest/folders").pipe(
            map((data: any) => {

                data = this.flatToNestedObject(data.folders);
                this.TREE_DATA = data;
                this.dataSource.data = this.TREE_DATA;
                let indexSelectedFolder = this.treeControl.dataNodes.map((folder: any) => folder.id).indexOf(3);
                this.treeControl.dataNodes[indexSelectedFolder].selected = true;

                while (indexSelectedFolder != -1) {
                    indexSelectedFolder = this.treeControl.dataNodes.map((folder: any) => folder.id).indexOf(this.treeControl.dataNodes[indexSelectedFolder].parent);
                    if (indexSelectedFolder != -1) {
                        this.treeControl.expand(this.treeControl.dataNodes[indexSelectedFolder]);
                    }
                }

                return data;
            }),
        ).subscribe();
    }

    ngOnInit(): void {

    }

    hasChild = (_: number, node: any) => node.expandable;

    selectFolder(node: any) {
        this.treeControl.dataNodes.forEach(element => {
            element.selected = false;
        });
        node.selected = true;
    }

    showAction(node: any) {
        this.treeControl.dataNodes.forEach(element => {
            element.showAction = false;
        });
        node.showAction = true;
    }

    hideAction(node: any) {
        node.showAction = false;
    }

    flatToNestedObject(data: any) {
        const nested = data.reduce((initial: any, value: any, index: any, original: any) => {
            if (value.parent_id === null) {
                if (initial.left.length) this.checkLeftOvers(initial.left, value)
                delete value.parent_id
                value.root = true;
                initial.nested.push(value)
            }
            else {
                let parentFound = this.findParent(initial.nested, value)
                if (parentFound) this.checkLeftOvers(initial.left, value)
                else initial.left.push(value)
            }
            return index < original.length - 1 ? initial : initial.nested
        }, { nested: [], left: [] })
        return nested;
    }

    checkLeftOvers(leftOvers: any, possibleParent: any) {
        for (let i = 0; i < leftOvers.length; i++) {
            if (leftOvers[i].parent_id === possibleParent.id) {
                delete leftOvers[i].parent_id;
                possibleParent.children ? possibleParent.children.push(leftOvers[i]) : possibleParent.children = [leftOvers[i]];
                possibleParent.count = possibleParent.children.length;
                const addedObj = leftOvers.splice(i, 1);
                this.checkLeftOvers(leftOvers, addedObj[0]);
            }
        }
    }

    findParent(possibleParents: any, possibleChild: any): any {
        let found = false
        for (let i = 0; i < possibleParents.length; i++) {
            if (possibleParents[i].id === possibleChild.parent_id) {
                found = true;
                delete possibleChild.parent_id;
                if (possibleParents[i].children) {
                    possibleParents[i].children.push(possibleChild);
                } else {
                    possibleParents[i].children = [possibleChild];
                }
                possibleParents[i].count = possibleParents[i].children.length;
                return true
            } else if (possibleParents[i].children) {
                found = this.findParent(possibleParents[i].children, possibleChild);
            }
        }
        return found;
    }
}
