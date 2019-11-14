import { Component, OnInit, Output, Input, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { tap, finalize, catchError, filter, exhaustMap } from 'rxjs/operators';
import { of } from 'rxjs';
import { AttachmentShowModalComponent } from './attachment-show-modal/attachment-show-modal.component';
import { MatDialog, MatDialogRef } from '@angular/material';
import { trigger, transition, style, animate } from '@angular/animations';
import { AttachmentPageComponent } from './attachments-page/attachment-page.component';
import { AttachmentCreateComponent } from './attachment-create/attachment-create.component';
import { ConfirmActionComponent } from '../actions/confirm-action/confirm-action.component';
import { ConfirmComponent } from '../../plugins/modal/confirm.component';

@Component({
    selector: 'app-attachments-list',
    templateUrl: 'attachments-list.component.html',
    styleUrls: ['attachments-list.component.scss'],
    providers: [NotificationService],
    animations: [
        trigger(
            'myAnimation',
            [
                transition(
                    ':enter', [
                    style({ transform: 'translateY(-10%)', opacity: 0 }),
                    animate('150ms', style({ transform: 'translateY(0)', 'opacity': 1 }))
                ]
                ),
                transition(
                    ':leave', [
                    style({ transform: 'translateY(0)', 'opacity': 1 }),
                    animate('150ms', style({ transform: 'translateY(-10%)', 'opacity': 0 })),
                ]
                )]
        )
    ],
})
export class AttachmentsListComponent implements OnInit {

    lang: any = LANG;
    attachments: any;
    loading: boolean = true;
    resIds: number[] = [];
    pos = 0;
    mailevaEnabled: boolean = false;

    hideMainInfo: boolean = false;

    dialogRef: MatDialogRef<any>;

    @Input('injectDatas') injectDatas: any;
    @Input('resId') resId: number = null;
    @Output('reloadBadgeAttachments') reloadBadgeNotes = new EventEmitter<string>();

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public dialog: MatDialog) { }

    ngOnInit(): void {
        if (this.resId !== null) {
            this.http.get(`../../rest/resources/${this.resId}/attachments`).pipe(
                tap((data: any) => {
                    this.mailevaEnabled = data.mailevaEnabled;
                    this.attachments = data.attachments;
                    this.attachments.forEach((element: any) => {
                        element.thumbnailUrl = '../../rest/attachments/' + element.resId + '/thumbnail';
                    });
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    loadAttachments(resId: number) {
        this.resIds[0] = resId;
        this.loading = true;
        this.http.get("../../rest/resources/" + this.resIds[0] + "/attachments")
            .subscribe((data: any) => {
                this.mailevaEnabled = data.mailevaEnabled;
                this.attachments = data.attachments;
                this.attachments.forEach((element: any) => {
                    element.thumbnailUrl = '../../rest/attachments/' + element.resId + '/thumbnail';
                });
                this.reloadBadgeNotes.emit(`${this.attachments.length}`);
                this.loading = false;
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }

    setInSignatureBook(attachment: any) {
        this.http.put("../../rest/attachments/" + attachment.resId + "/inSignatureBook", {})
            .subscribe(() => {
                attachment.in_signature_book = !attachment.in_signature_book;
                this.notify.success(this.lang.actionDone);
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }

    setInSendAttachment(attachment: any) {
        this.http.put("../../rest/attachments/" + attachment.resId + "/inSendAttachment", {})
            .subscribe(() => {
                attachment.in_send_attach = !attachment.in_send_attach;
                this.notify.success(this.lang.actionDone);
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }

    toggleInfo(attachment: any, state: boolean) {
        this.attachments.forEach((element: any) => {
            element.hideMainInfo = false;
        });
        attachment.hideMainInfo = state;
    }

    resetToggleInfo() {
        this.attachments.forEach((element: any) => {
            element.hideMainInfo = false;
        });
    }

    showAttachment(attachment: any) {
        this.dialog.open(AttachmentShowModalComponent, { data: { attachment: attachment } });
    }

    createAttachment() {
        this.dialogRef = this.dialog.open(AttachmentCreateComponent, { disableClose: true, panelClass: 'modal-container', height: '90vh', width: '90vw', data: { resIdMaster: this.resId } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'success'),
            tap(() => {
                this.loadAttachments(this.resId);
                this.notify.success('success');
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    deleteAttachment(attachment: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete, msg: this.lang.confirmAction } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.delete(`../../rest/attachments/${attachment.resId}`)),
            tap(() => {
                this.loadAttachments(this.resId);
                this.notify.success(this.lang.attachmentDeleted);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
