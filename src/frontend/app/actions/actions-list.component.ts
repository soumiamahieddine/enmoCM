import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatDialog } from '@angular/material';

import { ConfirmActionComponent } from '../actions/confirm-action/confirm-actions.component';

@Component({
    selector: 'app-actions-list',
    templateUrl: "actions-list.component.html",
    styleUrls: ['actions-list.component.scss'],
    providers: [NotificationService],
})
export class ActionsListComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    constructor(public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) { }

    ngOnInit(): void {
        /*this.http.get('../../rest/resourcesList/exportTemplate')
            .subscribe((data: any) => {
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });*/
        this.loading = false;
    }

    /* OPEN SPECIFIC ACTION */
    confirmAction() {
        this.dialog.open(ConfirmActionComponent, {
            width: '800px',
            data: { }
        });
    }
}
