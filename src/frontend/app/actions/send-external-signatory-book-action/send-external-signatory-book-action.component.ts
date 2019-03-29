import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';

@Component({
    templateUrl: "send-external-signatory-book-action.component.html",
    styleUrls: ['send-external-signatory-book-action.component.scss'],
    providers: [NotificationService],
})
export class sendExternalSignatoryBookActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    @ViewChild('noteEditor') noteEditor: NoteEditorComponent;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<sendExternalSignatoryBookActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        this.loading = false;

        // this.http.post('../../rest/resourcesList/users/' + this.data.currentBasketInfo.ownerId + '/groups/' + this.data.currentBasketInfo.groupId + '/baskets/' + this.data.currentBasketInfo.basketId + '/actions/' + this.data.action.id + '/checkShippings', { resources: this.data.selectedRes })
        //     .subscribe((data: any) => {
        //         this.shippings = data.shippingTemplates;
        //         this.mailsNotSend = data.canNotSend;
        //         this.entitiesList = data.entities;
        //         this.attachList = data.resources;
        //         this.loading = false;
        //     }, (err: any) => {
        //         this.notify.handleErrors(err);
        //         this.loading = false;
        //     });
    }

    onSubmit(): void {
        this.loading = false;

        this.http.put('../../rest/resourcesList/users/' + this.data.currentBasketInfo.ownerId + '/groups/' + this.data.currentBasketInfo.groupId + '/baskets/' + this.data.currentBasketInfo.basketId + '/actions/' + this.data.action.id, { resources: this.data.selectedRes, note: this.noteEditor.getNoteContent() })
            .subscribe((data: any) => {
                if (data && data.data != null) {
                    this.dialogRef.close('success');
                }
                if (data && data.errors != null) {
                    this.notify.error(data.errors);
                }
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
                this.loading = false;
            });
    }

}
