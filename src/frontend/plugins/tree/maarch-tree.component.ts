import {
    CollectionViewer,
    SelectionChange,
    DataSource
} from '@angular/cdk/collections';
import { FlatTreeControl } from '@angular/cdk/tree';
import { Component, Injectable, Input, OnInit } from '@angular/core';
import { BehaviorSubject, merge, Observable } from 'rxjs';
import { map, tap } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';

/** Flat node with expandable and level information */
export class DynamicFlatNode {
    constructor(
        public item: string,
        public level = 1,
        public expandable = false,
        public isLoading = false
    ) { }
}

/**
 * Database for dynamic data. When expanding a node in the tree, the data source will need to fetch
 * the descendants data from the database.
 */
@Injectable({ providedIn: 'root' })
export class DynamicDatabase {
    dataMap = new Map<string, string[]>([]);

    rootLevelNodes: string[] = [];

    /** Initial data from database */
    initialData(): DynamicFlatNode[] {
        return this.rootLevelNodes.map(name => new DynamicFlatNode(name, 0, true));
    }

    setData(node: any) {
        console.log(node.childrens);
        return this.dataMap.set(node.id, node.childrens);
    }

    setRootNode(rootnodes: string[]) {
        this.rootLevelNodes = rootnodes;
    }

    getChildren(node: string): string[] | undefined {
        return this.dataMap.get(node);
    }

    isExpandable(node: string): boolean {
        return this.dataMap.has(node);
    }
}
/**
 * File database, it can build a tree structured Json object from string.
 * Each node in Json object represents a file or a directory. For a file, it has filename and type.
 * For a directory, it has filename and children (a list of files or directories).
 * The input will be a json object string, and the output is a list of `FileNode` with nested
 * structure.
 */
export class DynamicDataSource implements DataSource<DynamicFlatNode> {
    dataChange = new BehaviorSubject<DynamicFlatNode[]>([]);

    get data(): DynamicFlatNode[] {
        return this.dataChange.value;
    }
    set data(value: DynamicFlatNode[]) {
        this._treeControl.dataNodes = value;
        this.dataChange.next(value);
    }

    constructor(
        private _treeControl: FlatTreeControl<DynamicFlatNode>,
        private _database: DynamicDatabase,
        private rawData: any,
        private httpClient: HttpClient
    ) { }

    connect(collectionViewer: CollectionViewer): Observable<DynamicFlatNode[]> {
        this._treeControl.expansionModel.changed.subscribe(change => {
            if (
                (change as SelectionChange<DynamicFlatNode>).added ||
                (change as SelectionChange<DynamicFlatNode>).removed
            ) {
                this.handleTreeControl(change as SelectionChange<DynamicFlatNode>);
            }
        });

        return merge(collectionViewer.viewChange, this.dataChange).pipe(
            map(() => this.data)
        );
    }

    disconnect(collectionViewer: CollectionViewer): void { }

    /** Handle expand/collapse behaviors */
    handleTreeControl(change: SelectionChange<DynamicFlatNode>) {
        if (change.added) {
            change.added.forEach(node => this.toggleNode(node, true));
        }
        if (change.removed) {
            change.removed
                .slice()
                .reverse()
                .forEach(node => this.toggleNode(node, false));
        }
    }

    /**
     * Toggle the node, remove from display list
     */
    toggleNode(node: DynamicFlatNode, expand: boolean) {
        let children = this._database.getChildren(node.item);
        let index = this.data.indexOf(node);
        if (!this.rawData[index].hasChildren) {
            // If no children, or cannot find the node, no op
            return;
        }

        node.isLoading = true;

        if (expand) {
            if (children === undefined) {
                this.httpClient
                    .get('../rest/entities')
                    .pipe(
                        tap(() => {
                            console.log(node);
                            this.rawData.push({
                                id: 34,
                                label: 'cool',
                                parent_id: node.item,
                                hasChildren: false
                            });

                            const folders = this.rawData.map((elem: any) => elem.id);

                            this.rawData.forEach((element: any) => {
                                const node = {
                                    id: element.id,
                                    childrens: this.rawData
                                        .filter((elem: any) => elem.parent_id === element.id)
                                        .map((elem: any) => elem.id)
                                };
                                if (
                                    this.rawData.filter((elem: any) => elem.parent_id === element.id).length > 0
                                ) {
                                    this._database.setData(node);
                                }
                            });

                            children = this._database.getChildren(node.item);
                            index = this.data.indexOf(node);

                            const nodes = children.map(
                                name =>
                                    new DynamicFlatNode(
                                        name,
                                        node.level + 1,
                                        this._database.isExpandable(name)
                                    )
                            );
                            console.log(nodes);
                            this.data.splice(index + 1, 0, ...nodes);
                            // notify the change
                            this.dataChange.next(this.data);
                            node.isLoading = false;
                        })
                    )
                    .subscribe();
            } else {
                const nodes = children.map(
                    name =>
                        new DynamicFlatNode(
                            name,
                            node.level + 1,
                            this._database.isExpandable(name)
                        )
                );
                console.log(nodes);
                this.data.splice(index + 1, 0, ...nodes);
                // notify the change
                this.dataChange.next(this.data);
                node.isLoading = false;
            }
        } else {
            let count = 0;
            for (
                let i = index + 1;
                i < this.data.length && this.data[i].level > node.level;
                i++, count++
            ) { }
            this.data.splice(index + 1, count);
            // notify the change
            this.dataChange.next(this.data);
            node.isLoading = false;
        }
    }
}

/**
 * @title Tree with dynamic data
 */
@Component({
    selector: 'app-maaarch-tree',
    templateUrl: 'maarch-tree.component.html',
    styleUrls: ['maarch-tree.component.scss']
})
export class MaarchTreeComponent implements OnInit {

    @Input() rawData: any = [];

    constructor(
        private database: DynamicDatabase,
        private httpClient: HttpClient
    ) {
        this.treeControl = new FlatTreeControl<DynamicFlatNode>(
            this.getLevel,
            this.isExpandable
        );
    }

    treeControl: FlatTreeControl<DynamicFlatNode>;

    dataSource: DynamicDataSource;

    getLevel = (node: DynamicFlatNode) => node.level;

    isExpandable = (node: DynamicFlatNode) => node.expandable;

    hasChild = (_: number, _nodeData: DynamicFlatNode) =>
        this.getData(_nodeData.item).hasChildren

    ngOnInit(): void {
        console.log('init!');
        // SAMPLE
        this.rawData = [
            {
                id: '46',
                label: 'bonjour',
                parent_id: null,
                hasChildren: true
            },
            {
                id: '1',
                label: 'Compétences fonctionnelles',
                parent_id: null,
                hasChildren: false
            }
        ];
        this.dataSource = new DynamicDataSource(
            this.treeControl,
            this.database,
            this.rawData,
            this.httpClient
        );
        this.initTree();
    }

    initTree() {
        this.rawData.forEach((element: any) => {
            const node = {
                id: element.id,
                childrens: this.rawData
                    .filter((elem: any) => elem.parent_id === element.id)
                    .map((elem: any) => elem.id)
            };
            if (
                this.rawData.filter((elem: any) => elem.parent_id === element.id).length > 0
            ) {
                this.database.setData(node);
            }
        });

        this.database.setRootNode(
            this.rawData.filter((elem: any) => elem.parent_id === null).map((elem: any) => elem.id)
        );
        this.dataSource.data = this.database.initialData();
    }

    getData(id: any) {
        return this.rawData.filter((elem: any) => elem.id === id)[0];
    }
}
