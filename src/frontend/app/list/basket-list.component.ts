import { Component, OnInit, ViewChild, EventEmitter, ViewContainerRef, ApplicationRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { merge, Observable, of as observableOf, Subject  } from 'rxjs';
import { NotificationService } from '../notification.service';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSidenav } from '@angular/material/sidenav';
import { MatSort } from '@angular/material/sort';

import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { startWith, switchMap, map, catchError, takeUntil } from 'rxjs/operators';
import { ActivatedRoute, Router } from '@angular/router';
import { HeaderService } from '../../service/header.service';
import { FiltersListService } from '../../service/filtersList.service';
import { FiltersToolComponent } from './filters/filters-tool.component';

import { ActionsListComponent } from '../actions/actions-list.component';
import { Overlay } from '@angular/cdk/overlay';
import { BasketHomeComponent } from '../basket/basket-home.component';
import { PanelListComponent } from './panel/panel-list.component';
import { AppService } from '../../service/app.service';
import { PanelFolderComponent } from '../folder/panel/panel-folder.component';


declare function $j(selector: any): any;

@Component({
    templateUrl: "basket-list.component.html",
    styleUrls: ['basket-list.component.scss'],
    providers: [NotificationService, AppService],
})
export class BasketListComponent implements OnInit {

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

    dragInit: boolean = true;

