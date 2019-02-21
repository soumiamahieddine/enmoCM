import { Component, OnInit, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialog } from '@angular/material';

@Component({
    templateUrl: "closing-action.component.html",
    styleUrls: ['closing-action.component.scss'],
    providers: [NotificationService],
})
export class ClosingActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    constructor(public http: HttpClient, private notify: NotificationService, public dialog: MatDialog, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        /*this.http.get('../../rest/resourcesList/exportTemplate')
            .subscribe((data: any) => {
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });*/
        this.loading = false;
    }
}
