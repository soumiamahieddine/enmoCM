import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { tap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';
import { FunctionsService } from '../../../service/functions.service';
import { AvisWorkflowComponent } from '../../avis/avis-workflow.component';

@Component({
    templateUrl: "send-avis-workflow-action.component.html",
    styleUrls: ['send-avis-workflow-action.component.scss'],
})
export class SendAvisWorkflowComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    resourcesError: any[] = [];

    noResourceToProcess: boolean = null;

    @ViewChild('noteEditor', { static: true }) noteEditor: NoteEditorComponent;
    @ViewChild('appAvisWorkflow', { static: false }) appAvisWorkflow: AvisWorkflowComponent;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public dialogRef: MatDialogRef<SendAvisWorkflowComponent>,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public functions: FunctionsService) { }

    async ngOnInit(): Promise<void> {        
        if (this.data.resIds.length > 0) {
            this.loading = true;
            await this.checkAvisWorkflow();
            this.loading = false;
        }
        if (this.data.resIds.length === 1) {
            await this.appAvisWorkflow.loadWorkflow(this.data.resIds[0]);
            if (this.appAvisWorkflow.emptyWorkflow()) {
                this.appAvisWorkflow.loadDefaultWorkflow(this.data.resIds[0]);
            }
        }
    }

    checkAvisWorkflow() {
        this.resourcesError = [];

        // TO DO : WAIT BACK
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
        if (this.data.resIds.length === 0) {
            let res = await this.indexDocument();
            if (res) {
                res = await this.appAvisWorkflow.saveAvisWorkflow(this.data.resIds);
            }
            if (res) {
                this.executeAction(this.data.resIds);
            }
        } else {
            const realResSelected: number[] = this.data.resIds.filter((resId: any) => this.resourcesError.map(resErr => resErr.res_id).indexOf(resId) === -1);

            const res = await this.appAvisWorkflow.saveAvisWorkflow(realResSelected);

            if (res) {
                this.executeAction(realResSelected);
            }
        }
        this.loading = false;
    }

    indexDocument() {
        return new Promise((resolve, reject) => {
            this.http.post('../../rest/resources', this.data.resource).pipe(
                tap((data: any) => {
                    this.data.resIds = [data.resId];
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });
    }

    executeAction(realResSelected: number[]) {

        this.http.put(this.data.processActionRoute, { resources: realResSelected, note: this.noteEditor.getNoteContent() }).pipe(
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
        if (!this.noResourceToProcess && this.appAvisWorkflow !== undefined && !this.appAvisWorkflow.emptyWorkflow() && !this.appAvisWorkflow.workflowEnd()) {
            return true;
        } else {
            return false;
        }
    }
}
