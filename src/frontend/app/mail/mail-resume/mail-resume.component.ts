import { Component, OnInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { catchError, tap, finalize } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../../notification.service';


@Component({
    selector: 'app-mail-resume',
    templateUrl: "mail-resume.component.html",
    styleUrls: [
        'mail-resume.component.scss',
    ]
})

export class MailResumeComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = true;

    mails: any[] = [];

    @Input('resId') resId: number = null;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
    ) {
    }

    ngOnInit(): void {
        this.loading = true;
        this.loadMails(this.resId);
    }

    loadMails(resId: number) {
        this.http.get(`../../rest/externalSummary/${resId}?limit=2`).pipe(
            tap((data: any) => {
                this.mails = data.elementsSend;
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}