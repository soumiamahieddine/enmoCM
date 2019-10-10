import { Component, OnInit, ViewChild, Input, Renderer2, Output, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { map, tap, catchError, filter, exhaustMap, finalize } from 'rxjs/operators';
import { FlatTreeControl } from '@angular/cdk/tree';
import { trigger, transition, style, animate } from '@angular/animations';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { MatInput } from '@angular/material/input';
import { MatTreeFlatDataSource, MatTreeFlattener } from '@angular/material/tree';
import { BehaviorSubject, of } from 'rxjs';
import { NotificationService } from '../notification.service';
import { ConfirmComponent } from '../../plugins/modal/confirm.component';
import { Router } from '@angular/router';
import { FolderUpdateComponent } from './folder-update/folder-update.component';

declare function $j(selector: any): any;
/**
 * Node for to-do item
 */
export class ItemNode {
    id: number;
    children: ItemNode[];
    label: string;
    parent_id: number;
    public: boolean;
    countResources: number;
}

/** Flat to-do item node with expandable and level information */
export class ItemFlatNode {
    id: number;
    label: string;
    parent_id: number;
    countResources: number;
    level: number;
    public: boolean;
    expandable: boolean;
}
@Component({
    selector: 'folder-tree',
    templateUrl: "folder-tree.component.html",
    styleUrls: ['folder-tree.component.scss'],
    providers: [NotificationService],
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
    dialogRef: MatDialogRef<any>;
    createRootNode: boolean = false;
    createItemNode: boolean = false;
    dataChange = new BehaviorSubject<ItemNode[]>([]);

    @Input('selectedId') seletedId: number;
    @ViewChild('itemValue', { static: true }) itemValue: MatInput;


    get data(): ItemNode[] { return this.dataChange.value; }

    /** Map from flat node to nested node. This helps us finding the nested node to be modified */
    flatNodeMap = new Map<ItemFlatNode, ItemNode>();

    /** Map from nested node to flattened node. This helps us to keep the same object for selection */
    nestedNodeMap = new Map<ItemNode, ItemFlatNode>();

    private transformer = (node: ItemNode, level: number) => {
        const existingNode = this.nestedNodeMap.get(node);
        const flatNode = existingNode && existingNode.label === node.label
            ? existingNode
            : new ItemFlatNode();
        flatNode.label = node.label;
        flatNode.countResources = node.countResources;
        flatNode.public = node.public;
        flatNode.parent_id = node.parent_id;
        flatNode.id = node.id;
        flatNode.level = level;
        flatNode.expandable = !!node.children;
        this.flatNodeMap.set(flatNode, node);
        this.nestedNodeMap.set(node, flatNode);
        return flatNode;
    };

    treeControl = new FlatTreeControl<any>(
        node => node.level, node => node.expandable);

    treeFlattener = new MatTreeFlattener(
        this.transformer, node => node.level, node => node.expandable, node => node.children);

    dataSource = new MatTreeFlatDataSource(this.treeControl, this.treeFlattener);

    @ViewChild('tree', { static: true }) tree: any;
    
    @Output('refreshDocList') refreshDocList = new EventEmitter<string>();
    @Output('refreshFolderList') refreshFolderList = new EventEmitter<string>();

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        private dialog: MatDialog,
        private router: Router,
        private renderer: Renderer2
    ) { }

    ngOnInit(): void {
        this.getFolders();
    }

    getFolders() {
        this.http.get("../../rest/folders").pipe(
            map((data: any) => this.flatToNestedObject(data.folders)),
            filter((data: any) => data.length > 0),
            tap((data) => this.initTree(data)),
            //filter(() => this.seletedId !== undefined),
            tap(() => {
                this.openTree(this.seletedId);
                this.selectTree(this.seletedId);
            })
        ).subscribe();
    }

    initTree(data: any) {
        this.dataChange.next(data);
        this.dataChange.subscribe(data => {
            this.dataSource.data = data;
        });
    }

    openTree(id: any) {
        let indexSelectedFolder = this.treeControl.dataNodes.map((folder: any) => folder.id).indexOf(parseInt(id));

        while (indexSelectedFolder != -1) {
            indexSelectedFolder = this.treeControl.dataNodes.map((folder: any) => folder.id).indexOf(this.treeControl.dataNodes[indexSelectedFolder].parent_id);
            if (indexSelectedFolder != -1) {
                this.treeControl.expand(this.treeControl.dataNodes[indexSelectedFolder]);
            }
        }
    }

    selectTree(id: any) {
        let indexSelectedFolder = this.treeControl.dataNodes.map((folder: any) => folder.id).indexOf(parseInt(id));
        if (indexSelectedFolder != -1) {
            this.treeControl.dataNodes[indexSelectedFolder].selected = true;
        }
    }

    hasChild = (_: number, node: any) => node.expandable;

    hasNoContent = (_: number, _nodeData: any) => _nodeData.label === '';

    selectFolder(node: any) {
        this.treeControl.dataNodes.forEach(element => {
            element.selected = false;
        });
        node.selected = true;
        this.router.navigate(['/folders/' + node.id]);
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
                if (initial.left.length) {
                    this.checkLeftOvers(initial.left, value);
                }
                delete value.parent_id;
                value.root = true;
                initial.nested.push(value)
            }
            else {
                let parentFound = this.findParent(initial.nested, value);
                if (parentFound) {
                    this.checkLeftOvers(initial.left, value);
                } else {
                    initial.left.push(value);
                }
            }
            return index < original.length - 1 ? initial : initial.nested
        }, { nested: [], left: [] });
        return nested;
    }

    checkLeftOvers(leftOvers: any, possibleParent: any) {
        for (let i = 0; i < leftOvers.length; i++) {
            if (leftOvers[i].parent_id === possibleParent.id) {
                // delete leftOvers[i].parent_id;
                possibleParent.children ? possibleParent.children.push(leftOvers[i]) : possibleParent.children = [leftOvers[i]];
                possibleParent.count = possibleParent.children.length;
                const addedObj = leftOvers.splice(i, 1);
                this.checkLeftOvers(leftOvers, addedObj[0]);
            }
        }
    }

    findParent(possibleParents: any, possibleChild: any): any {
        let found = false;
        for (let i = 0; i < possibleParents.length; i++) {
            if (possibleParents[i].id === possibleChild.parent_id) {
                found = true;
                //delete possibleChild.parent_id;
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

    addNewItem(node: any) {
        this.createItemNode = true;
        const currentNode = this.flatNodeMap.get(node);
        if (currentNode.children === undefined) {
            currentNode['children'] = [];
        }
        currentNode.children.push({ label: '', parent_id: currentNode.id, public : currentNode.public } as ItemNode);
        this.dataChange.next(this.data);

        this.treeControl.expand(node);
        this.renderer.selectRootElement('#itemValue').focus();
    }

    saveNode(node: any, value: any) {
        this.http.post("../../rest/folders", { label: value, parent_id: node.parent_id }).pipe(
            tap((data: any) => {
                const nestedNode = this.flatNodeMap.get(node);
                nestedNode.label = value;
                nestedNode.id = data.folder;
                nestedNode.countResources = 0;
                this.dataChange.next(this.data);
                this.treeControl.collapseAll();
                this.openTree(nestedNode.id);
                this.createItemNode = false;
            }),
            tap(() => this.notify.success(this.lang.folderAdded)),
            catchError((err) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    createRoot(value: any) {
        this.http.post("../../rest/folders", { label: value }).pipe(
            tap(() => {
                this.getFolders();
            }),
            tap(() => this.notify.success(this.lang.folderAdded)),
            catchError((err) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    deleteNode(node: any) {

        this.dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete, msg: this.lang.confirmAction } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.delete("../../rest/folders/" + node.id)),
            tap(() => {
                const parentNode = this.getParentNode(node);

                if (parentNode !== null) {
                    const index = parentNode.children.map(node => node.id).indexOf(node.id);

                    if (index !== -1) {
                        parentNode.children.splice(index, 1);
                    }
                } else {
                    const index = this.data.map(node => node.id).indexOf(node.id);
                    if (index !== -1) {
                        this.data.splice(index, 1);
                    }
                }
                this.flatNodeMap.delete(node);
                this.dataChange.next(this.data);

            }),
            tap(() => this.notify.success(this.lang.folderDeleted)),
            catchError((err) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    private getParentNode(node: any) {
        const currentLevel = node.level;
        if (currentLevel < 1) {
            return null;
        }
        const startIndex = this.treeControl.dataNodes.indexOf(node) - 1;
        for (let i = startIndex; i >= 0; i--) {
            const currentNode = this.treeControl.dataNodes[i];
            if (currentNode.level < currentLevel) {
                return this.flatNodeMap.get(currentNode);
            }
        }
        return null;
    }

    drop(ev: any, node: any) {
        this.classifyDocument(ev, node);
        /*if (ev.previousContainer.id === 'folder-list') {
            this.moveFolder(ev, node);
        } else {
            this.classifyDocument(ev, node);
        }*/
    }

    moveFolder(ev: any, node: any) {
        this.dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.move + ' ' + ev.item.data.alt_identifier, msg: this.lang.moveQuestion + ' <b>' + ev.item.data.alt_identifier + '</b> ' + this.lang.in + ' <b>' + node.label + '</b>&nbsp;?' } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            //exhaustMap(() => this.http.post("../../rest/folders/" + node.id)),
            tap(() => {
                node.countResources = node.countResources + 1;
            }),
            tap(() => this.notify.success('Courrier déplacé')),
            tap(() => this.getFolders()),
            finalize(() => node.drag = false),
            catchError((err) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    classifyDocument(ev: any, node: any) {
        this.dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.classify + ' ' + ev.item.data.alt_identifier, msg: this.lang.classifyQuestion + ' <b>' + ev.item.data.alt_identifier + '</b> ' + this.lang.in + ' <b>' + node.label + '</b>&nbsp;?' } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.post('../../rest/folders/' + node.id + '/resources', { resources: [ev.item.data.res_id] })),
            tap((data: any) => {
                node.countResources = data.countResources;
            }),
            tap(() => {
                this.notify.success(this.lang.mailClassified);
                this.refreshDocList.emit();
            }),
            finalize(() => node.drag = false),
            catchError((err) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    dragEnter(node: any) {
        node.drag = true;
    }

    getDragIds() {
        if (this.treeControl.dataNodes !== undefined) {
            return this.treeControl.dataNodes.map(node => 'folder-list-' + node.id);
        } else {
            return [];
        }
    }

    toggleInput() {
        this.createRootNode = !this.createRootNode;
        if (this.createRootNode) {
            setTimeout(() => {
                this.renderer.selectRootElement('#itemValue').focus();
            }, 0);
        }
    }

    openFolderAdmin(node: any) {
        this.dialogRef = this.dialog.open(FolderUpdateComponent, { autoFocus: false, data: { folderId: node.id } });

        this.dialogRef.afterClosed().pipe(
            tap((data) => {
                if (data !== undefined) {
                    this.getFolders();
                }                
            })
        ).subscribe();
    }

    checkRights(node: any) {
        let userEntities: any[] = [];
        let currentUserId: number = 0;
        this.http.get("../../rest/currentUser/profile").pipe(
            tap((data: any) => {
                userEntities = data.entities.map((info: any) => info.id);
                currentUserId = data.id;
            }),
            exhaustMap(() => this.http.get("../../rest/folders/" + node.id)),
            tap((data: any) => {
                let canAdmin = false;

                let canAdd = true;
                
                const compare = data.folder.sharing.entities.filter((item: any) => userEntities.indexOf(item) > -1);

                const entitiesCompare = data.folder.sharing.entities.filter((item: any) => compare.indexOf(item.id));

                entitiesCompare.forEach((element: any) => {
                    if (element.edition === true) {
                        canAdmin = true;
                    }
                });
                if (data.folder.user_id !== currentUserId && node.public) {
                    canAdd = false;
                }
                node.edition = (canAdmin || data.folder.user_id === currentUserId) ? true : false;
                node.canAdd = node.edition;
            }),
        ).subscribe();
    }

    goTo(folder: any) {
        this.seletedId = folder.id;
        this.getFolders();
        this.router.navigate(["/folders/" + folder.id]);
    }
}
