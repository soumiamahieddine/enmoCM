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
import { ContactsListModalComponent } from '../contact/list/modal/contacts-list-modal.component';
import { DiffusionsListComponent } from '../diffusions/diffusions-list.component';

import { ContactService } from '../../service/contact.service';
import { VisaWorkflowComponent } from '../visa/visa-workflow.component';
import { PrivilegeService } from '../../service/privileges.service';
import { AvisWorkflowComponent } from '../avis/avis-workflow.component';



@Component({
    templateUrl: "process.component.html",
    styleUrls: [
        'process.component.scss',
        '../indexation/indexing-form/indexing-form.component.scss'
    ],
    providers: [NotificationService, AppService, ActionsService, ContactService],
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
            editMode: false,
            count: 0
        },
        {
            id: 'mails',
            icon: 'fas fa-envelope',
            label: this.lang.mailsSentAlt,
            count: 0
        },
        {
            id: 'visaCircuit',
            icon: 'fas fa-list-ol',
            label: this.lang.visaWorkflow,
            count: 0
        },
        {
            id: 'opinionCircuit',
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

    autoAction: boolean = false;

    @ViewChild('snav', { static: true }) sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) sidenavRight: MatSidenav;

    @ViewChild('appDocumentViewer', { static: true }) appDocumentViewer: DocumentViewerComponent;
    @ViewChild('indexingForm', { static: false }) indexingForm: IndexingFormComponent;
    @ViewChild('appDiffusionsList', { static: false }) appDiffusionsList: DiffusionsListComponent;
    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;
    @ViewChild('appAvisWorkflow', { static: false }) appAvisWorkflow: AvisWorkflowComponent;
    senderLightInfo: any = { 'displayName': null, 'fillingRate': null };
    hasContact: boolean = false;

    resourceFollowed: boolean = false;

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
        private contactService: ContactService,
        private router: Router,
        public privilegeService: PrivilegeService
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
            this.loadBadges();
            this.loadResource();

            if (this.appService.getViewMode()) {
                setTimeout(() => {
                    this.sidenavLeft.open();
                }, 800);
            }

            this.http.get(`../../rest/resources/${this.currentResourceInformations.resId}/users/${this.currentUserId}/groups/${this.currentGroupId}/baskets/${this.currentBasketId}/processingData`).pipe(
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
                this.resourceFollowed = data.followed;
                this.loadSenders();
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
        this.http.get(`../../rest/resources/${this.currentResourceInformations.resId}/items`).pipe(
            tap((data: any) => {
                this.processTool.forEach(element => {
                    element.count = data[element.id] !== undefined ? data[element.id] : 0;
                }); 
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    loadSenders() {
        if (this.currentResourceInformations.senders === undefined || this.currentResourceInformations.senders.length === 0) {
            this.hasContact = false;
            this.senderLightInfo = { 'displayName': this.lang.noSelectedContact, 'filling': null };
        } else if (this.currentResourceInformations.senders.length == 1) {
            this.hasContact = true;
            if (this.currentResourceInformations.senders[0].type === 'contact') {
                this.http.get('../../rest/contacts/' + this.currentResourceInformations.senders[0].id).pipe(
                    tap((data: any) => {
                        const arrInfo = [];

                        if (this.empty(data.firstname) && this.empty(data.lastname)) {
                            this.senderLightInfo = { 'displayName': data.company, 'filling': this.contactService.getFillingColor(data.fillingRate.thresholdLevel) };
                        } else {
                            arrInfo.push(data.firstname);
                            arrInfo.push(data.lastname);
                            if (!this.empty(data.company)) {
                                arrInfo.push('(' + data.company + ')');
                            }

                            this.senderLightInfo = { 'displayName': arrInfo.filter(info => info !== '').join(' '), 'filling': this.contactService.getFillingColor(data.fillingRate.thresholdLevel) };
                        }
                    })
                ).subscribe();
            } else if (this.currentResourceInformations.senders[0].type == 'entity') {
                this.http.get('../../rest/entities/' + this.currentResourceInformations.senders[0].id).pipe(
                    tap((data: any) => {
                        this.senderLightInfo = { 'displayName': data.entity_label, 'filling': null };
                    })
                ).subscribe();
            } else if (this.currentResourceInformations.senders[0].type == 'user') {
                this.http.get('../../rest/users/' + this.currentResourceInformations.senders[0].id).pipe(
                    tap((data: any) => {
                        this.senderLightInfo = { 'displayName': data.firstname + ' ' + data.lastname, 'filling': null };
                    })
                ).subscribe();
            }
        } else if (this.currentResourceInformations.senders.length > 1) {
            this.hasContact = true;
            this.senderLightInfo = { 'displayName': this.currentResourceInformations.senders.length + ' ' + this.lang.senders, 'filling': null };
        }
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
        if (this.currentTool === 'info' || this.isModalOpen('info')) {
            this.processAction();
        } else {
            this.autoAction = true;
            this.currentTool = 'info';
        }
    }

    triggerProcessAction() {
        if (this.autoAction) {
            this.processAction();
            this.autoAction = !this.autoAction;
        }
    }

    processAction() {
        if (this.indexingForm.isValidForm()) {
            if (this.isToolModified()) {
                const dialogRef = this.openConfirmModification();
                dialogRef.afterClosed().pipe(
                    tap((data: string) => {
                        if (data !== 'ok') {
                            this.refreshTool();
                        }
                    }),
                    filter((data: string) => data === 'ok'),
                    tap(() => {
                        this.saveTool();
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
        } else {
            this.notify.error(this.lang.mustFixErrors);
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

    isModalOpen(tool = this.currentTool) {
        return this.modalModule.map(module => module.id).indexOf(tool) > -1;
    }

    ngOnDestroy() {
        // unsubscribe to ensure no memory leaks
        this.subscription.unsubscribe();
    }

    changeTab(tabId: string) {

        if (this.isToolModified() && !this.isModalOpen()) {
            const dialogRef = this.openConfirmModification();

            dialogRef.afterClosed().pipe(
                tap((data: string) => {
                    if (data !== 'ok') {
                        this.currentTool = tabId;
                    }
                }),
                filter((data: string) => data === 'ok'),
                tap(() => {
                    this.saveTool();
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
        setTimeout(() => {
            this.loadResource();
        }, 400);
    }

    refreshBadge(nbRres: any, id: string) {
        this.processTool.filter(tool => tool.id === id)[0].count = nbRres;
    }

    openContact() {
        if (this.hasContact) {
            this.dialog.open(ContactsListModalComponent, { data: { title: `${this.currentResourceInformations.chrono} - ${this.currentResourceInformations.subject}`, mode: 'senders', resId: this.currentResourceInformations.resId } });
        }
    }

    saveListinstance() {
        this.appDiffusionsList.saveListinstance();
    }

    saveVisaWorkflow() {
        this.appVisaWorkflow.saveVisaWorkflow();
    }

    isToolModified() {
        if (this.currentTool === 'info' && this.indexingForm !== undefined && this.indexingForm.isResourceModified()) {
            return true;
        } else if (this.currentTool === 'diffusionList' && this.appDiffusionsList !== undefined && this.appDiffusionsList.isModified()) {
            return true;
        } else if (this.currentTool === 'visaCircuit' && this.appVisaWorkflow !== undefined && this.appVisaWorkflow.isModified()) {
            return true;
        } else if (this.currentTool === 'opinionCircuit' && this.appAvisWorkflow !== undefined && this.appAvisWorkflow.isModified()) {
            return true;
        } else {
            return false;
        }
    }

    refreshTool() {
        const tmpTool = this.currentTool;
        this.currentTool = '';
        setTimeout(() => {
            this.currentTool = tmpTool;
        }, 0);
    }

    async saveTool() {
        if (this.currentTool === 'info' && this.indexingForm !== undefined) {
            await this.indexingForm.saveData(this.currentUserId, this.currentGroupId, this.currentBasketId);
            this.loadResource();
        } else if (this.currentTool === 'diffusionList' && this.appDiffusionsList !== undefined) {
            await this.appDiffusionsList.saveListinstance();
            this.loadBadges();
        } else if (this.currentTool === 'visaCircuit' && this.appVisaWorkflow !== undefined) {
            await this.appVisaWorkflow.saveVisaWorkflow();
            this.loadBadges();
        } else if (this.currentTool === 'opinionCircuit' && this.appAvisWorkflow !== undefined) {
            await this.appAvisWorkflow.saveAvisWorkflow();
            this.loadBadges();
        }
    }

    empty(value: string) {

        if (value === null || value === undefined) {
            return true;

        } else if (Array.isArray(value)) {
            if (value.length > 0) {
                return false;
            } else {
                return true;
            }
        } else if (String(value) !== '') {
            return false;
        } else {
            return true;
        }
    }

    toggleFollow() {
        this.resourceFollowed = !this.resourceFollowed;

        if (this.resourceFollowed) {
            this.http.post('../../rest/resources/follow', {resources: [this.currentResourceInformations.resId]}).pipe(
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.http.request('DELETE', '../../rest/resources/unfollow', {body: {resources: [this.currentResourceInformations.resId]}}).pipe(
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    isToolEnabled(id: string) {
        if (id === 'history') {
            if (!this.privilegeService.hasCurrentUserPrivilege('view_full_history') && !this.privilegeService.hasCurrentUserPrivilege('view_doc_history')) {
                return false
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
}
