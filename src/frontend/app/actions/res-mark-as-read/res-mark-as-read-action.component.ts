import { Component, OnInit, Inject } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { HttpClient } from '@angular/common/http';

@Component({
    templateUrl: "../confirm-action/confirm-action.component.html",
    styleUrls: ['../confirm-action/confirm-action.component.scss'],
    providers: [NotificationService],
})
export class ResMarkAsReadActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<ResMarkAsReadActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void { }

    onSubmit(): void {
        this.loading = true;
        this.http.put('../../rest/resourcesList/users/' + this.data.currentBasketInfo.ownerId + '/groups/' + this.data.currentBasketInfo.groupId + '/baskets/' + this.data.currentBasketInfo.basketId + '/actions/' + this.data.action.id, {resources : this.data.selectedRes, data : {basketId : this.data.currentBasketInfo.basketId}})
            .subscribe((data: any) => {
                this.loading = false;
                this.dialogRef.close('success');
            }, (err: any) => {
                this.notify.handleErrors(err);
                this.loading = false;
            });
    }
    
}
