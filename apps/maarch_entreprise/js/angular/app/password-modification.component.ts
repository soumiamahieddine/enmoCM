import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';
import { NotificationService } from './notification.service';
import { MatDialog } from '@angular/material';

declare function $j(selector: any): any;

declare var tinymce: any;
declare var angularGlobals: any;


@Component({
    templateUrl: "../../../Views/password-modification.component.html",
    providers: [NotificationService]
})
export class PasswordModificationComponent implements OnInit {

    private _mobileQueryListener: () => void;
    mobileQuery                 : MediaQueryList;

    coreUrl         : string;
    lang            : any = LANG;
    loading         : boolean = false;

    passwordModel   : any = {
        currentPassword : "",
        newPassword     : "",
        reNewPassword   : "",
    };

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    prepare() {
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
        this.prepare();
        this.coreUrl = angularGlobals.coreUrl;

    }
}
