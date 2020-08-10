import { NestedTreeControl } from '@angular/cdk/tree';
import { Component, Input, OnInit, HostListener, Output, EventEmitter } from '@angular/core';
import { MatTreeNestedDataSource } from '@angular/material/tree';
import { SortPipe } from '../../plugins/sorting.pipe';
import { FormControl } from '@angular/forms';
import { tap, debounceTime } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';


/** Flat node with expandable and level information */
interface ExampleFlatNode {
    expandable: boolean;
    item: string;
    parent_id: string;
    level: number;
}

/**
 * @title Tree with flat nodes
 */
@Component({
    selector: 'app-maarch-flat-tree',
    templateUrl: 'maarch-flat-tree.component.html',
    styleUrls: ['maarch-flat-tree.component.scss'],
    providers: [SortPipe],
})
export class MaarchFlatTreeComponent implements OnInit {

    @Input() rawData: any = [];

    @Output() afterSelectNode = new EventEmitter<any>();
    @Output() afterDeselectNode = new EventEmitter<any>();

    holdShift: boolean = false;

    treeControl = new NestedTreeControl<any>(node => node.children);
    dataSource = new MatTreeNestedDataSource<any>();

    searchMode: boolean = false;
    searchTerm: FormControl = new FormControl('');

    lastSelectedNodeIds: any[] = [];

    @HostListener('document:keydown.Shift', ['$event']) onKeydownHandler(event: KeyboardEvent) {
        this.holdShift = true;
    }
    @HostListener('document:keyup.Shift', ['$event']) onKeyupHandler(event: KeyboardEvent) {
        this.holdShift = false;
    }

    constructor(
        private sortPipe: SortPipe,
        private latinisePipe: LatinisePipe,
    ) { }

    ngOnInit(): void {
        // SAMPLE
        /* this.rawData = [
            {
                id: '46',
                text: 'bonjour',
                parent_id: null,
                icon: 'fa fa-building',
                state: {
                    selected: true,
                }
            },
            {
                id: '42',
                text: 'coucou',
                parent_id: '46',
                icon: 'fa fa-building',
                state: {
                    selected: true,
                }
            },
            {
                id: '41',
                text: 'coucou',
                parent_id: '42',
                icon: 'fa fa-building',
                state: {
                    selected: true,
                }
            },
            {
                id: '1',
                text: 'Compétences fonctionnelles',
                parent_id: null,
                icon: 'fa fa-building',
                state: {
                    selected: true,
                }
            },
            {
                id: '232',
                text: 'Compétences technique',
                parent_id: null,
                icon: 'fa fa-building',
                state: {
                    selected: true,
                }
            }
        ]; */
        if (this.rawData.length > 0) {
            this.initData();
        }
    }

    initData(data: any = this.rawData) {
        this.rawData = data.map((item: any) => {
            return {
                ...item,
                parent_id : item.parent_id === '#' ? null : item.parent_id,
                state: {
                    selected: item.state.selected,
                    opened: item.state.opened,
                    disabled: item.state.disabled,
                }
            };
        });

        this.rawData = this.sortPipe.transform(this.rawData, 'parent_id');

        const nestedData = this.flatToNestedObject(this.rawData);

        this.dataSource.data = nestedData;
        this.treeControl.dataNodes = nestedData;

        this.searchTerm.valueChanges
            .pipe(
                debounceTime(300),
                // filter(value => value.length > 2),
                tap((filterValue: any) => {
                    filterValue = filterValue.trim(); // Remove whitespace
                    filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
                    this.searchNode(this.dataSource.data, filterValue);
                }),
            ).subscribe();
    }

    getData(id: any) {
        return this.rawData.filter((elem: any) => elem.id === id)[0];
    }

    getIteration(it: number) {
        return Array(it).fill(0).map((x, i) => i);
    }

