import { ChangeDetectorRef, Component, OnInit, ViewChild, QueryList, ViewChildren } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { merge, Observable, of as observableOf} from 'rxjs';
import { NotificationService } from '../notification.service';
import { MatDialog, MatSidenav, MatExpansionPanel, MatTableDataSource, MatPaginator, MatSort } from '@angular/material';

import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { startWith, switchMap, map, catchError } from 'rxjs/operators';
import { ActivatedRoute } from '@angular/router';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "../../../../Views/basket-list.component.html",
    providers: [NotificationService]
})
export class BasketListComponent implements OnInit {

    private _mobileQueryListener: () => void;
    mobileQuery: MediaQueryList;
    mobileMode: boolean   = false;
    coreUrl: string;
    lang: any = LANG;

    loading: boolean = false;
    docUrl : string = '';
    public innerHtml: SafeHtml;
    basketUrl: string;
    homeData: any;


    @ViewChild('snav') sidenavLeft: MatSidenav;
    @ViewChild('snav2') sidenavRight: MatSidenav;
    

    displayedColumnsBasket: string[] = ['res_id', 'subject', 'contact_society', 'creation_date'];

    exampleDatabase: ExampleHttpDao | null;
    data: any[] = [];
    resultsLength = 0;
    isLoadingResults = true;
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild('tableBasketListSort') sort: MatSort;
    constructor(changeDetectorRef: ChangeDetectorRef, private route: ActivatedRoute, media: MediaMatcher, public http: HttpClient, public dialog: MatDialog, private sanitizer: DomSanitizer) {
        this.mobileMode = angularGlobals.mobileMode;
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = false;

        this.http.get(this.coreUrl + "rest/home")
        .subscribe((data: any) => {
            this.homeData = data;
        });


        this.route.params.subscribe(params => {
            this.basketUrl = this.coreUrl + 'rest/resources/groups/'+params['groupSerialId']+'/baskets/'+params['basketId'];
            this.http.get(this.coreUrl + "rest/baskets/" + params['basketId'])
            .subscribe((data: any) => {
                window['MainHeaderComponent'].refreshTitle(data.basket.basket_name);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);
                this.exampleDatabase = new ExampleHttpDao(this.http);

        // If the user changes the sort order, reset back to the first page.
        this.paginator.pageIndex = 0;
        this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

        merge(this.sort.sortChange, this.paginator.page)
            .pipe(
                startWith({}),
                switchMap(() => {
                    this.isLoadingResults = true;
                    return this.exampleDatabase!.getRepoIssues(
                        this.sort.active, this.sort.direction, this.paginator.pageIndex, this.basketUrl);
                }),
                map(data => {
                    // Flip flag to show that loading has finished.
                    this.isLoadingResults = false;
                    this.resultsLength = data.number;

                    return data.resources;
                }),
                catchError(() => {
                    this.isLoadingResults = false;
                    return observableOf([]);
                })
            ).subscribe(data => this.data = data);

            }, () => {
                location.href = "index.php";
            });   
        });


    }

    goTo(row:any){
        if (this.docUrl == this.coreUrl+'rest/res/'+row.res_id+'/content' && this.sidenavRight.opened) {
            this.sidenavRight.close();
        } else {
            this.docUrl = this.coreUrl+'rest/res/'+row.res_id+'/content';
            this.innerHtml = this.sanitizer.bypassSecurityTrustHtml(
                "<object style='height:100%;width:100%;' data='" + this.docUrl + "' type='application/pdf' class='embed-responsive-item'>" +
                "<div>Le document "+row.res_id+" ne peut pas être chargé</div>" +
                "</object>");  
            this.sidenavRight.open();
        }
    }

    goToDetail(row:any){
        location.href = "index.php?page=details&dir=indexing_searching&id="+row.res_id;
    }

}
export interface BasketList {
    resources: any[];
    number: number;
}


export class ExampleHttpDao {

    constructor(private http: HttpClient) { }

    getRepoIssues(sort: string, order: string, page: number, href :string): Observable<BasketList> {
        let offset = page * 10;
        const requestUrl = `${href}?limit=10&offset=${offset}`;

        return this.http.get<BasketList>(requestUrl);
    }
}