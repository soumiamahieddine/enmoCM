import { Component, OnInit, Input, EventEmitter, Output, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { catchError, tap, finalize } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../../notification.service';
import { MatDialog, MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { AppService } from '../../../service/app.service';

interface attachment {
    resId: 0,
    resIdMaster : 0,
    chrono : null,
    title : '',
    creationDate : null,
    modificationDate: null,
    relation : 1,
    status : null,
    type : null,
    originId : null,
    inSignatureBook : false,
    inSendAttach : false,
    typeLabel : ''
}

@Component({
    selector: 'app-attachment-page',
    templateUrl: "attachment-page.component.html",
    styleUrls: [
        'attachment-page.component.scss',
    ],
    providers: [AppService],
})

export class AttachmentPageComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = true;

    attachment: attachment;

    creationMode: boolean = true;

    @Input('resId') resId: number = null;

    constructor(
        public http: HttpClient,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<AttachmentPageComponent>,
        public appService: AppService,
        private notify: NotificationService) {
    }

    ngOnInit(): void {
        if (this.data === null) {
            this.creationMode = true;
            this.attachment = {
                resId: null,
                resIdMaster : null,
                chrono : null,
                title : '',
                creationDate : null,
                modificationDate: null,
                relation : 1,
                status : null,
                type : null,
                originId : null,
                inSignatureBook : false,
                inSendAttach : false,
                typeLabel : ''
            };
        } else {
            this.creationMode = false;
            this.attachment = this.data.attachment;
        }
    }
}