import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { XParaphComponent } from './x-paraph/x-paraph.component';
import { MaarchParaphComponent } from './maarch-paraph/maarch-paraph.component';
import { tap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
    templateUrl: "send-external-signatory-book-action.component.html",
    styleUrls: ['send-external-signatory-book-action.component.scss'],
    providers: [NotificationService],
})
export class SendExternalSignatoryBookActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;
    additionalsInfos: any = {
        destinationId: '',
        users: [],
        attachments: [],
        noAttachment: []
    };
    signatoryBookEnabled: string = '';

    externalSignatoryBookDatas: any = {
        steps: [],
        objectSent: 'attachment'
    };
    errors: any;

    @ViewChild('noteEditor', { static: true }) noteEditor: NoteEditorComponent;
    
    @ViewChild('xParaph', { static: false }) xParaph: XParaphComponent;
    @ViewChild('maarchParapheur', { static: false }) maarchParapheur: MaarchParaphComponent;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<SendExternalSignatoryBookActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        this.loading = true;

        this.http.post('../../rest/resourcesList/users/' + this.data.userId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/checkExternalSignatoryBook', { resources: this.data.resIds })
            .subscribe((data: any) => {
                this.additionalsInfos = data.additionalsInfos;
                if (this.additionalsInfos.attachments.length > 0) {
                    this.signatoryBookEnabled = data.signatureBookEnabled;
                }  
                this.errors = data.errors;
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
                this.loading = false;
            });
    }

    onSubmit() {
        this.loading = true;
        if ( this.data.resIds.length === 0) {
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
        let realResSelected: string[];
        let datas: any;

        realResSelected = this[this.signatoryBookEnabled].getRessources();
        datas = this[this.signatoryBookEnabled].getDatas();
        
        this.http.put(this.data.processActionRoute, {resources : realResSelected, note : this.noteEditor.getNoteContent(), data: datas}).pipe(
            tap((data: any) => {
                if (!data) {
                    this.dialogRef.close('success');
                }
                if (data && data.errors != null) {
                    this.notify.error(data.errors);
                }
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    checkValidAction() {
        if (this[this.signatoryBookEnabled] !== undefined) {   
            return this[this.signatoryBookEnabled].checkValidParaph();
        } else {
            return true;
        }
    }
}
