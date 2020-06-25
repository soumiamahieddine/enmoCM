import { Component, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ValidatorFn } from '@angular/forms';
import { LANG } from '../../translate.component';
import { StepAction } from '../types';
import { DomSanitizer } from '@angular/platform-browser';
import { NotificationService } from '../../../service/notification/notification.service';
import { environment } from '../../../environments/environment';
import { ScanPipe } from 'ngx-pipes';
import { debounceTime, filter, tap, catchError } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';
import { of } from 'rxjs/internal/observable/of';
import { MatDrawer } from '@angular/material/sidenav';

declare var tinymce: any;

@Component({
    selector: 'app-mailserver',
    templateUrl: './mailserver.component.html',
    styleUrls: ['./mailserver.component.scss'],
})
export class MailserverComponent implements OnInit {
    lang: any = LANG;

    hidePassword: boolean = true;
    stepFormGroup: FormGroup;

    testRecipient: string = '';
    emailSendLoading: boolean = false;
    emailSendResult: any = {
        msg: '',
        debug: ''
    };

    smtpTypeList = [
        {
            id: 'smtp',
            label: this.lang.smtpclient
        },
        {
            id: 'sendmail',
            label: this.lang.smtprelay
        },
        {
            id: 'qmail',
            label: this.lang.qmail
        },
        {
            id: 'mail',
            label: this.lang.phpmail
        }
    ];
    smtpSecList = [
        {
            id: '',
            label: this.lang.none
        },
        {
            id: 'ssl',
            label: 'ssl'
        },
        {
            id: 'tls',
            label: 'tls'
        }
    ];

    @ViewChild('checkMailserverContent', { static: true }) checkMailserverContent: MatDrawer;

    constructor(
        private _formBuilder: FormBuilder,
        private notify: NotificationService,
        public http: HttpClient,
    ) {
        const valEmail: ValidatorFn[] = [Validators.pattern(/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/), Validators.required];

        this.stepFormGroup = this._formBuilder.group({
            firstCtrl: ['success', Validators.required],
            type: ['smtp', Validators.required],
            host: ['', Validators.required],
            auth: [true],
            user: ['', Validators.required],
            password: ['', Validators.required],
            secure: ['ssl', Validators.required],
            port: ['465', Validators.required],
            charset: ['utf-8', Validators.required],
            from: ['', valEmail],
        });
    }

    ngOnInit(): void {
        this.stepFormGroup.valueChanges.subscribe(() => {
            if (this.checkMailserverContent.opened) {
                this.checkMailserverContent.close();
                this.emailSendLoading = false;
                this.emailSendResult = {
                    icon: 'fa-paper-plane primary',
                    msg: this.lang.emailSendInProgress,
                    debug: ''
                };
            }
        });
        this.stepFormGroup.controls['type'].valueChanges.pipe(
            tap((data: any) => {
                if (['smtp', 'mail'].indexOf(data) === -1) {
                    this.stepFormGroup.controls['secure'].disable();
                    this.stepFormGroup.controls['host'].disable();
                    this.stepFormGroup.controls['port'].disable();
                    this.stepFormGroup.controls['auth'].disable();
                    this.stepFormGroup.controls['user'].disable();
                    this.stepFormGroup.controls['password'].disable();
                } else {
                    this.stepFormGroup.controls['secure'].enable();
                    this.stepFormGroup.controls['host'].enable();
                    this.stepFormGroup.controls['port'].enable();
                    this.stepFormGroup.controls['auth'].enable();
                    if (this.stepFormGroup.controls['auth'].value) {
                        this.stepFormGroup.controls['user'].enable();
                        this.stepFormGroup.controls['password'].enable();
                    }
                }
            })
        ).subscribe();
        this.stepFormGroup.controls['auth'].valueChanges.pipe(
            tap((data: any) => {
                if (!data) {
                    this.stepFormGroup.controls['user'].disable();
                    this.stepFormGroup.controls['password'].disable();
                } else {
                    this.stepFormGroup.controls['user'].enable();
                    this.stepFormGroup.controls['password'].enable();
                }
            })
        ).subscribe();
    }

    testEmailSend() {
        this.emailSendResult = {
            icon: 'fa-paper-plane primary',
            msg: this.lang.emailSendInProgress,
            debug: ''
        };
        const email = {
            'sender': { 'email': this.stepFormGroup.controls['from'] },
            'recipients': [this.testRecipient],
            'object': '[' + this.lang.doNotReply + '] ' + this.lang.emailSendTest,
            'status': 'EXPRESS',
            'body': this.lang.emailSendTest,
            'isHtml': false
        };
        this.emailSendLoading = true;

        this.http.get(`../rest/emails`).pipe(
            tap((data: any) => {
                this.emailSendLoading = false;
                this.emailSendResult = {
                    icon: 'fa-check green',
                    msg: this.lang.emailSendSuccess,
                    debug: ''
                };
            }),
            catchError((err: any) => {
                console.log(err);

                this.emailSendLoading = false;
                this.emailSendResult = {
                    icon: 'fa-times red',
                    msg: this.lang.emailSendFailed,
                    debug: err.error.errors
                };
                return of(false);
            })
        ).subscribe();
    }

    initStep() {
        return false;
    }

    isValidStep() {
        return this.stepFormGroup === undefined ? false : this.stepFormGroup.valid;
    }

    getFormGroup() {
        return this.stepFormGroup;
    }

    checkStep() {
        return this.stepFormGroup.valid;
    }

    getInfoToInstall(): StepAction[] {
        return [];
    }
}
