import { ChangeDetectorRef, Component, OnInit, ViewChild, EventEmitter } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { merge, Observable, of as observableOf } from 'rxjs';
import { NotificationService } from '../notification.service';
import { MatDialog, MatSidenav, MatPaginator, MatSort, MatBottomSheet } from '@angular/material';

import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { startWith, switchMap, map, catchError } from 'rxjs/operators';
import { ActivatedRoute, Router } from '@angular/router';
import { HeaderService } from '../../service/header.service';
import { FiltersListService } from '../../service/filtersList.service';
import { NotesListComponent } from '../notes/notes.component';
import { AttachmentsListComponent } from '../attachments/attachments-list.component';
import { DiffusionsListComponent } from '../diffusions/diffusions-list.component';
import { FiltersToolComponent } from './filters/filters-tool.component';


declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "basket-list.component.html",
    styleUrls: ['basket-list.component.scss'],
    providers: [NotificationService],
})
export class BasketListComponent implements OnInit {

    private _mobileQueryListener: () => void;
    mobileQuery: MediaQueryList;
    mobileMode: boolean = false;
    coreUrl: string;
    lang: any = LANG;

    loading: boolean = false;
    docUrl: string = '';
    public innerHtml: SafeHtml;
    basketUrl: string;
    homeData: any;

    filtersChange = new EventEmitter();

    @ViewChild('snav') sidenavLeft: MatSidenav;
    @ViewChild('snav2') sidenavRight: MatSidenav;

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
    thumbnailUrl: string = '';

    selectedRes: number[] = [];
    allResInBasket: number[] = [];

    @ViewChild('filtersTool') filtersTool: FiltersToolComponent;

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild('tableBasketListSort') sort: MatSort;
    constructor(changeDetectorRef: ChangeDetectorRef, private router: Router, private route: ActivatedRoute, media: MediaMatcher, public http: HttpClient, public dialog: MatDialog, private sanitizer: DomSanitizer, private bottomSheet: MatBottomSheet, private headerService: HeaderService, public filtersListService: FiltersListService, private notify: NotificationService) {
        this.mobileMode = angularGlobals.mobileMode;
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = false;

        if (this.mobileMode) {
            $j('.mat-paginator-navigation-previous').hide();
            $j('.mat-paginator-navigation-next').hide();
        }

        this.http.get(this.coreUrl + "rest/home")
            .subscribe((data: any) => {
                this.homeData = data;
            });

        this.isLoadingResults = false;

        this.initResultList();

        this.route.params.subscribe(params => {
            this.basketUrl = '../../rest/resourcesList/users/' + params['userSerialId'] + '/groups/' + params['groupSerialId'] + '/baskets/' + params['basketId'];

            this.currentBasketInfo = {
                ownerId: params['userSerialId'],
                groupId: params['groupSerialId'],
                basketId: params['basketId']
            };
            this.filtersListService.filterMode = false;
            this.selectedRes = [];
            window['MainHeaderComponent'].setSnav(this.sidenavLeft);
            window['MainHeaderComponent'].setSnavRight(null);

            this.listProperties = this.filtersListService.initListsProperties(this.currentBasketInfo.ownerId, this.currentBasketInfo.groupId, this.currentBasketInfo.basketId);

            this.refreshDao();

        },
            (err: any) => {
                this.notify.handleErrors(err);
            });
    }

    initResultList() {
        this.resultListDatabase = new ResultListHttpDao(this.http, this.filtersListService);
        // If the user changes the sort order, reset back to the first page.
        this.paginator.pageIndex = this.listProperties.page;
        this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

        // When list is refresh (sort, page, filters)
        merge(this.sort.sortChange, this.paginator.page, this.filtersChange)
            .pipe(
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
                    this.headerService.setHeader(data.basketLabel, this.resultsLength + ' ' + this.lang.entries);
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
        if (this.docUrl == this.coreUrl + 'rest/res/' + row.res_id + '/content' && this.sidenavRight.opened) {
            this.sidenavRight.close();
        } else {
            this.docUrl = this.coreUrl + 'rest/res/' + row.res_id + '/content';
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

    openBottomSheet(row: any): void {
        this.bottomSheet.open(NotesListComponent, {
            data: { resId: row.res_id, chrono: row.alt_identifier },
            panelClass: 'note-width-bottom-sheet'
        });
    }

    openAttachSheet(row: any): void {
        this.bottomSheet.open(AttachmentsListComponent, {
            data: { resId: row.res_id, chrono: row.alt_identifier },
        });
    }

    openDiffusionSheet(row: any): void {
        this.bottomSheet.open(DiffusionsListComponent, {
            data: { resId: row.res_id, chrono: row.alt_identifier },
        });
    }

    refreshDao() {
        this.paginator.pageIndex = this.listProperties.page;
        this.filtersChange.emit();
    }

    filterThis(value: string) {
        this.filtersTool.setInputSearch(value);
    }

    viewThumbnail(row: any) {
        this.thumbnailUrl = this.coreUrl + 'rest/res/' + row.res_id + '/thumbnail';
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
                } else if ((element[key] == null || element[key] == '') && ['closingDate', 'countAttachments', 'countNotes', 'display'].indexOf(key) === -1) {
                    element[key] = this.lang.undefined;
                }
            });

            // Process secondary datas
            element.display.forEach((key: any) => {
                if ((key.displayValue == null || key.displayValue == '') && ['getCreationAndProcessLimitDates', 'getParallelOpinionsNumber'].indexOf(key.value) === -1) {
                    key.displayValue = this.lang.undefined;

                } else if (["getSenders", "getRecipients"].indexOf(key.value) > -1) {
                    
                    if (key.displayValue.length > 1) {
                        key.displayValue = this.lang.isMulticontact;
                    } else {
                        key.displayValue = key.displayValue[0];
                    }
                } else if(key.value == 'getCreationAndProcessLimitDates') {
                    key.icon = '';
                } else if(key.value == 'getVisaWorkflow') {
                    let formatWorkflow: any = [];
                    let content = '';
                    let user = '';
                    key.displayValue.forEach((visa: any) => {
                        content = '';
                        user = visa.user;
                        if (visa.mode == 'sign') {
                            user = '<u>'+user+'</u>';
                        } 
                        if (visa.date == '') {
                            content = '<i class="fa fa-hourglass-half"></i> <span title="' + this.lang[visa.mode+'User'] + '">' + user + '</span>';
                        } else {
                            content = '<span color="accent" style=""><i class="fa fa-check"></i> <span title="' + this.lang[visa.mode+'User'] + '">' + user + '</span></span>';
                        }

                        if (visa.current) {
                            content = '<b color="primary">'+content+'</b>';                            
                        }
                        formatWorkflow.push(content);
                    });
                    key.icon = '';
                    key.displayValue = formatWorkflow.join(' <i class="fas fa-long-arrow-alt-right"></i> ');
                } else if(key.value == 'getParallelOpinionsNumber') {
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

    toggleRes(e: any, resId: any) {
        if (e.checked) {
            if (this.selectedRes.indexOf(resId) === -1) {
                this.selectedRes.push(resId);
            }
        } else {
            let index = this.selectedRes.indexOf(resId);
            this.selectedRes.splice(index, 1);
        }
    }

    toggleAllRes(e: any) {
        this.selectedRes = [];
        if (e.checked) {
            this.data.forEach((element: any) => {
                element['checked'] = true;
            });
            this.selectedRes = this.allResInBasket;
        } else {
            this.data.forEach((element: any) => {
                element['checked'] = false;
            });
        }
    }
}
export interface BasketList {
    resources: any[];
    count: number;
    basketLabel: string,
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
