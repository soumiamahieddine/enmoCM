import { Component, OnInit, Input, ViewChild, HostListener } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatDialog, MatMenuTrigger } from '@angular/material';

import { ConfirmActionComponent } from './confirm-action/confirm-action.component';
import { ClosingActionComponent } from './closing-action/closing-action.component';

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
    currentActionName = '';
    basketInfo: any = {};
    contextResId = 0;

    actionsList: any[] = [];

    @Input('selectedRes') selectedRes: any;
    @Input('totalRes') totalRes: number;
    @Input('contextMode') contextMode: boolean;
    @Input('currentBasketInfo') currentBasketInfo: any;

    constructor(public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) { }

    ngOnInit(): void { }
      
    open(x: number, y: number, row: any) {

        this.loadActionList();
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

    launchEvent(action: string, actionName: string) {
        let arrRes: any[] = [];
        this.currentActionName = actionName;

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
                    alert(this.lang.actionNotExist);
                }
                this.loading = false;
            }, (err: any) => {
                if (err.error.lockBy) {
                    alert(this.lang.thisRes + " : " + arrRes.join(', ') + " " + this.lang.lockedBy + " " + err.error.lockBy.join(', '));
                } else {
                    this.notify.handleErrors(err);
                } 
            });
    }

    /* OPEN SPECIFIC ACTION */
    confirmAction() {
        this.dialog.open(ConfirmActionComponent, {
            width: '500px',
            data: { 
                contextMode : this.contextMode,
                contextChrono : this.contextMenuTitle,
                selectedRes : this.selectedRes,
                actionName : this.currentActionName
            }
        });
    }

    closingAction() {
        this.dialog.open(ClosingActionComponent, {
            width: '500px',
            data: { 
                contextMode : this.contextMode,
                contextChrono : this.contextMenuTitle,
                selectedRes : this.selectedRes,
                actionName : this.currentActionName
            }
        });
    }

    loadActionList() {
        
        if (JSON.stringify(this.basketInfo) != JSON.stringify(this.currentBasketInfo)) {

            this.basketInfo = JSON.parse(JSON.stringify(this.currentBasketInfo));

            this.http.get('../../rest/resourcesList/users/' + this.currentBasketInfo.ownerId + '/groups/' + this.currentBasketInfo.groupId + '/baskets/' + this.currentBasketInfo.basketId + '/actions')
            .subscribe((data: any) => {
                if (data.actions.length > 0) {
                    this.actionsList = data.actions;
                } else {
                    this.actionsList = [{
                        label_action : 'Aucune action',
                        component : ''
                    }];
                }
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });
        }
        
    }
}
