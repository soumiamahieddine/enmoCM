import { Component, OnInit, Output, Input, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

@Component({
    selector: 'app-attachments-list',
    templateUrl: 'attachments-list.component.html',
    styleUrls: ['attachments-list.component.scss'],
    providers: [NotificationService]
})
export class AttachmentsListComponent implements OnInit {

    lang: any = LANG;
    attachments: any;
    attachmentTypes: any;
    loading: boolean = true;
    resIds: number[] = [];
    @Input('injectDatas') injectDatas: any;
    @Output('reloadBadgeAttachments') reloadBadgeNotes = new EventEmitter<string>();

    constructor(public http: HttpClient) { }

    ngOnInit(): void { }

    loadAttachments(resId: number) {
        this.resIds[0] = resId;
        this.loading = true;
        this.http.get("../../rest/resources/" + this.resIds[0] + "/attachments")
            .subscribe((data: any) => {
                this.attachments = data.attachments;
                this.attachments.forEach((element: any) => {
                    element.thumbnailUrl = '../../rest/res/' + this.resIds[0] + '/attachments/' + element.res_id + '/thumbnail';
                });
                this.attachmentTypes = data.attachmentTypes;
                this.reloadBadgeNotes.emit(`${this.attachments.length}`);
                this.loading = false;
            });
    }
}