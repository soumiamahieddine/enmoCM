import { Component, AfterViewInit, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MAT_BOTTOM_SHEET_DATA } from '@angular/material';

declare function $j(selector: any): any;

@Component({
    selector: 'app-attachments-list',
    templateUrl: 'attachments-list.component.html',
    styleUrls: ['attachments-list.component.scss'],
    providers: [NotificationService]
})
export class AttachmentsListComponent implements AfterViewInit {

    lang: any = LANG;
    attachments: any;
    attachmentTypes: any;
    loading: boolean = true;

    constructor(public http: HttpClient, @Inject(MAT_BOTTOM_SHEET_DATA) public data: any) { }

    ngAfterViewInit() {
        this.http.get("../../rest/res/" + this.data.resId + "/attachments")
            .subscribe((data: any) => {
                this.attachments = data.attachments;
                this.attachmentTypes = data.attachment_types;
                this.loading = false;
            });
    }
}