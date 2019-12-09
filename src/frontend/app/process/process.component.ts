import { Component, OnInit, ViewChild, ViewContainerRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatDialog } from '@angular/material/dialog';
import { MatSidenav } from '@angular/material/sidenav';

import { ActivatedRoute, Router } from '@angular/router';
import { HeaderService } from '../../service/header.service';
import { FiltersListService } from '../../service/filtersList.service';

import { Overlay } from '@angular/cdk/overlay';
import { AppService } from '../../service/app.service';
import { ActionsService } from '../actions/actions.service';
import { tap, catchError, map, finalize, filter } from 'rxjs/operators';
import { of, Subscription } from 'rxjs';
import { DocumentViewerComponent } from '../viewer/document-viewer.component';
import { IndexingFormComponent } from '../indexation/indexing-form/indexing-form.component';
import { ConfirmComponent } from '../../plugins/modal/confirm.component';

@Component({
    templateUrl: "process.component.html",
    styleUrls: [
        'process.component.scss',
        '../indexation/indexing-form/indexing-form.component.scss'
    ],
    providers: [NotificationService, AppService, ActionsService],
})
export class ProcessComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = false;

    currentResourceLock: any = null;

    actionsList: any[] = [];
    currentUserId: number = null;
    currentBasketId: number = null;
    currentGroupId: number = null;

    selectedAction: any = {
        id: 0,
        label: '',
        component: '',
        default: false,
        categoryUse: []
    };

    currentResourceInformations: any = {};

    processTool: any[] = [
        {
            id: 'dashboard',
            icon: 'fas fa-columns',
            label: this.lang.newsFeed,
            count: 0
        },
        {
            id: 'history',
            icon: 'fas fa-history',
            label: this.lang.history,
            count: 0
        },
        {
            id: 'notes',
            icon: 'fas fa-pen-square',
            label: this.lang.notesAlt,
            count: 0
        },
        {
            id: 'attachments',
            icon: 'fas fa-paperclip',
            label: this.lang.attachments,
            count: 0
        },
        {
            id: 'link',
            icon: 'fas fa-link',
            label: this.lang.links,
            count: 0
        },
        {
            id: 'diffusionList',
            icon: 'fas fa-share-alt',
            label: this.lang.diffusionList,
            count: 0
        },
        {
            id: 'mails',
            icon: 'fas fa-envelope',
            label: this.lang.mailsSentAlt,
            count: 0
        },
        {
            id: 'visa',
            icon: 'fas fa-list-ol',
            label: this.lang.visaWorkflow,
            count: 0
        },
        {
            id: 'avis',
            icon: 'fas fa-comment-alt',
            label: this.lang.avis,
            count: 0
        },
        {
            id: 'info',
            icon: 'fas fa-info-circle',
            label: this.lang.informations,
            count: 0
        }
    ];

    modalModule: any[] = [];

    currentTool: string = 'dashboard';

    subscription: Subscription;

    actionEnded: boolean = false;

    canEditData: boolean = false;

    @ViewChild('snav', { static: true }) sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) sidenavRight: MatSidenav;

    @ViewChild('appDocumentViewer', { static: true }) appDocumentViewer: DocumentViewerComponent;
    @ViewChild('indexingForm', { static: false }) indexingForm: IndexingFormComponent;

    constructor(
        private route: ActivatedRoute,
        private _activatedRoute: ActivatedRoute,
        public http: HttpClient,
        public dialog: MatDialog,
        private headerService: HeaderService,
        public filtersListService: FiltersListService,
        private notify: NotificationService,
        public overlay: Overlay,
        public viewContainerRef: ViewContainerRef,
        public appService: AppService,
        public actionService: ActionsService,
        private router: Router
    ) {
        // Event after process action 
        this.subscription = this.actionService.catchAction().subscribe(message => {
            this.actionEnded = true;
            clearInterval(this.currentResourceLock);
            this.router.navigate([`/basketList/users/${this.currentUserId}/groups/${this.currentGroupId}/baskets/${this.currentBasketId}`]);
        });
    }

    ngOnInit(): void {
        this.loading = true;

        this.headerService.setHeader(this.lang.eventProcessDoc);

        this.route.params.subscribe(params => {
            this.currentUserId = params['userSerialId'];
            this.currentGroupId = params['groupSerialId'];
            this.currentBasketId = params['basketId'];

            this.currentResourceInformations = {
                resId: params['resId'],
                mailtracking: false
            };

            this.lockResource();

            this.loadResource();

            this.http.get(`../../rest/resourcesList/users/${this.currentUserId}/groups/${this.currentGroupId}/baskets/${this.currentBasketId}/listEventData`).pipe(
                tap((data: any) => {
                    if (data.listEventData !== null) {
                        this.currentTool = data.listEventData.defaultTab;
                        this.canEditData = data.listEventData.canUpdate;
                    }
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();

            this.http.get(`../../rest/resourcesList/users/${this.currentUserId}/groups/${this.currentGroupId}/baskets/${this.currentBasketId}/actions?resId=${this.currentResourceInformations.resId}`).pipe(
                map((data: any) => {
                    data.actions = data.actions.map((action: any, index: number) => {
                        return {
                            id: action.id,
                            label: action.label,
                            component: action.component,
                            categoryUse: action.categories
                        }
                    });
                    return data;
                }),
                tap((data: any) => {
                    this.selectedAction = data.actions[0];
                    this.actionsList = data.actions;
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();

        }, (err: any) => {
            this.notify.handleErrors(err);
        });
    }

    isActionEnded() {
        return this.actionEnded;
    }

    loadResource() {
        this.http.get(`../../rest/resources/${this.currentResourceInformations.resId}?light=true`).pipe(
            tap((data: any) => {
                this.currentResourceInformations = data;
                this.loadBadges();
                this.headerService.setHeader(this.lang.eventProcessDoc, this.lang[this.currentResourceInformations.categoryId]);
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    loadBadges() {
        this.processTool.forEach(element => {
            element.count = this.currentResourceInformations[element.id] !== undefined ? this.currentResourceInformations[element.id] : 0;
        });
    }

    lockResource() {
        this.http.put(`../../rest/resourcesList/users/${this.currentUserId}/groups/${this.currentGroupId}/baskets/${this.currentBasketId}/lock`, { resources: [this.currentResourceInformations.resId] }).pipe(
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();

        this.currentResourceLock = setInterval(() => {
            this.http.put(`../../rest/resourcesList/users/${this.currentUserId}/groups/${this.currentGroupId}/baskets/${this.currentBasketId}/lock`, { resources: [this.currentResourceInformations.resId] }).pipe(
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }, 50000);
    }

    unlockResource() {
        clearInterval(this.currentResourceLock);

        this.http.put(`../../rest/resourcesList/users/${this.currentUserId}/groups/${this.currentGroupId}/baskets/${this.currentBasketId}/unlock`, { resources: [this.currentResourceInformations.resId] }).pipe(
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    onSubmit() {
        if (this.currentTool === 'info' && this.indexingForm.isResourceModified()) {
            const dialogRef = this.openConfirmModification();
            dialogRef.afterClosed().pipe(
                tap((data: string) => {
                    if (data !== 'ok') {
                        this.currentTool = '';
                        setTimeout(() => {
                            this.currentTool = 'info';
                        }, 0);
                    }
                }),
                filter((data: string) => data === 'ok'),
                tap(() => {
                    this.indexingForm.saveData(this.currentUserId, this.currentGroupId, this.currentBasketId);
                }),
                finalize(() => this.actionService.launchAction(this.selectedAction, this.currentUserId, this.currentGroupId, this.currentBasketId, [this.currentResourceInformations.resId], this.currentResourceInformations, false)),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.actionService.launchAction(this.selectedAction, this.currentUserId, this.currentGroupId, this.currentBasketId, [this.currentResourceInformations.resId], this.currentResourceInformations, false);
        }


    }

    showActionInCurrentCategory(action: any) {

        if (this.selectedAction.categoryUse.indexOf(this.currentResourceInformations.categoryId) === -1) {
            const newAction = this.actionsList.filter(action => action.categoryUse.indexOf(this.currentResourceInformations.categoryId) > -1)[0];
            if (newAction !== undefined) {
                this.selectedAction = this.actionsList.filter(action => action.categoryUse.indexOf(this.currentResourceInformations.categoryId) > -1)[0];
            } else {
                this.selectedAction = {
                    id: 0,
                    label: '',
                    component: '',
                    default: false,
                    categoryUse: []
                };
            }
        }
        return action.categoryUse.indexOf(this.currentResourceInformations.categoryId) > -1;
    }

    selectAction(action: any) {
        this.selectedAction = action;
    }

    createModal() {
        this.modalModule.push(this.processTool.filter(module => module.id === this.currentTool)[0]);
    }

    removeModal(index: number) {
        if (this.modalModule[index].id === 'info' && this.indexingForm.isResourceModified()) {
            const dialogRef = this.openConfirmModification();

            dialogRef.afterClosed().pipe(
                tap((data: string) => {
                    if (data !== 'ok') {
                        this.modalModule.splice(index, 1);
                    }
                }),
                filter((data: string) => data === 'ok'),
                tap(() => {
                    this.indexingForm.saveData(this.currentUserId, this.currentGroupId, this.currentBasketId);
                    setTimeout(() => {
                        this.loadResource();
                    }, 400);
                    this.modalModule.splice(index, 1);
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.modalModule.splice(index, 1);
        }
    }

    isModalOpen() {
        return this.modalModule.map(module => module.id).indexOf(this.currentTool) > -1;
    }

    ngOnDestroy() {
        // unsubscribe to ensure no memory leaks
        this.subscription.unsubscribe();
    }

    changeTab(tabId: string) {
        if (this.currentTool === 'info' && this.indexingForm.isResourceModified() && !this.isModalOpen()) {
            const dialogRef = this.openConfirmModification();

            dialogRef.afterClosed().pipe(
                tap((data: string) => {
                    if (data !== 'ok') {
                        this.currentTool = tabId;
                    }
                }),
                filter((data: string) => data === 'ok'),
                tap(() => {
                    this.indexingForm.saveData(this.currentUserId, this.currentGroupId, this.currentBasketId);
                    setTimeout(() => {
                        this.loadResource();
                    }, 400);
                    this.currentTool = tabId;
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.currentTool = tabId;
        }
    }

    openConfirmModification() {
        return this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.confirm, msg: this.lang.saveModifiedData, buttonValidate: this.lang.yes, buttonCancel: this.lang.no } });
    }

    confirmModification() {
        this.indexingForm.saveData(this.currentUserId, this.currentGroupId, this.currentBasketId);
    }

    refreshBadge(nbRres: any, id: string) {
        this.processTool.filter(tool => tool.id === id)[0].count = nbRres;
    }
}
