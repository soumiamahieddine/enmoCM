import { ChangeDetectorRef, Component, OnInit, ViewChild, Inject, EventEmitter, AfterViewInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { merge, Observable, of as observableOf } from 'rxjs';
import { NotificationService } from '../notification.service';
import { MatDialog, MatSidenav, MatPaginator, MatSort, MatBottomSheet, MatBottomSheetRef, MAT_BOTTOM_SHEET_DATA } from '@angular/material';

import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { startWith, switchMap, map, catchError } from 'rxjs/operators';
import { ActivatedRoute } from '@angular/router';
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
    providers: [NotificationService]
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
            'id': 'alt_identifier',
            'class': 'softColorData centerData',
            'icon': ''
        },
        {
            'id': 'subject',
            'class': 'longData',
            'icon': ''
        }
    ];

    //displayedSecondaryData: any = [];
    displayedSecondaryData: any = [
        {
            'id' : 'priority_label',
            'class' : '',
            'icon' : ''
        },
        {
            'id' : 'category_id',
            'class' : '',
            'icon' : ''
        },
        {
            'id' : 'doctype_label',
            'class' : '',
            'icon' : 'fa fa-file'
        },
        {
            'id' : 'contact_society',
            'class' : '',
            'icon' : ''
        },
        {
            'id' : 'contact_society',
            'class' : '',
            'icon' : ''
        },
        {
            'id' : 'date',
            'class' : 'rightData',
            'icon' : ''
        },
    ];

    resultListDatabase: ResultListHttpDao | null;
    data: any[] = [];
    resultsLength = 0;
    isLoadingResults = true;
    listProperties: any = {};
    currentBasketInfo: any = {};
    currentChrono: string = '';

    @ViewChild('filtersTool') filtersTool: FiltersToolComponent;

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild('tableBasketListSort') sort: MatSort;
    constructor(changeDetectorRef: ChangeDetectorRef, private route: ActivatedRoute, media: MediaMatcher, public http: HttpClient, public dialog: MatDialog, private sanitizer: DomSanitizer, private bottomSheet: MatBottomSheet, private headerService: HeaderService, public filtersListService: FiltersListService) {
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
            //this.headerService.headerMessage = data.basketLabel;
            this.filtersListService.filterMode = false;
            window['MainHeaderComponent'].setSnav(this.sidenavLeft);
            window['MainHeaderComponent'].setSnavRight(this.sidenavRight);

            this.listProperties = this.filtersListService.initListsProperties('bbain', params['groupSerialId'], params['basketId']);

            this.refreshDao();

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
                    this.resultsLength = data.count;
                    this.headerService.headerMessage = data.basketLabel;
                    return data.resources;
                }),
                catchError((err: any) => {
                    console.log(err);
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
}
export interface BasketList {
    resources: any[];
    count: number;
    basketLabel: string
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
