import { ChangeDetectorRef, Component, OnInit, ViewChild, QueryList, ViewChildren } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';
import { merge, Observable, of as observableOf} from 'rxjs';
import { NotificationService } from './notification.service';
import { MatDialog, MatSidenav, MatExpansionPanel, MatTableDataSource, MatPaginator, MatSort } from '@angular/material';

import { AutoCompletePlugin } from '../plugins/autocomplete.plugin';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { startWith, switchMap, map, catchError } from 'rxjs/operators';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "../../../Views/home.component.html",
    styleUrls: ['../../../css/profile.component.css'],
    providers: [NotificationService]
})
export class HomeComponent extends AutoCompletePlugin implements OnInit {

    private _mobileQueryListener: () => void;
    mobileQuery: MediaQueryList;
    mobileMode: boolean   = false;
    coreUrl: string;
    lang: any = LANG;

    loading: boolean = false;
    docUrl : string = '';
    public innerHtml: SafeHtml;

    @ViewChild('snav') snav: MatSidenav;
    @ViewChild('snav2') sidenavRight: MatSidenav;
    

    @ViewChildren(MatExpansionPanel) viewPanels: QueryList<MatExpansionPanel>;
    homeData: any;
    dataSource: any;
    displayedColumns: string[] = ['res_id', 'subject', 'creation_date'];

    currentDate : string = "";

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, public dialog: MatDialog, private sanitizer: DomSanitizer) {
        super(http, ['users']);
        this.mobileMode = angularGlobals.mobileMode;
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        if (this.mobileMode) {
            this.displayedColumns = ['res_id', 'subject'];
        }

        window['MainHeaderComponent'].refreshTitle(this.lang.home);
        window['MainHeaderComponent'].setSnav(this.snav);
        window['MainHeaderComponent'].setSnavRight(null);
        this.coreUrl = angularGlobals.coreUrl;
        let event = new Date();
        let options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

        this.currentDate = event.toLocaleDateString('fr-FR', options);
        this.loading = false;

        this.http.get(this.coreUrl + "rest/home")
        .subscribe((data: any) => {
            this.homeData = data;
            this.loading = false;
            setTimeout(() => {
                this.dataSource = new MatTableDataSource(this.homeData.lastResources);
            }, 0);
        });

    }

    goTo(row:any){
        if (this.docUrl == this.coreUrl+'rest/res/'+row.res_id+'/content' && this.sidenavRight.opened) {
            this.sidenavRight.close();
        } else {
            this.docUrl = this.coreUrl+'rest/res/'+row.res_id+'/content';
            this.innerHtml = this.sanitizer.bypassSecurityTrustHtml(
                "<iframe style='height:100%;width:100%;' src='" + this.docUrl + "' class='embed-responsive-item'>" +
                "</iframe>");  
                /*"<object style='height:100%;width:100%;' data='" + this.docUrl + "' type='application/pdf' class='embed-responsive-item'>" +
                "<div>Le document "+row.res_id+" ne peut pas être chargé</div>" +
                "</object>");*/
            this.sidenavRight.open();
        }
    }

    viewThumbnail(row:any) {
        console.log('ok');
        $j('#viewThumbnail').css({'background':'white url('+this.coreUrl+'rest/res/' + row.res_id + '/thumbnail) no-repeat 100%'});
        $j('#viewThumbnail').css({'background-size': '100%'});
        $j('#viewThumbnail').show();
    }

    closeThumbnail() {
        $j('#viewThumbnail').hide();
    }

    goToDetail(row:any){
        location.href = "index.php?page=details&dir=indexing_searching&id="+row.res_id;
    }

}