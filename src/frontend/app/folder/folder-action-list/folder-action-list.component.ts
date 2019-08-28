import { Component, OnInit, Input, ViewChild, Output, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { MatMenuTrigger } from '@angular/material/menu';

import { Router } from '@angular/router';
import { ConfirmComponent } from '../../../plugins/modal/confirm.component';
import { filter, exhaustMap, tap } from 'rxjs/operators';

@Component({
    selector: 'app-folder-action-list',
    templateUrl: "folder-action-list.component.html",
    styleUrls: ['folder-action-list.component.scss'],
    providers: [NotificationService],
})
export class FolderActionListComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    @ViewChild(MatMenuTrigger, { static: false }) contextMenu: MatMenuTrigger;
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
    @Input('currentFolderInfo') currentFolderInfo: any;

    @Output('refreshEvent') refreshEvent = new EventEmitter<string>();
    @Output('refreshPanelFolders') refreshPanelFolders = new EventEmitter<string>();

    constructor(public http: HttpClient, private notify: NotificationService, public dialog: MatDialog, private router: Router) { }

    dialogRef: MatDialogRef<any>;
    
    ngOnInit(): void { }

    open(x: number, y: number, row: any) {

        //this.loadActionList();

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

    /*launchEvent(action: any, row: any) {
        this.arrRes = [];
        this.currentAction = action;

        this.arrRes = this.selectedRes;


        if (this.contextMode && this.selectedRes.length > 1) {
            this.contextMenuTitle = '';
            this.contextResId = 0;
        }
        
        if (row !== undefined){
            this.contextMenuTitle = row.alt_identifier;
        }

        if (action.component == 'v1Action' && this.arrRes.length > 1) {
            alert(this.lang.actionMassForbidden);
        } else if (action.component !== null) {
            
            this.http.put('../../rest/resourcesList/users/' + this.currentBasketInfo.ownerId + '/groups/' + this.currentBasketInfo.groupId + '/baskets/' + this.currentBasketInfo.basketId + '/lock', { resources: this.arrRes })
                .subscribe((data: any) => {
                    try {
                        let msgWarn = this.lang.warnLockRes + ' : ' + data.lockers.join(', ');

                        if (data.lockedResources != this.arrRes.length) {
                            msgWarn += this.lang.warnLockRes2 + '.';
                        }

                        if (data.lockedResources > 0) {
                            alert(data.lockedResources + ' ' + msgWarn);
                        }

                        if (data.lockedResources != this.arrRes.length) {
                            this.lock();
                            this[action.component]();
                        }
                    }
                    catch (error) {
                        console.log(error);
                        console.log(action.component);
                        alert(this.lang.actionNotExist);
                    }
                    this.loading = false;
                }, (err: any) => {
                    this.notify.handleErrors(err);
                });
        }

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

    unlockRest() {
        this.http.put('../../rest/resourcesList/users/' + this.currentBasketInfo.ownerId + '/groups/' + this.currentBasketInfo.groupId + '/baskets/' + this.currentBasketInfo.basketId + '/unlock', { resources: this.arrRes })
            .subscribe((data: any) => { }, (err: any) => { });
    }*/

    refreshFolders() {
        this.refreshPanelFolders.emit();  
    }

    refreshDaoAfterAction() {
        this.refreshEvent.emit();
    }

    unclassify() {
        this.dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete, msg: 'Voulez-vous enlever <b>' + this.selectedRes.length + '</b> document(s) du classement ?' } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.request('DELETE', '../../rest/folders/' + this.currentFolderInfo.id + '/resources', { body: { resources: this.selectedRes } })),
            tap((data: any) => {
                this.notify.success(this.lang.removedFromFolder);
                this.refreshFolders();
                this.refreshDaoAfterAction();
            })
        ).subscribe();
    }
}
