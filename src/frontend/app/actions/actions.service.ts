import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../app/translate.component';
import { tap, catchError, filter, finalize, exhaustMap, map } from 'rxjs/operators';
import { of, forkJoin } from 'rxjs';
import { NotificationService } from '../notification.service';
import { ConfirmActionComponent } from './confirm-action/confirm-action.component';
import { MatDialog } from '@angular/material';
import { Router } from '@angular/router';

@Injectable()
export class ActionsService {

    lang: any = LANG;

    mode: string = 'indexing';

    currentResourceLock: any = null;

    currentAction: any = null;
    currentUserId: number = null;
    currentGroupId: number = null;
    currentBasketId: number = null;
    currentResIds: number[] = [];
    currentResourceInformations: any = null;

    loading: boolean = false;

    constructor(
        public http: HttpClient,
        public dialog: MatDialog,
        private notify: NotificationService,
        private router: Router
    ) { }

    setLoading(state: boolean) {
        this.loading = state;
    }

    setActionInformations(action: any, userId: number, groupId: number, basketId: number, resIds: number[]) {

        if (action !== null && userId > 0 && groupId > 0) {
            this.mode = basketId === null ? 'indexing' : 'process';
            this.currentAction = action;
            this.currentUserId = userId;
            this.currentGroupId = groupId;
            this.currentBasketId = basketId;
            return true;
        } else {
            let arrErr = [];
            
            console.log('Bad informations: ');
            console.log({'action' : action}, {'userId': userId}, {'groupId': groupId}, {'basketId': basketId}, {'resIds': resIds});

            this.notify.error('Une erreur est survenue');
            return false;
        }
    }

    saveDocument(datas: any) {
        this.loading = true;
        this.setResourceInformations(datas);
        return this.http.post('../../rest/resources', this.currentResourceInformations );
    }

    setResourceInformations(datas: any) {
        this.currentResourceInformations = datas;
    }

    setResourceIds(resId: number[]) {
        this.currentResourceInformations['resId'] = resId;
        this.currentResIds = resId;
    }

    launchIndexingAction(action: any, userId: number, groupId: number, datas: any) {

        if (this.setActionInformations(action, userId, groupId, null, null)) {
            this.setResourceInformations(datas);
            this.loading = true;
            try {
                this[action.component]();
            }
            catch (error) {
                console.log(error);
                console.log(action.component);
                alert(this.lang.actionNotExist);
            }
        }
    }


    launchAction(action: any, userId: number, groupId: number, basketId: number, resIds: number[]) {

        if (this.setActionInformations(action, userId, groupId, basketId, resIds)) {
            this.loading = true;
            this.http.put(`../../rest/resourcesList/users/${userId}/groups/${groupId}/baskets/${basketId}/lock`, { resources: resIds }).pipe(
                tap((data: any) => {
                    if (this.canExecuteAction(data.lockedResources, data.lockers, resIds)) {
                        try {
                            this.lockResource();
                            this[action.component]();
                        }
                        catch (error) {
                            console.log(error);
                            console.log(action.component);
                            alert(this.lang.actionNotExist);
                        }
                    }
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            )
        }
    }

    canExecuteAction(numberOflockedResIds: number, usersWholocked: any[], resIds: number[]) {
        let msgWarn = this.lang.warnLockRes + ' : ' + usersWholocked.join(', ');

        if (numberOflockedResIds != resIds.length) {
            msgWarn += this.lang.warnLockRes2 + '.';
        }

        if (numberOflockedResIds > 0) {
            alert(numberOflockedResIds + ' ' + msgWarn);
        }

        if (numberOflockedResIds != resIds.length) {
            return true;
        } else {
            return false;
        }
    }


    lockResource() {
        this.currentResourceLock = setInterval(() => {
            this.http.put(`../../rest/resourcesList/users/${this.currentUserId}/groups/${this.currentGroupId}/baskets/${this.currentBasketId}/lock`, { resources: this.currentResIds }).pipe(
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }, 50000);
    }

    unlockResource() {
        if (this.currentResIds.length > 0) {
            this.http.put(`../../rest/resourcesList/users/${this.currentUserId}/groups/${this.currentGroupId}/baskets/${this.currentBasketId}/unlock`, { resources: this.currentResIds }).pipe(
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe(); 
        }
    }

    stopRefreshResourceLock() {
        if (this.currentResourceLock !== null) { 
            clearInterval(this.currentResourceLock);
        }
    }


    /* OPEN SPECIFIC ACTION */
    confirmAction() {

        const dialogRef = this.dialog.open(ConfirmActionComponent, {
            width: '500px',
            data: {
                resIds: this.currentResIds,
                resource: this.currentResourceInformations,
                action: this.currentAction,
                userId: this.currentUserId,
                groupId: this.currentGroupId,
                basketId: this.currentBasketId
            }
        });

        dialogRef.afterClosed().pipe(
            tap(() => {
                this.stopRefreshResourceLock();
            }),
            filter((data: string) => data === 'success'),
            tap((result: any) => {
                this.endAction(result);
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }


    endAction(status: any) {
        this.unlockResource();

        if (this.mode === 'indexing') {
            this.router.navigate(['/home']);
        }
        this.notify.success(this.lang.action + ' : "' + this.currentAction.label + '" ' + this.lang.done);
    }
}
