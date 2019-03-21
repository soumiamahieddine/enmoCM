import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';

@Component({
    templateUrl: "send-shipping-action.component.html",
    styleUrls: ['send-shipping-action.component.scss'],
    providers: [NotificationService],
})
export class SendShippingActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    shippings: any[] = [{
        label: 'Envoi Maileva',
        description: '',
        options: {
            shapingOptions: ['color'],
            sendMode: 'fast',
        },
        fee: {
            first_page_price: 0.1,
            next_page_price: 0.3,
            postage_price: 2,
        },
        account: {
            id: '',
            password: ''
        },
        entities: []
    }];

    mailsNotSend: any[] = [
        {
            res_id : 100,
            alt_identifier : 'MAARCH/2019A/0001',
            reason : 'noAdress'
        } 
    ]

    totalPrice : number = 10;

    @ViewChild('noteEditor') noteEditor: NoteEditorComponent;
    
    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<SendShippingActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void { }

    onSubmit(): void {
        this.loading = true;
        /*this.http.put('../../rest/resourcesList/users/' + this.data.currentBasketInfo.ownerId + '/groups/' + this.data.currentBasketInfo.groupId + '/baskets/' + this.data.currentBasketInfo.basketId + '/actions/' + this.data.action.id, {resources : this.data.selectedRes, note : this.noteEditor.getNoteContent()})
            .subscribe((data: any) => {
                this.loading = false;
                this.dialogRef.close('success');
            }, (err: any) => {
                this.notify.handleErrors(err);
                this.loading = false;
            });*/
    }
    
}
