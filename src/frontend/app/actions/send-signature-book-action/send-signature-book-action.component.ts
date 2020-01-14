import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { tap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';
import { FunctionsService } from '../../../service/functions.service';
import { VisaWorkflowComponent } from '../../visa/visa-workflow.component';

@Component({
    templateUrl: "send-signature-book-action.component.html",
    styleUrls: ['send-signature-book-action.component.scss'],
})
export class SendSignatureBookActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    resourcesError: any[] = [];

    noResourceToProcess: boolean = false;

    @ViewChild('noteEditor', { static: true }) noteEditor: NoteEditorComponent;
    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;

    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        public dialogRef: MatDialogRef<SendSignatureBookActionComponent>, 
        @Inject(MAT_DIALOG_DATA) public data: any,
        public functions: FunctionsService) { }

    async ngOnInit(): Promise<void> {
        this.loading = true;
        await this.checkSignatureBook();
        this.loading = false;
    }

    checkSignatureBook() {
        this.resourcesError = [];

        return new Promise((resolve, reject) => {
            this.http.post('../../rest/resourcesList/users/' + this.data.userId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/actions/' + this.data.action.id + '/checkSignatureBook', { resources: this.data.resIds })
            .subscribe((data: any) => {                
                if (!this.functions.empty(data.resourcesInformations.noAttachment)) {
                    this.resourcesError = data.resourcesInformations.noAttachment;
                }
                this.noResourceToProcess = this.data.resIds.length === this.resourcesError.length;
                resolve(true);
            }, (err: any) => {
                this.notify.handleSoftErrors(err);
            });
        });
    }

    async onSubmit() {
        this.loading = true;
        if ( this.data.resIds.length === 0) {
            // this.indexDocumentAndExecuteAction();
        } else {
            const res = await this.appVisaWorkflow.saveVisaWorkflow();

            if (res) {
                this.executeAction();
            }
        }
        this.loading = false;
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

        realResSelected = this.data.resIds.filter((resId: any) => this.resourcesError.map(resErr => resErr.res_id).indexOf(resId) === -1);
        
        this.http.put(this.data.processActionRoute, {resources : realResSelected, note : this.noteEditor.getNoteContent()}).pipe(
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

    isValidAction() {
        if (!this.noResourceToProcess) {
            return true;
        } else {
            return false;
        }
    }
}
