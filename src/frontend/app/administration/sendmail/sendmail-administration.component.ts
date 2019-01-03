import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { MatSidenav } from '@angular/material';
import { NotificationService } from '../../notification.service';
import { HeaderService }        from '../../../service/header.service';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "sendmail-administration.component.html",
    providers: [NotificationService]
})
export class SendmailAdministrationComponent implements OnInit {

    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;
    
    mobileQuery                     : MediaQueryList;
    private _mobileQueryListener    : () => void;

    coreUrl     : string;
    lang        : any = LANG;
    loading     : boolean = false;

    sendmail: any = {
        'type': 'internalParam',
        'host': '',
        'auth': true,
        'user': '',
        'password': '',
        'secure': 'ssl', //tls, ssl, starttls
        'port': '465',
        'charset': 'utf-8',
        'from': '',
    };

    smtpTypeList = [
        {
            id : 'internalParam',
            label : this.lang.internalParam
        }
        ,{
            id : 'smtpRelay',
            label : this.lang.smtpRelay
        }
    ];
    smtpTypeDesc = '';
    smtpSecList = ['ssl', 'tls'];
    sendmailClone: any = {};
    hidePassword: boolean = true;


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, private headerService: HeaderService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.sendmail);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;

        this.http.get(this.coreUrl + 'rest/configurations/admin_email_server')
            .subscribe((data: any) => {
                this.sendmail = data.configuration.value
                this.sendmailClone = JSON.parse(JSON.stringify(this.sendmail));
                this.smtpTypeDesc = this.lang[this.sendmail.type+'Desc'];
                this.loading = false;
            }, (err) => {
                this.notify.handleErrors(err);
            });
    }

    cancelModification() {
        this.sendmail = JSON.parse(JSON.stringify(this.sendmailClone));
    }

    changeDesc(e: any) {
        this.smtpTypeDesc = this.lang[e.selected.value+'Desc'];
    }

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/configurations/admin_email_server', this.sendmail)
            .subscribe((data: any) => {
                this.sendmailClone = JSON.parse(JSON.stringify(this.sendmail));
                this.notify.success(this.lang.configurationUpdated);
            }, (err) => {
                this.notify.handleErrors(err);
            });
    }

    checkModif() {
        return (JSON.stringify(this.sendmailClone) === JSON.stringify(this.sendmail));
    }
}
