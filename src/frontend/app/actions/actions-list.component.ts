import { Component, OnInit, Input, ViewChild, Output, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatDialog, MatMenuTrigger } from '@angular/material';

import { ConfirmActionComponent } from './confirm-action/confirm-action.component';
import { EnabledBasketPersistenceActionComponent } from './enabled-basket-persistence/enabled-basket-persistence-action.component';
import { DisabledBasketPersistenceActionComponent } from './disabled-basket-persistence/disabled-basket-persistence-action.component';
import { ResMarkAsReadActionComponent } from './res-mark-as-read/res-mark-as-read-action.component';
import { CloseMailActionComponent } from './close-mail-action/close-mail-action.component';
import { UpdateDepartureDateActionComponent } from './update-departure-date-action/update-departure-date-action.component';
import { ProcessActionComponent } from './process-action/process-action.component';
import { Router } from '@angular/router';

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
    @Output() triggerEvent = new EventEmitter<string>();

    contextMenuPosition = { x: '0px', y: '0px' };
    contextMenuTitle = '';
    currentAction: any = {};
    basketInfo: any = {};
    contextResId = 0;
    currentLock: any = null;
    arrRes: any[] = [];

    actionsList: any[] = [];

    @Input('selectedRes') selectedRes: any;
    @Input('totalRes') totalRes: number;
    @Input('contextMode') contextMode: boolean;
    @Input('currentBasketInfo') currentBasketInfo: any;

    constructor(public http: HttpClient, private notify: NotificationService, public dialog: MatDialog, private router: Router) { }

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

    launchEvent(action: any) {
        this.arrRes = [];
        this.currentAction = action;

        if (this.contextMode && this.selectedRes.length == 0) {
            this.arrRes = [this.contextResId];
        } else {
            this.arrRes = this.selectedRes;
        }
        this.http.put('../../rest/resourcesList/users/' + this.currentBasketInfo.ownerId + '/groups/' + this.currentBasketInfo.groupId + '/baskets/' + this.currentBasketInfo.basketId + '/lock', { resources: this.arrRes })
            .subscribe((data: any) => {
                try {
                    if (data.lockedResources > 0) {
                        alert(data.lockedResources + ' ' + this.lang.warnLockRes + '.');
                    }
                    this.lock();
                    this[action.component]();
                }
                catch (error) {
                    console.log(error);
                    console.log(action);
                    alert(this.lang.actionNotExist);  
                }
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });
    }

    /* OPEN SPECIFIC ACTION */
    confirmAction() {
        const dialogRef = this.dialog.open(ConfirmActionComponent, {
            width: '500px',
            data: {
                contextMode: this.contextMode,
                contextChrono: this.contextMenuTitle,
                selectedRes: this.selectedRes,
                action: this.currentAction,
                currentBasketInfo: this.currentBasketInfo
            }
        });
        dialogRef.afterClosed().subscribe(result => {
            this.unlock();

            if (result == 'success') {
                this.endAction();
            }
        });
    }

    closeMailAction() {
        const dialogRef = this.dialog.open(CloseMailActionComponent, {
            width: '500px',
            data: {
                contextMode: this.contextMode,
                contextChrono: this.contextMenuTitle,
                selectedRes: this.selectedRes,
                action: this.currentAction,
                currentBasketInfo: this.currentBasketInfo
            }
        });
        dialogRef.afterClosed().subscribe(result => {
            this.unlock();

            if (result == 'success') {
                this.endAction();
            }
        });
    }

    updateDepartureDateAction() {
        const dialogRef = this.dialog.open(UpdateDepartureDateActionComponent, {
            width: '500px',
            data: {
                contextMode: this.contextMode,
                contextChrono: this.contextMenuTitle,
                selectedRes: this.selectedRes,
                action: this.currentAction,
                currentBasketInfo: this.currentBasketInfo
            }
        });
        dialogRef.afterClosed().subscribe(result => {
            this.unlock();

            if (result == 'success') {
                this.endAction();
            }
        });
    }

    disabledBasketPersistenceAction() {
        const dialogRef = this.dialog.open(DisabledBasketPersistenceActionComponent, {
            width: '500px',
            data: {
                contextMode: this.contextMode,
                contextChrono: this.contextMenuTitle,
                selectedRes: this.selectedRes,
                action: this.currentAction,
                currentBasketInfo: this.currentBasketInfo
            }
        });
        dialogRef.afterClosed().subscribe(result => {
            this.unlock();

            if (result == 'success') {
                this.endAction();
            }
        });
    }

    enabledBasketPersistenceAction() {
        const dialogRef = this.dialog.open(EnabledBasketPersistenceActionComponent, {
            width: '500px',
            data: {
                contextMode: this.contextMode,
                contextChrono: this.contextMenuTitle,
                selectedRes: this.selectedRes,
                action: this.currentAction,
                currentBasketInfo: this.currentBasketInfo
            }
        });
        dialogRef.afterClosed().subscribe(result => {
            this.unlock();

            if (result == 'success') {
                this.endAction();
            }
        });
    }

    resMarkAsReadAction() {
        const dialogRef = this.dialog.open(ResMarkAsReadActionComponent, {
            width: '500px',
            data: {
                contextMode: this.contextMode,
                contextChrono: this.contextMenuTitle,
                selectedRes: this.selectedRes,
                action: this.currentAction,
                currentBasketInfo: this.currentBasketInfo
            }
        });
        dialogRef.afterClosed().subscribe(result => {
            this.unlock();

            if (result == 'success') {
                this.endAction();
            }
        });
    }

    processAction() {

    // CALL GENERIC ACTION V1
    v1Action() {
        location.hash = "";
        window.location.href = 'index.php?page=view_baskets&module=basket&baskets='+this.currentBasketInfo.basket_id+'&basketId='+this.currentBasketInfo.basketId+'&resId='+this.arrRes[0]+'&userId='+this.currentBasketInfo.ownerId+'&groupIdSer='+this.currentBasketInfo.groupId+'&defaultAction='+this.currentAction.id;
        // WHEN V2
        /*this.dialog.open(ProcessActionComponent, {
            width: '500px',
            data: {
                contextMode: this.contextMode,
                contextChrono: this.contextMenuTitle,
                selectedRes: this.selectedRes,
                action: this.currentAction,
                currentBasketInfo: this.currentBasketInfo
            }
        });*/
    }

    // CALL SIGNATUREBOOK WITH V1 METHOD
    signatureBookAction() {
        location.hash = "";
        window.location.href = 'index.php?page=view_baskets&module=basket&baskets='+this.currentBasketInfo.basket_id+'&basketId='+this.currentBasketInfo.basketId+'&resId='+this.arrRes[0]+'&userId='+this.currentBasketInfo.ownerId+'&groupIdSer='+this.currentBasketInfo.groupId+'&defaultAction='+this.currentAction.id+'&signatureBookMode=true';

    }
    ////

    endAction() {
        this.triggerEvent.emit();
        this.notify.success(this.lang.action + ' : "' + this.currentAction.label_action + '" ' + this.lang.done);
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
                            id: 0,
                            label_action: this.lang.noAction,
                            component: ''
                        }];
                    }
                    this.loading = false;
                }, (err: any) => {
                    this.notify.handleErrors(err);
                });
        }
    }

    lock() {
        this.currentLock = setInterval(() => {
            this.http.put('../../rest/resourcesList/users/' + this.currentBasketInfo.ownerId + '/groups/' + this.currentBasketInfo.groupId + '/baskets/' + this.currentBasketInfo.basketId + '/lock', { resources: this.arrRes })
                .subscribe((data: any) => { }, (err: any) => { });
        }, 50000);
    }

    unlock() {
        clearInterval(this.currentLock);
    }
}
