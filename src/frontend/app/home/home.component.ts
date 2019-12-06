import { Component, OnInit, ViewChild, QueryList, ViewChildren } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { MatDialog } from '@angular/material/dialog';
import { MatExpansionPanel } from '@angular/material/expansion';
import { MatSidenav } from '@angular/material/sidenav';
import { MatTableDataSource } from '@angular/material/table';
import { NotificationService } from '../notification.service';
import { HeaderService }        from '../../service/header.service';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { AppService } from '../../service/app.service';

declare function $j(selector: any): any;

@Component({
    templateUrl: "home.component.html",
    styleUrls: ['home.component.scss'],
    providers: [NotificationService, AppService]
})
export class HomeComponent implements OnInit {

    lang                : any       = LANG;
    loading             : boolean   = false;

    thumbnailUrl        : string;
    docUrl              : string    = '';
    homeData            : any;
    homeMessage         : string;
    dataSource          : any;
    currentDate         : string    = "";
    nbMpDocs            : number    = 0;


    public innerHtml    : SafeHtml;
    displayedColumns    : string[] = ['res_id', 'subject', 'creation_date'];

    @ViewChild('snav', { static: true }) snav: MatSidenav;
    @ViewChild('snav2', { static: true }) sidenavRight: MatSidenav;
    @ViewChildren(MatExpansionPanel) viewPanels: QueryList<MatExpansionPanel>;

    constructor(
        public http: HttpClient, 
        public dialog: MatDialog, 
        private sanitizer: DomSanitizer, 
        private notify: NotificationService, 
        private headerService: HeaderService,
        public appService: AppService
        ) {
        $j("link[href='merged_css.php']").remove();
        (<any>window).pdfWorkerSrc = '../../node_modules/pdfjs-dist/build/pdf.worker.min.js';
    }

    ngOnInit(): void {
        this.loading = true;
        if (this.appService.getViewMode()) {
            this.displayedColumns = ['res_id', 'subject'];
        }
        this.headerService.setHeader(this.lang.home);
        window['MainHeaderComponent'].setSnav(this.snav);
        window['MainHeaderComponent'].setSnavRight(null);

        let event = new Date();
        let options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

        this.currentDate = event.toLocaleDateString(this.lang.langISO, options);

        this.http.get("../../rest/home")
            .subscribe((data: any) => {
                this.homeData = data;
                this.homeMessage = data['homeMessage'];
        });
    }

    ngAfterViewInit(): void {
        this.http.get("../../rest/home/lastRessources")
        .subscribe((data: any) => {
            setTimeout(() => {
                this.dataSource = new MatTableDataSource(data.lastResources);
                this.loading = false;
            }, 0);
        });
    }

    goTo(row:any) {
        if (this.docUrl == '../../rest/resources/' + row.res_id + '/content' && this.sidenavRight.opened) {
            this.sidenavRight.close();
        } else {
            this.docUrl = '../../rest/resources/' + row.res_id + '/content';

            this.innerHtml = this.sanitizer.bypassSecurityTrustHtml(
                "<iframe style='height:100%;width:100%;' src='" + this.docUrl + "' class='embed-responsive-item'>" +
                "</iframe>");  
            this.sidenavRight.open();
        }
    }

    viewThumbnail(row:any) {
        this.thumbnailUrl = '../../rest/resources/' + row.res_id + '/thumbnail';
        $j('#viewThumbnail').show();
        $j('#listContent').css({"overflow":"hidden"});
    }

    closeThumbnail() {
        $j('#viewThumbnail').hide();
        $j('#listContent').css({"overflow":"auto"});
    }

    goToDetail(row:any) {
        this.http.get("../../rest/resources/" + row.res_id + "/isAllowed")
            .subscribe((data: any) => {
                if (data['isAllowed']) {
                    location.href = "index.php?page=details&dir=indexing_searching&id=" + row.res_id;
                } else {
                    this.notify.error(this.lang.documentOutOfPerimeter);
                }
            }, () => {
                this.notify.error(this.lang.errorOccured);
            });
    }

    updateNbMpDocs(ev: any) {
        this.nbMpDocs = ev;
    }
}
