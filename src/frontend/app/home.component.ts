import { ChangeDetectorRef, Component, OnInit, ViewChild, QueryList, ViewChildren } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';
import { NotificationService } from './notification.service';
import { MatDialog, MatSidenav, MatExpansionPanel, MatTableDataSource } from '@angular/material';

import { AutoCompletePlugin } from '../plugins/autocomplete.plugin';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "home.component.html",
    styleUrls: ['profile.component.css'],
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
        this.loading = true;
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

        this.http.get(this.coreUrl + "rest/home")
        .subscribe((data: any) => {
            this.homeData = data;
        });
    }

    ngAfterViewInit(): void {
        this.http.get(this.coreUrl + "rest/home/lastRessources")
        .subscribe((data: any) => {
            setTimeout(() => {
                this.dataSource = new MatTableDataSource(data.lastResources);
                this.loading = false;
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
            this.sidenavRight.open();
        }
    }

    viewThumbnail(row:any) {
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