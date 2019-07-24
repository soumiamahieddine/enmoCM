import { Component, OnInit, ViewChild } from '@angular/core';
import { LANG } from './translate.component';
import { NotificationService } from './notification.service';
import { HeaderService }        from '../service/header.service';
import { MatSidenav } from '@angular/material';
import { AppService } from '../service/app.service';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "about-us.component.html",
    styleUrls: ['profile.component.css'],
    providers: [NotificationService, AppService]
})
export class AboutUsComponent implements OnInit {

    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    applicationVersion              : string;
    lang: any = LANG;

    loading: boolean = false;


    constructor(
        private headerService: HeaderService,
        public appService: AppService) {  
            $j("link[href='merged_css.php']").remove();
    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.aboutUs);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.applicationVersion = angularGlobals.applicationVersion;
        this.loading = false;
    }
}
