import { Component, OnInit, Input, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatDialog, MatMenuTrigger } from '@angular/material';

import { ConfirmActionComponent } from './confirm-action/confirm-action.component';

@Component({
    selector: 'app-actions-list',
    templateUrl: "actions-list.component.html",
    styleUrls: ['actions-list.component.scss'],
    providers: [NotificationService],
})
export class ActionsListComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    @ViewChild(MatMenuTrigger) contextMenu: MatMenuTrigger;
    
    contextMenuPosition = { x: '0px', y: '0px' };
    contextMenuTitle = '';
    contextResId = 0;

    @Input('selectedRes') selectedRes: any;
    @Input('totalRes') totalRes: number;
    @Input('contextMode') contextMode: boolean;

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

    open(x: number, y: number, row: any) {
    
        // Adjust the menu anchor position
        this.contextMenuPosition.x = x + 'px';
        this.contextMenuPosition.y = y + 'px';

        this.contextMenuTitle = row.alt_identifier;
        this.contextResId = row.res_id;

        // Opens the menu
        this.contextMenu.openMenu();
        
        // prevents default
        return false;
    }

    launchEvent(action: string) {
        let arrRes: any[] = [];

        if (this.contextMode && this.selectedRes.length == 0) {
            arrRes = [this.contextResId];
        } else {
            arrRes = this.selectedRes;  
        }
        this.http.put('../../rest/resources/lock', {resources : arrRes})
            .subscribe((data: any) => {
                try {
                    this[action]();
                }
                catch (error) {
                    alert("L'action n'existe pas!");
                }
                this.loading = false;
            }, (err: any) => {
                if (err.error.lockBy) {
                    alert("Courrier suivant : " + arrRes.join(', ') + " verrouillé par " + err.error.lockBy.join(', '));
                } else {
                    this.notify.handleErrors(err);
                } 
            });
    }

    /* OPEN SPECIFIC ACTION */
    confirmAction() {
        this.dialog.open(ConfirmActionComponent, {
            width: 'auto',
            data: { 
                contextMode : this.contextMode,
                contextChrono : this.contextMenuTitle,
                selectedRes : this.selectedRes
            }
        });
    }
}