    flatToNestedObject(data: any) {
        const nested = data.reduce((initial: any, value: any, index: any, original: any) => {
            if (value.parent_id === '') {
                if (initial.left.length) {
                    this.checkLeftOvers(initial.left, value);
                }
                delete value.parent_id;
                value.root = true;
                initial.nested.push(value);
                initial.nested = this.sortPipe.transform(initial.nested, 'text');
                initial.nested = initial.nested.map((info: any, indexPar: number) => {
                    return {
                        ...info,
                        last: initial.nested.length - 1 === indexPar,
                    };
                });
            } else {
                const parentFound = this.findParent(initial.nested, value);
                if (parentFound) {
                    this.checkLeftOvers(initial.left, value);
                } else {
                    initial.left.push(value);
                }
            }
            return index < original.length - 1 ? initial : initial.nested;
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
                // delete possibleChild.parent_id;
                if (possibleParents[i].children) {
                    possibleParents[i].children.push(possibleChild);
                } else {
                    possibleParents[i].children = [possibleChild];
                }
                possibleParents[i].count = possibleParents[i].children.length;
                possibleParents[i].children = this.sortPipe.transform(possibleParents[i].children, 'text');
                possibleParents[i].children = possibleParents[i].children.map((info: any, index: number) => {
                    return {
                        ...info,
                        last: possibleParents[i].children.length - 1 === index,
                    };
                });
                return true;
            } else if (possibleParents[i].children) {
                found = this.findParent(possibleParents[i].children, possibleChild);
            }
        }
        return found;
    }

    hasChild = (_: number, node: any) => !!node.children && node.children.length > 0;

    selectNode(node: any) {
        if (!node.state.disabled) {
            if (this.searchMode) {
                this.searchMode = false;
                this.searchTerm.setValue('');
            }

            this.lastSelectedNodeIds = [];

            if (this.holdShift) {
                this.toggleNode(
                    this.dataSource.data,
                    {
                        selected: !node.state.selected,
                        opened: true
                    },
                    [node.id]
                );
            } else {
                node.state.selected = !node.state.selected;
                this.lastSelectedNodeIds = [node];
            }

            if (node.state.selected) {
                this.afterSelectNode.emit(this.lastSelectedNodeIds);
            } else {
                this.afterDeselectNode.emit(this.lastSelectedNodeIds);
            }
        }
    }

    toggleNode(data, state, nodeIds) {
        // traverse throuh each node
        if (Array.isArray(data)) { // if data is an array
            data.forEach((d) => {

                if (nodeIds.indexOf(d.id) > -1 || (this.holdShift && nodeIds.indexOf(d.parent_id) > -1)) {
                    Object.keys(state).forEach(key => {
                        if (d.state.disabled && key === 'opened') {
                            d.state[key] = state[key];
                        } else if (!d.state.disabled) {
                            d.state[key] = state[key];
                            if (key === 'selected') {
                                this.lastSelectedNodeIds.push(d);
                            }
                        }
                    });
                }
                if (this.holdShift && nodeIds.indexOf(d.parent_id) > -1) {
                    nodeIds.push(d.id);
                }

                this.toggleNode(d, state, nodeIds);

            }); // call the function on each item
        } else if (data instanceof Object) { // otherwise, if data is an object
            (data.children || []).forEach((f) => {
                if (nodeIds.indexOf(f.id) > -1 || (this.holdShift && nodeIds.indexOf(f.parent_id) > -1)) {
                    Object.keys(state).forEach(key => {
                        if (f.state.disabled && key === 'opened') {
                            f.state[key] = state[key];
                        } else if (!f.state.disabled) {
                            f.state[key] = state[key];
                            if (key === 'selected') {
                                this.lastSelectedNodeIds.push(f);
                            }
                        }
                    });
                }
                if (this.holdShift && nodeIds.indexOf(f.parent_id) > -1) {
                    nodeIds.push(f.id);
                }
                this.toggleNode(f, state, nodeIds);

            }); // and call function on each child
        }
    }

    searchNode(data, term) {
        this.searchMode = term !== '';
        // traverse throuh each node
        if (Array.isArray(data)) { // if data is an array
            data.forEach((d) => {
                d.state.opened = true;
                if (this.latinisePipe.transform(d.text.toLowerCase()).indexOf(this.latinisePipe.transform(term)) > -1) {
                    d.state.search = true;
                } else if (term === '') {
                    delete d.state.search;
                } else {
                    d.state.search = false;
                }
                this.searchNode(d, term);

            }); // call the function on each item
        } else if (data instanceof Object) { // otherwise, if data is an object
            (data.children || []).forEach((f) => {
                f.state.opened = true;
                if (this.latinisePipe.transform(f.text.toLowerCase()).indexOf(this.latinisePipe.transform(term)) > -1) {
                    f.state.search = true;
                } else if (term === '') {
                    delete f.state.search;
                } else {
                    f.state.search = false;
                }
                this.searchNode(f, term);

            }); // and call function on each child
        }
    }

    getSelectedNodes() {
        return this.rawData.filter((data: any) => data.state.selected);
    }
}
