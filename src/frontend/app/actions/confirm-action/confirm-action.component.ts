import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { ActionsService } from '../actions.service';
import { tap, exhaustMap, catchError, finalize } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
    templateUrl: "confirm-action.component.html",
    styleUrls: ['confirm-action.component.scss'],
    providers: [NotificationService, ActionsService],
})
export class ConfirmActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    @ViewChild('noteEditor', { static: true }) noteEditor: NoteEditorComponent;
    
    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        public dialogRef: MatDialogRef<ConfirmActionComponent>,
        private actionService: ActionsService,
        @Inject(MAT_DIALOG_DATA) public data: any
    ) { }

    ngOnInit(): void {
        console.log(this.data);
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
        this.actionService.saveDocument(this.data.resource).pipe(
            tap((data: any) => {
                this.actionService.setResourceIds([data.resId]);
                this.data.resIds = this.actionService.currentResourceInformations.resId;
            }),
            exhaustMap(() => this.http.put('../../rest/indexing/groups/' + this.data.groupId + '/actions/' + this.data.action.id, {resources : this.data.resIds, note : this.noteEditor.getNoteContent()})),
            tap(() => {
                this.dialogRef.close('success');
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                this.actionService.setLoading(false);
                return of(false);
            })
        ).subscribe()
    }

    executeAction() {
        this.http.put('../../rest/resourcesList/users/' + this.data.userId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/actions/' + this.data.action.id, {resources : this.data.resIds, note : this.noteEditor.getNoteContent()}).pipe(
            tap(() => {
                this.dialogRef.close('success');
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                this.actionService.setLoading(false);
                return of(false);
            })
        ).subscribe();
    }
    
}
