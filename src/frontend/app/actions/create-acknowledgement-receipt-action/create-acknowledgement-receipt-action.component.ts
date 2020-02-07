import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { tap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
    templateUrl: "create-acknowledgement-receipt-action.component.html",
    styleUrls: ['create-acknowledgement-receipt-action.component.scss'],
    providers: [NotificationService],
})
export class CreateAcknowledgementReceiptActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;
    loadingInit: boolean = false;
    acknowledgement: any = {
        alReadyGenerated: {},
        alReadySend: {},
        noSendAR: {},
        sendEmail: 0,
        sendPaper: 0,
        sendList: []
    };

    @ViewChild('noteEditor', { static: false }) noteEditor: NoteEditorComponent;
    loadingExport: boolean;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<CreateAcknowledgementReceiptActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        this.loadingInit = true;

        this.http.post('../../rest/resourcesList/users/' + this.data.userId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/checkAcknowledgementReceipt', { resources: this.data.resIds })
            .subscribe((data: any) => {
                this.acknowledgement = data;
                this.loadingInit = false;
            }, (err) => {
                this.notify.error(err.error.errors);
                this.loadingInit = false;
            });
    }

    onSubmit() {
        this.loading = true;
        if (this.data.resIds.length === 0) {
            // this.indexDocumentAndExecuteAction();
        } else {
            this.executeAction();
        }
    }

    /* indexDocumentAndExecuteAction() {
            
            this.http.post('../../rest/resources', this.data.resource).pipe(
                tap((data: any) => {
                    this.data.resIds = [data.resId];
                }),
                exhaustMap(() => this.http.put(this.data.indexActionRoute, {resource : this.data.resIds[0], note : this.noteEditor.getNoteContent()})),
                tap(() => {
                    this.dialogRef.close('success');
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe()
        } */

    executeAction() {
        this.http.put(this.data.processActionRoute, { resources: this.data.resIds, note: this.noteEditor.getNoteContent() }).pipe(
            tap((data: any) => {
                if (data && data.data != null) {
                    this.downloadAcknowledgementReceipt(data.data);
                }
                if (data && data.errors != null) {
                    this.notify.error(data.errors);
                }
                this.dialogRef.close('success');
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    downloadAcknowledgementReceipt(data: any) {
        this.loadingExport = true;
        this.http.post('../../rest/acknowledgementReceipts', { 'resources': data }, { responseType: "blob" })
            .subscribe((data) => {
                let downloadLink = document.createElement('a');
                downloadLink.href = window.URL.createObjectURL(data);
                let today: any;
                let dd: any;
                let mm: any;
                let yyyy: any;

                today = new Date();
                dd = today.getDate();
                mm = today.getMonth() + 1;
                yyyy = today.getFullYear();

                if (dd < 10) {
                    dd = '0' + dd;
                }
                if (mm < 10) {
                    mm = '0' + mm;
                }
                today = dd + '-' + mm + '-' + yyyy;
                downloadLink.setAttribute('download', "acknowledgement_receipt_maarch_" + today + ".pdf");
                document.body.appendChild(downloadLink);
                downloadLink.click();
                this.loadingExport = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });
    }

}
