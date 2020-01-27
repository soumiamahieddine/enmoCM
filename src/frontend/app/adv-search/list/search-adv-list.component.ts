import { Component, OnInit, ViewChild, EventEmitter, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService }        from '../../../service/header.service';
import { AppService } from '../../../service/app.service';
import { Observable, merge, Subject, of as observableOf, of } from 'rxjs';
import { MatPaginator, MatSort, MatDialog } from '@angular/material';
import { takeUntil, startWith, switchMap, map, catchError, filter, exhaustMap, tap, debounceTime, distinctUntilChanged } from 'rxjs/operators';
import { FormControl } from '@angular/forms';
import { FunctionsService } from '../../../service/functions.service';

@Component({
    selector: 'search-adv-list',
    templateUrl: "search-adv-list.component.html",
    styleUrls: ['search-adv-list.component.scss'],
    providers: [AppService]
})
export class SearchAdvListComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    filtersChange = new EventEmitter();
    
    data: any;

    displayedColumnsResource: string[] = ['action', 'resId', 'status', 'subject', 'type', 'creationDate'];

    selectedRes: number[] = [];
    allResInSearch: number[] = [];

    isLoadingResults = true;
    routeUrl: string = '../../rest/search';
    resultListDatabase: ResourceListHttpDao | null;
    resultsLength = 0;

    searchResource = new FormControl();

    @Input('search') search: string = '';

    @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
    @ViewChild('tableResourceListSort', { static: true }) sort: MatSort;

    private destroy$ = new Subject<boolean>();
    
    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        private headerService: HeaderService,
        public appService: AppService,
        public dialog: MatDialog,
        public functions: FunctionsService) { }

    ngOnInit(): void {
        this.loading = true;
        this.initResourceList();
    }

    initResourceList() {
        this.resultListDatabase = new ResourceListHttpDao(this.http);
        this.paginator.pageIndex = 0;
        this.sort.active = 'resId';
        this.sort.direction = 'desc';
        this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

        // When list is refresh (sort, page, filters)
        merge(this.sort.sortChange, this.paginator.page, this.filtersChange)
            .pipe(
                takeUntil(this.destroy$),
                startWith({}),
                switchMap(() => {
                    this.isLoadingResults = true;
                    return this.resultListDatabase!.getRepoIssues(
                        this.sort.active, this.sort.direction, this.paginator.pageIndex, this.routeUrl, this.search);
                }),
                map(data => {
                    this.selectedRes = [];
                    this.isLoadingResults = false;
                    data = this.processPostData(data);
                    this.resultsLength = data.count;
                    this.allResInSearch = data.allResources;
                    return data.resources;
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    this.isLoadingResults = false;
                    return observableOf([]);
                })
            ).subscribe(data => this.data = data);
    }

    processPostData(data: any) {
        // FOR TEST
        data = {
            resources : [
                {
                  "resId": 101,
                  "chrono": "MAARCH/2020A/2",
                  "subject": "test qualif",
                  "barcode": null,
                  "filename": "0048_1580814739.pdf",
                  "creationDate": "2020-01-06 22:37:30.652561",
                  "type": 112,
                  "priority": "poiuytre1357nbvc",
                  "status": "EAVIS",
                  "destUser": "bbain",
                  "priorityColor": "#009dc5",
                  "statusLabel": "Avis demandé",
                  "statusImage": "fa-lightbulb",
                  "destUserLabel": "Barbara BAIN",
                  "hasDocument": true,
                  "senders": {
                    "id": 6,
                    "type": "contact",
                    "mode": "sender"
                  },
                  "recipients": [],
                  "attachments": 1
                }
              ],
              count: 1,
              allResources : [100]
        }
        return data;
    }

    refreshDao() {
        this.filtersChange.emit();
    }

    toggleRes(e: any, row: any) {
        if (e.checked) {
            if (this.selectedRes.indexOf(row.resId) === -1) {
                this.selectedRes.push(row.resId);
                row.checked = true;
            }
        } else {
            let index = this.selectedRes.indexOf(row.resId);
            this.selectedRes.splice(index, 1);
            row.checked = false;
        }
    }

    toggleAllRes(e: any) {
        this.selectedRes = [];
        if (e.checked) {
            this.data.forEach((element: any) => {
                element['checked'] = true;
            });
            this.selectedRes = JSON.parse(JSON.stringify(this.allResInSearch));
        } else {
            this.data.forEach((element: any) => {
                element['checked'] = false;
            });
        }
    }

    getSelectedRessources() {
        return this.selectedRes;
    }

}

export interface ResourceList {
    resources: any[];
    count: number;
    allResources : number[]
}
export class ResourceListHttpDao {

    constructor(private http: HttpClient) { }

    getRepoIssues(sort: string, order: string, page: number, href: string, search: string): Observable<ResourceList> {
        
        let offset = page * 10;
        const requestUrl = `${href}?limit=10&offset=${offset}&order=${order}&orderBy=${sort}${search}`;

        return this.http.get<ResourceList>(requestUrl);
    }
}