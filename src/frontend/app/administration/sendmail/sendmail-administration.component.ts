import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { MatSidenav } from '@angular/material';
import { NotificationService } from '../../notification.service';
import { HeaderService } from '../../../service/header.service';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "sendmail-administration.component.html",
    styleUrls: ['sendmail-administration.component.scss'],
    providers: [NotificationService]
})
export class SendmailAdministrationComponent implements OnInit {

    @ViewChild('snav') public sidenavLeft: MatSidenav;
    @ViewChild('snav2') public sidenavRight: MatSidenav;

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;

    coreUrl: string;
    lang: any = LANG;
    loading: boolean = false;

    sendmail: any = {
        'type': 'smtp',
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
            id: 'smtp',
            label: this.lang.smtp
        },
        {
            id: 'sendmail',
            label: 'Sendmail'
        },
        {
            id: 'qmail',
            label: 'Qmail'
        }
    ];
    smtpTypeDesc = '';
    smtpSecList = ['ssl', 'tls'];
    sendmailClone: any = {};
    hidePassword: boolean = true;
    serverConnectionLoading: boolean = false;
    emailSendLoading: boolean = false;
    emailSendResult = {
        icon : '',
        msg : ''
    };
    currentUser: any = {};



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
                this.smtpTypeDesc = this.lang[this.sendmail.type + 'Desc'];
                this.loading = false;
            }, (err) => {
                this.notify.handleErrors(err);
            });
    }

    cancelModification() {
        this.sendmail = JSON.parse(JSON.stringify(this.sendmailClone));
    }

    changeDesc(e: any) {
        this.smtpTypeDesc = this.lang[e.selected.value + 'Desc'];
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

    initEmailSend() {
        this.emailSendResult = {
            icon : '',
            msg : ''
        };
        if (this.currentUser.mail === undefined) {
            this.http.get('../../rest/currentUser/profile')
            .subscribe((data: any) => {
                this.currentUser = data;
            });
        }  
    }

    testEmailSend() {
        this.emailSendResult = {
            icon : 'fa-paper-plane primary',
            msg : this.lang.emailSendInProgress
        };
        let email = {
            "sender": { "email": this.currentUser.mail },
            "recipients": [this.currentUser.mail],
            "object": "test mail envoi",
            "status": "SENDMAILTEST",
            "body": "test mail envoi",
            "isHtml": false
        }
        this.emailSendLoading = true;

        this.http.post('../../rest/emails', email)
            .subscribe((data: any) => {
                this.emailSendLoading = false;
                this.emailSendResult = {
                    icon : 'fa-check green',
                    msg : this.lang.emailSendSuccess
                };
            });
    }
}
