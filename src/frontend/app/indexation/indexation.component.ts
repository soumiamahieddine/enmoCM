import { Component, OnInit, ViewChild, ViewContainerRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { MatSidenav } from '@angular/material/sidenav';

import { ActivatedRoute } from '@angular/router';
import { HeaderService } from '../../service/header.service';
import { FiltersListService } from '../../service/filtersList.service';

import { Overlay } from '@angular/cdk/overlay';
import { AppService } from '../../service/app.service';
import { IndexingFormComponent } from './indexing-form/indexing-form.component';
import { tap, finalize, catchError, map, filter, exhaustMap } from 'rxjs/operators';
import { of } from 'rxjs';
import { DocumentViewerComponent } from '../viewer/document-viewer.component';
import { ConfirmComponent } from '../../plugins/modal/confirm.component';
import { AddPrivateIndexingModelModalComponent } from './private-indexing-model/add-private-indexing-model-modal.component';

@Component({
    templateUrl: "indexation.component.html",
    styleUrls: [
        'indexation.component.scss',
        'indexing-form/indexing-form.component.scss'
    ],
    providers: [NotificationService, AppService],
})
export class IndexationComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = false;


    @ViewChild('snav', { static: true }) sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) sidenavRight: MatSidenav;

    @ViewChild('indexingForm', { static: false }) indexingForm: IndexingFormComponent;
    @ViewChild('appDocumentViewer', { static: false }) appDocumentViewer: DocumentViewerComponent;

    indexingModels: any[] = [];
    currentIndexingModel: any = {};
    currentGroupId: number;

    actionsList: any[] = [];
    selectedAction: any = {};
    tmpFilename: string = '';

    dialogRef: MatDialogRef<any>;

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
        public appService: AppService) {

        _activatedRoute.queryParams.subscribe(
            params => this.tmpFilename = params.tmpfilename
        );
    }

    ngOnInit(): void {
        this.loading = false;

        this.headerService.setHeader("Enregistrement d'un courrier");

        this.route.params.subscribe(params => {
            this.currentGroupId = params['groupId'];
            this.http.get("../../rest/indexingModels").pipe(
                tap((data: any) => {
                    this.indexingModels = data.indexingModels;
                    if (this.indexingModels.length > 0) {
                        this.currentIndexingModel = this.indexingModels.filter(model => model.default === true)[0];
                        if (this.currentIndexingModel === undefined) {
                            this.currentIndexingModel = this.indexingModels[0];
                            this.notify.error(this.lang.noDefaultIndexingModel);
                        }
                    }

                    if (this.appService.getViewMode()) {
                        setTimeout(() => {
                            this.sidenavLeft.open();
                        }, 400);
                    }
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
            this.http.get("../../rest/indexing/" + this.currentGroupId + "/actions").pipe(
                map((data: any) => {
                    data.actions = data.actions.map((action: any, index: number) => {
                        return {
                            id: action.id,
                            label: action.label_action,
                            component: action.component,
                            default: index === 0 ? true : false
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
        },
            (err: any) => {
                this.notify.handleErrors(err);
            });
    }

    onSubmit() {
        if (this.indexingForm.isValidForm()) {
            alert(this.selectedAction.component + '() déclenchée');
            console.log(this.indexingForm.getDatas());
            console.log(this.appDocumentViewer.getFile());
        } else {
            alert('Veuillez corriger les erreurs.');
        }

    }

    loadIndexingModel(indexingModel: any) {
        this.currentIndexingModel = indexingModel;
        this.indexingForm.loadForm(indexingModel.id);
    }

    selectAction(action: any) {
        this.selectedAction = action;
    }

    savePrivateIndexingModel() {
        let fields = JSON.parse(JSON.stringify(this.indexingForm.getDatas()));
        fields.forEach((element: any, key: any) => {
            delete fields[key].event;
            delete fields[key].label;
            delete fields[key].system;
            delete fields[key].type;
            delete fields[key].values;
        });

        const privateIndexingModel = {
            category: this.indexingForm.getCategory(),
            label: '',
            owner: this.headerService.user.id,
            private: true,
            fields: fields,
            master: this.currentIndexingModel.master !== null ? this.currentIndexingModel.master : this.currentIndexingModel.id
        }

        const masterIndexingModel = this.indexingModels.filter((indexingModel) => indexingModel.id === privateIndexingModel.master)[0];
        this.dialogRef = this.dialog.open(AddPrivateIndexingModelModalComponent, { autoFocus: true, disableClose: true, data: { indexingModel: privateIndexingModel, masterIndexingModel : masterIndexingModel } });

        this.dialogRef.afterClosed().pipe(
            filter((data: any) => data !== undefined),
            tap((data) => {
                this.indexingModels.push(data.indexingModel);
                this.currentIndexingModel = this.indexingModels.filter(indexingModel => indexingModel.id === data.indexingModel.id)[0];
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    deletePrivateIndexingModel(id: number, index: number) {
        this.dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete, msg: this.lang.confirmAction } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.delete(`../../rest/indexingModels/${id}`)),
            tap(() => {
                this.indexingModels.splice(index, 1);
                this.notify.success(this.lang.indexingModelDeleted);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

}