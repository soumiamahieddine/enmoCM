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
import { tap, finalize, catchError, map } from 'rxjs/operators';
import { of } from 'rxjs';
import { DocumentViewerComponent } from '../viewer/document-viewer.component';

@Component({
    templateUrl: "process.component.html",
    styleUrls: [
        'process.component.scss',
        '/../indexation/indexing-form/indexing-form.component.scss'
    ],
    providers: [NotificationService, AppService, ActionsService],
})
export class ProcessComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = false;

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

    currentResourceInformations: any = {

    }

    processTool: any[] = [
        {
            id: 'dashboard',
            icon : 'fas fa-columns',
            label: this.lang.dashboard,   
        },
        {
            id: 'history',
            icon : 'fas fa-history',
            label: this.lang.history,
        },
        {
            id: 'notes',
            icon : 'fas fa-pen-square',
            label: this.lang.notes,   
        },
        {
            id: 'attachments',
            icon : 'fas fa-paperclip',
            label: this.lang.attachments,   
        },
        {
            id: 'link',
            icon : 'fas fa-link',
            label: this.lang.links,   
        },
        {
            id: 'diffusionList',
            icon : 'fas fa-share-alt',
            label: this.lang.diffusionList,   
        },
        {
            id: 'mails',
            icon : 'fas fa-envelope',
            label: this.lang.mailsSent,   
        },
        {
            id: 'visa',
            icon : 'fas fa-envelope',
            label: this.lang.visaWorkflow,   
        },
        {
            id: 'avis',
            icon : 'fas fa-envelope',
            label: this.lang.avis,   
        },
        {
            id: 'info',
            icon : 'fas fa-info-circle',
            label: this.lang.informations,   
        }
    ] 

    @ViewChild('snav', { static: true }) sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) sidenavRight: MatSidenav;

    @ViewChild('appDocumentViewer', { static: true }) appDocumentViewer: DocumentViewerComponent;

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
    }

    ngOnInit(): void {
        this.loading = false;

        this.headerService.setHeader("Traitement d'un courrier");

        this.route.params.subscribe(params => {
            this.currentUserId = params['userSerialId'];
            this.currentGroupId = params['groupSerialId'];
            this.currentBasketId = params['basketId'];

            this.processTool[0]['selected'] = true;
            
            this.currentResourceInformations = {
                resId: params['resId'],
                category: 'outgoing',
                mailtracking: false
            }

            // TO : WAIT RIGHT ROUTE FOR PROCESS
            this.http.get(`../../rest/resourcesList/users/${this.currentUserId}/groups/${this.currentGroupId}/baskets/${this.currentBasketId}/actions`).pipe(
                map((data: any) => {
                    data.actions = data.actions.map((action: any, index: number) => {
                        return {
                            id: action.id,
                            label: action.label,
                            component: action.component,
                            enabled: action.enabled,
                            default: index === 0 ? true : false,
                            categoryUse: ['incoming', 'outgoing']
                        }
                    });
                    return data;
                }),
                tap((data: any) => {
                    this.selectedAction = data.actions[0];
                    this.actionsList = data.actions;
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();

            this.appDocumentViewer.loadRessource(this.currentResourceInformations.resId);
            /*this.http.get('../../rest/resources/' +  params['resId']).pipe(
                tap((data: any) => {
                    this.currentResourceInformations = data.resource;
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();*/

        },
            (err: any) => {
                this.notify.handleErrors(err);
            });
    }

    onSubmit() {
        this.actionService.launchAction(this.selectedAction,  this.currentUserId, this.currentGroupId, this.currentBasketId, this.currentResourceInformations.resId, this.currentResourceInformations);
    }

    showActionInCurrentCategory(action: any) {

        if (this.selectedAction.categoryUse.indexOf(this.currentResourceInformations.category) === -1) {
            const newAction = this.actionsList.filter(action => action.categoryUse.indexOf(this.currentResourceInformations.category) > -1)[0];
            if (newAction !== undefined) {
                this.selectedAction = this.actionsList.filter(action => action.categoryUse.indexOf(this.currentResourceInformations.category) > -1)[0];
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
        if (action.categoryUse.indexOf(this.currentResourceInformations.category) > -1) {
            return true;
        } else {
            return false;
        }
    }
}