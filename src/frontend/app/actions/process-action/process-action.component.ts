import { Component, OnInit, Inject } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { HttpClient } from '@angular/common/http';

@Component({
    templateUrl: "process-action.component.html",
    styleUrls: ['process-action.component.scss'],
    providers: [NotificationService],
})
export class ProcessActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<ProcessActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        window.location.href = 'index.php?page=view_baskets&module=basket&baskets=MyBasket&resId=105&defaultAction=19';
    }
}
