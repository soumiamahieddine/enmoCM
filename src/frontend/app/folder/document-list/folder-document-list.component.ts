import { Component, OnInit, ViewChild, EventEmitter, ViewContainerRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { merge, Observable, of as observableOf, Subject  } from 'rxjs';
import { NotificationService } from '../../notification.service';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSidenav } from '@angular/material/sidenav';
import { MatSort } from '@angular/material/sort';

import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { startWith, switchMap, map, catchError, takeUntil, tap } from 'rxjs/operators';
import { ActivatedRoute, Router } from '@angular/router';
import { HeaderService } from '../../../service/header.service';

import { Overlay } from '@angular/cdk/overlay';
import { PanelListComponent } from '../../list/panel/panel-list.component';
import { AppService } from '../../../service/app.service';
import { PanelFolderComponent } from '../panel/panel-folder.component';


declare function $j(selector: any): any;

@Component({
    templateUrl: "folder-document-list.component.html",
    styleUrls: ['folder-document-list.component.scss'],
    providers: [NotificationService, AppService],
})
export class FolderDocumentListComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = false;
    docUrl: string = '';
    public innerHtml: SafeHtml;
    basketUrl: string;
    homeData: any;
    
    injectDatasParam = {
        resId: 0,
        editable: false
    };
    currentResource: any = {};

    filtersChange = new EventEmitter();

    @ViewChild('snav', { static: false }) sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: false }) sidenavRight: MatSidenav;

    displayedColumnsBasket: string[] = ['res_id'];

    displayedMainData: any = [
        {
            'value': 'alt_identifier',
            'cssClasses': ['softColorData', 'align_centerData', 'chronoData'],
            'icon': ''
        },
        {
            'value': 'subject',
            'cssClasses': ['longData'],
            'icon': ''
        }
    ];

    resultListDatabase: ResultListHttpDao | null;
    data: any;
    resultsLength = 0;
    isLoadingResults = true;
    listProperties: any = {};
    currentChrono: string = '';
    currentMode: string = '';

    thumbnailUrl: string = '';

    selectedRes: Array<number> = [];
    allResInBasket: number[] = [];
    selectedDiffusionTab: number = 0;
    folderInfo: any = {
        id: 0
    };

    private destroy$ = new Subject<boolean>();

    @ViewChild('appPanelList', { static: false }) appPanelList: PanelListComponent;

    currentSelectedChrono: string = '';

    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild('tableBasketListSort', { static: false }) sort: MatSort;
    @ViewChild('panelFolder', { static: false }) panelFolder: PanelFolderComponent;

    constructor(
        private router: Router, 
        private route: ActivatedRoute, 
        public http: HttpClient, 
        public dialog: MatDialog, 
        private sanitizer: DomSanitizer, 
        private headerService: HeaderService, 
        private notify: NotificationService, 
        public overlay: Overlay, 
        public viewContainerRef: ViewContainerRef,
        public appService: AppService) {
            $j("link[href='merged_css.php']").remove();
    }

    ngOnInit(): void {
        this.loading = false;

        this.http.get("../../rest/home")
            .subscribe((data: any) => {
                this.homeData = data;
            });

        this.isLoadingResults = false;

        this.route.params.subscribe(params => {
            this.destroy$.next(true);

            this.basketUrl = '../../rest/folders/' + params['folderId'] + '/resources';
            this.folderInfo = 
            {
                "id": params['folderId'],
                "label": 'test'

            };
            this.selectedRes = [];
            this.sidenavRight.close();
            window['MainHeaderComponent'].setSnav(this.sidenavLeft);
            window['MainHeaderComponent'].setSnavRight(null);

            this.initResultList();

        },
            (err: any) => {
                this.notify.handleErrors(err);
            });
    }

    ngOnDestroy() {
        this.destroy$.next(true);
    }

    initResultList() {
        this.resultListDatabase = new ResultListHttpDao(this.http);
        // If the user changes the sort order, reset back to the first page.
        this.paginator.pageIndex = this.listProperties.page;
        this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

        // When list is refresh (sort, page, filters)
        merge(this.sort.sortChange, this.paginator.page, this.filtersChange)
            .pipe(
                takeUntil(this.destroy$),
                startWith({}),
                switchMap(() => {
                    this.isLoadingResults = true;
                    return this.resultListDatabase!.getRepoIssues(
                        this.sort.active, this.sort.direction, this.paginator.pageIndex, this.basketUrl);
                }),
                map(data => {
                    // Flip flag to show that loading has finished.
                    this.isLoadingResults = false;
                    data = this.processPostData(data);
                    this.resultsLength = data.countResources;
                    //this.allResInBasket = data.count;
                    //this.headerService.setHeader('Dossier : ' + this.folderInfo.label);
                    return data.resources;
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    this.router.navigate(['/home']);
                    this.isLoadingResults = false;
                    return observableOf([]);
                })
            ).subscribe(data => this.data = data);
    }

    goTo(row: any) {
        if (this.docUrl == '../../rest/res/' + row.res_id + '/content' && this.sidenavRight.opened) {
            this.sidenavRight.close();
        } else {
            this.docUrl = '../../rest/res/' + row.res_id + '/content';
            this.currentChrono = row.alt_identifier;
            this.innerHtml = this.sanitizer.bypassSecurityTrustHtml(
                "<iframe style='height:100%;width:100%;' src='" + this.docUrl + "' class='embed-responsive-item'>" +
                "</iframe>");
            this.sidenavRight.open();
        }
    }

    goToDetail(row: any) {
        location.href = "index.php?page=details&dir=indexing_searching&id=" + row.res_id;
    }

    togglePanel(mode: string, row: any) {
        let thisSelect = { checked : true };
        let thisDeselect = { checked : false };
        row.checked = true;
        this.toggleAllRes(thisDeselect);
        this.toggleRes(thisSelect, row);

        if(this.currentResource.res_id == row.res_id && this.sidenavRight.opened && this.currentMode == mode) {
            this.sidenavRight.close();
        } else {
            this.currentMode = mode;
            this.currentResource = row;
            this.appPanelList.loadComponent(mode, row);
            this.sidenavRight.open();
        }
    }

    refreshBadgeNotes(nb: number) {
        this.currentResource.countNotes = nb;
    }                                     

    refreshBadgeAttachments(nb: number) {
        this.currentResource.countAttachments = nb;
    }

    refreshDao() {
        this.paginator.pageIndex = this.listProperties.page;
        this.filtersChange.emit();
    }

    viewThumbnail(row: any) {
        this.thumbnailUrl = '../../rest/res/' + row.res_id + '/thumbnail';
        $j('#viewThumbnail').show();
        $j('#listContent').css({ "overflow": "hidden" });
    }

    closeThumbnail() {
        $j('#viewThumbnail').hide();
        $j('#listContent').css({ "overflow": "auto" });
    }

    processPostData(data: any) {
        data.resources.forEach((element: any) => {
            // Process main datas
            Object.keys(element).forEach((key) => {
                if (key == 'statusImage' && element[key] == null) {
                    element[key] = 'fa-question undefined';
                } else if ((element[key] == null || element[key] == '') && ['closingDate', 'countAttachments', 'countNotes', 'display'].indexOf(key) === -1) {
                    element[key] = this.lang.undefined;
                }
            });

            element['checked'] = this.selectedRes.indexOf(element['res_id']) !== -1;
        });

        return data;
    }

    toggleRes(e: any, row: any) {
        if (e.checked) {
            if (this.selectedRes.indexOf(row.res_id) === -1) {
                this.selectedRes.push(row.res_id);
                row.checked = true;
            }
        } else {
            let index = this.selectedRes.indexOf(row.res_id);
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
            this.selectedRes = JSON.parse(JSON.stringify(this.allResInBasket));
        } else {
            this.data.forEach((element: any) => {
                element['checked'] = false;
            });
        }
    }

    unclassify() {
        this.http.request('DELETE', '../../rest/folders/' + this.folderInfo.id + '/resources', { body: { resources: this.selectedRes } }).pipe(
            tap((data: any) => {
                this.notify.success(this.lang.removedFromFolder);
                this.resultsLength = data.countResources;
                this.data.forEach((resource: any, key: number) => {
                    if (this.selectedRes.indexOf(resource.res_id) != -1) {
                        this.data.splice(key, 1);
                    }
                });
            })
        ).subscribe();
    }
}
export interface BasketList {
    folder: any;
    resources: any[];
    countResources: number;
}

export class ResultListHttpDao {

    constructor(private http: HttpClient) { }

    getRepoIssues(sort: string, order: string, page: number, href: string): Observable<BasketList> {
        let offset = page * 10;
        const requestUrl = `${href}?limit=10&offset=${offset}`;

        return this.http.get<BasketList>(requestUrl);
    }
}
