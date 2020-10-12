import { HttpClient } from '@angular/common/http';
import { Component, Inject, OnInit, ViewChild } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { NoteEditorComponent } from '@appRoot/notes/note-editor.component';
import { TranslateService } from '@ngx-translate/core';
import { FunctionsService } from '@service/functions.service';
import { NotificationService } from '@service/notification/notification.service';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';

@Component({
    selector: 'app-check-reply-record-management',
    templateUrl: './check-reply-record-management.component.html',
    styleUrls: ['./check-reply-record-management.component.scss']
})
export class CheckReplyRecordManagementComponent implements OnInit {

    loading: boolean = false;
    checking: boolean = true;
    resourcesErrors: any[] = [];
    selectedRes: number[] = [];

    @ViewChild('noteEditor', { static: false }) noteEditor: NoteEditorComponent;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        public dialogRef: MatDialogRef<CheckReplyRecordManagementComponent>,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public functions: FunctionsService
    ) { }

    ngOnInit(): void {
        // this.checkReply();

        // FOR TEST
        setTimeout(() => {
            this.checking = false;
            this.selectedRes = this.data.resIds;
        }, 2000);
    }

    checkReply() {
        this.http.post(`../rest/resourcesList/users/${this.data.userId}/groups/${this.data.groupId}/baskets/${this.data.basketId}/actions/${this.data.action.id}/checkCloseWithFieldsAction`, { resources: this.data.resIds }).pipe(
            tap((data: any) => {
                // TO DO
                this.resourcesErrors = data.errors;
                this.selectedRes = data.success;
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    onSubmit() {
        this.loading = true;
        // this.executeAction();

        // FOR TEST
        setTimeout(() => {
            this.loading = false;
        }, 3000);
    }

    executeAction() {
        this.http.put(this.data.processActionRoute, { resources: this.selectedRes, note: this.noteEditor.getNote() }).pipe(
            tap(() => {
                this.dialogRef.close(this.selectedRes);
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
