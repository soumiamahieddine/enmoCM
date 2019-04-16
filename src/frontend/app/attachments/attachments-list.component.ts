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

    lang        : any       = LANG;
    attachments : any;
    loading     : boolean   = true;
    resIds      : number[]  = [];
    pos         = 0;

    @Input('injectDatas') injectDatas: any;
    @Output('reloadBadgeAttachments') reloadBadgeNotes = new EventEmitter<string>();

    constructor(public http: HttpClient, private notify: NotificationService) { }

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
                this.reloadBadgeNotes.emit(`${this.attachments.length}`);
                this.loading = false;
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }

    setInSignatureBook(attachment: any) {
        const is_version = attachment.res_id_version > 0;
        this.http.put("../../rest/attachments/" + attachment.res_id + "/inSignatureBook", { isVersion: is_version })
            .subscribe(() => {
                attachment.in_signature_book = !attachment.in_signature_book;
                this.notify.success(this.lang.actionDone);
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }

    setInSendAttachment(attachment: any) {
        const is_version = attachment.res_id_version > 0;
        this.http.put("../../rest/attachments/" + attachment.res_id + "/inSendAttachment", { isVersion: is_version })
            .subscribe(() => {
                attachment.in_send_attach = !attachment.in_send_attach;
                this.notify.success(this.lang.actionDone);
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }
}