    @ViewChild('snav', { static: true }) sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) sidenavRight: MatSidenav;

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
    displayedSecondaryData: any = [];

    resultListDatabase: ResultListHttpDao | null;
    data: any;
    resultsLength = 0;
    isLoadingResults = true;
    listProperties: any = {};
    currentBasketInfo: any = {};
    currentChrono: string = '';
    currentMode: string = '';
    defaultAction = {
        id: 19,
        component : 'processAction'
    };
    thumbnailUrl: string = '';

    selectedRes: number[] = [];
    allResInBasket: number[] = [];
    selectedDiffusionTab: number = 0;

    private destroy$ = new Subject<boolean>();

    @ViewChild('actionsListContext', { static: true }) actionsList: ActionsListComponent;
    @ViewChild('filtersTool', { static: true }) filtersTool: FiltersToolComponent;
    @ViewChild('appPanelList', { static: true }) appPanelList: PanelListComponent;
    @ViewChild('basketHome', { static: true }) basketHome: BasketHomeComponent;
    @ViewChild('panelFolder', { static: true }) panelFolder: PanelFolderComponent;

    currentSelectedChrono: string = '';

    @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
    @ViewChild('tableBasketListSort', { static: true }) sort: MatSort;

    constructor(
        private router: Router, 
        private route: ActivatedRoute, 
        public http: HttpClient, 
        public dialog: MatDialog, 
        private sanitizer: DomSanitizer, 
        private headerService: HeaderService, 
        public filtersListService: FiltersListService, 
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
            this.dragInit = true;
            this.destroy$.next(true);

            this.basketUrl = '../../rest/resourcesList/users/' + params['userSerialId'] + '/groups/' + params['groupSerialId'] + '/baskets/' + params['basketId'];

            this.currentBasketInfo = {
                ownerId: params['userSerialId'],
                groupId: params['groupSerialId'],
                basketId: params['basketId']
            };
            this.filtersListService.filterMode = false;
            this.selectedRes = [];
            this.sidenavRight.close();
            window['MainHeaderComponent'].setSnav(this.sidenavLeft);
            window['MainHeaderComponent'].setSnavRight(null);

            this.listProperties = this.filtersListService.initListsProperties(this.currentBasketInfo.ownerId, this.currentBasketInfo.groupId, this.currentBasketInfo.basketId);


            setTimeout(() => {
                this.dragInit = false;
            }, 1000);
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
        this.resultListDatabase = new ResultListHttpDao(this.http, this.filtersListService);
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
                        this.sort.active, this.sort.direction, this.paginator.pageIndex, this.basketUrl, this.filtersListService.getUrlFilters());
                }),
                map(data => {
                    // Flip flag to show that loading has finished.
                    this.isLoadingResults = false;
                    data = this.processPostData(data);
                    this.resultsLength = data.count;
                    this.allResInBasket = data.allResources;
                    this.currentBasketInfo.basket_id = data.basket_id;
                    this.defaultAction = data.defaultAction;
                    this.headerService.setHeader(data.basketLabel);
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
        this.filtersListService.filterMode = false;
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

    refreshDaoAfterAction() {
        this.sidenavRight.close();
        this.refreshDao();
        this.basketHome.refreshBasketHome();
        const e:any = {checked : false};
        this.toggleAllRes(e); 
    }

    filterThis(value: string) {
        this.filtersTool.setInputSearch(value);
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
        this.displayedSecondaryData = [];
        data.resources.forEach((element: any) => {
            // Process main datas
            Object.keys(element).forEach((key) => {
                if (key == 'statusImage' && element[key] == null) {
                    element[key] = 'fa-question undefined';
                } else if ((element[key] == null || element[key] == '') && ['closingDate', 'countAttachments', 'countNotes', 'display', 'folders'].indexOf(key) === -1) {
                    element[key] = this.lang.undefined;
                }
            });

            // Process secondary datas
            element.display.forEach((key: any) => {
                key.displayTitle = key.displayValue;
                if ((key.displayValue == null || key.displayValue == '') && ['getCreationAndProcessLimitDates', 'getParallelOpinionsNumber'].indexOf(key.value) === -1) {
                    key.displayValue = this.lang.undefined;
                    key.displayTitle = '';
                } else if (["getSenders", "getRecipients"].indexOf(key.value) > -1) {
                    if (key.displayValue.length > 1) {
                        key.displayTitle = key.displayValue.join(' - ');
                        key.displayValue = '<b>' + key.displayValue.length + '</b> ' + this.lang.contacts;
                    } else {
                        key.displayValue = key.displayValue[0];
                    }
                } else if (key.value == 'getCreationAndProcessLimitDates') {
                    key.icon = '';
                } else if (key.value == 'getVisaWorkflow') {
                    let formatWorkflow: any = [];
                    let content = '';
                    let user = '';
                    let displayTitle: string[] = [];

                    key.displayValue.forEach((visa: any, key: number) => {
                        content = '';
                        user = visa.user;
                        displayTitle.push(user);

                        if (visa.mode == 'sign') {
                            user = '<u>' + user + '</u>';
                        }
                        if (visa.date == '') {
                            content = '<i class="fa fa-hourglass-half"></i> <span title="' + this.lang[visa.mode + 'User'] + '">' + user + '</span>';
                        } else {
                            content = '<span color="accent" style=""><i class="fa fa-check"></i> <span title="' + this.lang[visa.mode + 'User'] + '">' + user + '</span></span>';
                        }

                        if (visa.current && key >= 0) {
                            content = '<b color="primary">' + content + '</b>';
                        }

                        formatWorkflow.push(content);

                    });

                    //TRUNCATE DISPLAY LIST
                    const index = key.displayValue.map((e: any) => { return e.current; }).indexOf(true);
                    if (index > 0) {
                        formatWorkflow = formatWorkflow.slice(index - 1);
                        formatWorkflow = formatWorkflow.reverse();
                        const indexReverse = key.displayValue.map((e: any) => { return e.current; }).reverse().indexOf(true);
                        if (indexReverse > 1) {
                            formatWorkflow = formatWorkflow.slice(indexReverse - 1);
                        }
                        formatWorkflow = formatWorkflow.reverse();
                    } else if (index === 0) {
                        formatWorkflow = formatWorkflow.reverse();
                        formatWorkflow = formatWorkflow.slice(index - 2);
                        formatWorkflow = formatWorkflow.reverse();
                    } else if (index === -1) {
                        formatWorkflow = formatWorkflow.slice(formatWorkflow.length - 2);
                    }
                    if (index >= 2 || (index == -1 && key.displayValue.length >= 3)) {
                        formatWorkflow.unshift('...');
                    }
                    if (index != -1 && index - 2 <= key.displayValue.length && index + 2 < key.displayValue.length && key.displayValue.length >= 3) {
                        formatWorkflow.push('...');
                    }

                    key.displayValue = formatWorkflow.join(' <i class="fas fa-long-arrow-alt-right"></i> ');
                    key.displayTitle = displayTitle.join(' - ');
                } else if (key.value == 'getSignatories') {
                    let userList: any[] = [];
                    key.displayValue.forEach((visa: any) => {
                        userList.push(visa.user);
                    });
                    key.displayValue = userList.join(', ');
                    key.displayTitle = userList.join(', ');
                } else if (key.value == 'getParallelOpinionsNumber') {
                    key.displayTitle = key.displayValue + ' ' + this.lang.opinionsSent;

                    if (key.displayValue > 0) {
                        key.displayValue = '<b color="primary">' + key.displayValue + '</b> ' + this.lang.opinionsSent;
                    } else {
                        key.displayValue = key.displayValue + ' ' + this.lang.opinionsSent;
                    }
                }
                key.label = this.lang[key.value];
            });

            if (this.selectedRes.indexOf(element['res_id']) === -1) {
                element['checked'] = false;
            } else {
                element['checked'] = true;
            }
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

    open({ x, y }: MouseEvent, row: any) {
        
        let thisSelect = { checked : true };
        let thisDeselect = { checked : false };
        if ( row.checked === false) {
            row.checked = true;
            this.toggleAllRes(thisDeselect);
            this.toggleRes(thisSelect, row);
        }
        this.actionsList.open(x, y, row)

        // prevents default
        return false;
    }

    launch(action: any, row: any) {
        let thisSelect = { checked : true };
        let thisDeselect = { checked : false };
        row.checked = true;
        this.toggleAllRes(thisDeselect);
        this.toggleRes(thisSelect, row);
        
        setTimeout(() => {
            this.actionsList.launchEvent(action, row);
        }, 200);
    }

    listTodrag() {
        if (this.panelFolder !== undefined) {
            return this.panelFolder.getDragIds();
        } else {
            return [0];
        }
    }
}
export interface BasketList {
    resources: any[];
    count: number;
    basketLabel: string,
    basket_id: string,
    defaultAction: any;
    allResources: number[]
}

export class ResultListHttpDao {

    constructor(private http: HttpClient, private filtersListService: FiltersListService) { }

    getRepoIssues(sort: string, order: string, page: number, href: string, filters: string): Observable<BasketList> {
        this.filtersListService.updateListsPropertiesPage(page);
        let offset = page * 10;
        const requestUrl = `${href}?limit=10&offset=${offset}${filters}`;

        return this.http.get<BasketList>(requestUrl);
    }
}
