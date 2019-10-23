import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { catchError, finalize, tap } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
    templateUrl: "send-shipping-action.component.html",
    styleUrls: ['send-shipping-action.component.scss'],
    providers: [NotificationService],
})
export class SendShippingActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    shippings: any[] = [{
        label: '',
        description: '',
        options: {
            shapingOptions: [],
            sendMode: '',
        },
        fee: 0,
        account: {
            id: '',
            password: ''
        },
    }];

    currentShipping: any = null;

    entitiesList: string[] = [];
    attachList: any[] = [];

    mailsNotSend: any[] = []

    @ViewChild('noteEditor', { static: false }) noteEditor: NoteEditorComponent;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<SendShippingActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        this.loading = true;

        this.http.post('../../rest/resourcesList/users/' + this.data.userId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/actions/' + this.data.action.id + '/checkShippings', { resources: this.data.resIds })
            .subscribe((data: any) => {
                this.shippings = data.shippingTemplates;
                this.mailsNotSend = data.canNotSend;
                this.entitiesList = data.entities;
                this.attachList = data.resources;
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
        let realResSelected: string[] = this.attachList.map((e: any) => { return e.res_id_master; });

        this.http.put(this.data.processActionRoute, {resources : realResSelected, data: { shippingTemplateId: this.currentShipping.id }, note : this.noteEditor.getNoteContent()}).pipe(
            tap((data: any) => {
                if (data && data.errors != null) {
                    this.notify.error(data.errors);
                } else {
                    this.dialogRef.close('success');
                }
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

}
