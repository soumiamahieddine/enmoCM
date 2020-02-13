import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { tap, exhaustMap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
    templateUrl: "close-mail-action.component.html",
    styleUrls: ['close-mail-action.component.scss'],
    providers: [NotificationService],
})
export class CloseMailActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    @ViewChild('noteEditor', { static: false }) noteEditor: NoteEditorComponent;
    mailsWithEmptyFields: Array<any> = [];
    canCloseResIds: Array<any> = [];

    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        public dialogRef: MatDialogRef<CloseMailActionComponent>,
        @Inject(MAT_DIALOG_DATA) public data: any
    ) { }

    ngOnInit(): void { 
        if (this.data.resIds.length > 0) {
            this.loading = true;
            this.checkClose();
        }
    }

    checkClose() {
        this.http.post(`../../rest/resourcesList/users/${this.data.userId}/groups/${this.data.groupId}/baskets/${this.data.basketId}/actions/${this.data.action.id}/checkCloseMails`, { resources: this.data.resIds }).pipe(
            tap((data: any) => {
                this.mailsWithEmptyFields = data.emptyFields;
                this.canCloseResIds = data.canClose;
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe()
    }

    onSubmit() {
        this.loading = true;
        if ( this.data.resIds.length === 0) {
            this.indexDocumentAndExecuteAction();
        } else {
            this.executeAction();
        }
    }

    indexDocumentAndExecuteAction() {
        
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
    }

    executeAction() {
        this.http.put(this.data.processActionRoute, {resources : this.canCloseResIds, note : this.noteEditor.getNoteContent()}).pipe(
            tap(() => {
                this.dialogRef.close('success');
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
