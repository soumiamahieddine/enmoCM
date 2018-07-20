import { ChangeDetectorRef, Component, OnInit, ViewChild, QueryList, ViewChildren } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';
import { NotificationService } from './notification.service';
import { MatDialog, MatSidenav, MatExpansionPanel } from '@angular/material';

import { AutoCompletePlugin } from '../plugins/autocomplete.plugin';
import { FormGroup } from '@angular/forms';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "../../../Views/about-us.component.html",
    styleUrls: ['../../../css/profile.component.css'],
    providers: [NotificationService]
})
export class AboutUsComponent extends AutoCompletePlugin implements OnInit {

    private _mobileQueryListener: () => void;
    mobileQuery: MediaQueryList;
    mobileMode                      : boolean   = false;
    coreUrl: string;
    lang: any = LANG;

    loading: boolean = false;

    @ViewChild('snav2') sidenav: MatSidenav;

    @ViewChildren(MatExpansionPanel) viewPanels: QueryList<MatExpansionPanel>;

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, public dialog: MatDialog) {
        super(http, ['users']);
        this.mobileMode = angularGlobals.mobileMode;
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    prepareProfile() {
        $j('#inner_content').remove();
        $j('#inner_content_contact').parent('div').remove(); 
        $j('#inner_content_contact').remove(); 
        $j('#menunav').hide();
        $j('#divList').remove();
        $j('#magicContactsTable').remove();
        $j('#manageBasketsOrderTable').remove();
        $j('#controlParamTechnicTable').remove();
        $j('#container').width("99%");
        if ($j('#content h1')[0] && $j('#content h1')[0] != $j('my-app h1')[0]) {
            $j('#content h1')[0].remove();
        }
    }

    ngOnInit(): void {
        this.prepareProfile();
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = false;

    }

}
