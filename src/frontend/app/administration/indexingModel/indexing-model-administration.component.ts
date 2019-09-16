import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService } from '../../../service/header.service';
import { MatDialog } from '@angular/material/dialog';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../../service/app.service';
import { tap, catchError, finalize } from 'rxjs/operators';
import { of } from 'rxjs';
import { SortPipe } from '../../../plugins/sorting.pipe';
import { IndexingFormComponent } from '../../indexation/indexing-form/indexing-form.component';
import { ActivatedRoute, Router } from '@angular/router';

declare function $j(selector: any): any;

@Component({
    templateUrl: "indexing-model-administration.component.html",
    styleUrls: [
        'indexing-model-administration.component.scss',
        '../../indexation/indexing-form/indexing-form.component.scss'
    ],
    providers: [NotificationService, AppService, SortPipe]
})

export class IndexingModelAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;

    @ViewChild('indexingForm', { static: false }) indexingForm: IndexingFormComponent;

    lang: any = LANG;

    loading: boolean = true;

    indexingModel: any = {
        id: 0,
        label: '',
        default: false,
        owner: 0,
        private: false
    };

    indexingModelClone: any;

    indexingModelsCustomFields: any[] = [];

    creationMode: boolean = true;

    availableFields: any[] = [
        {
            identifier: 'priority',
            label: this.lang.priority,
            type: 'select',
            values: []
        },
        {
            identifier: 'confidential',
            label: this.lang.confidential,
            type: 'radio',
            values: ['yes', 'no']
        },
        {
            identifier: 'initiator',
            label: this.lang.initiator,
            type: 'select',
            values: []
        },
        {
            identifier: 'processLimitDate',
            label: this.lang.processLimitDate,
            type: 'date',
            values: []
        },
        {
            identifier: 'arrivalDate',
            label: this.lang.arrivalDate,
            type: 'date',
            values: []
        }
    ];

    availableCustomFields: any[] = []

    constructor(
        public http: HttpClient,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService,
        public dialog: MatDialog,
        private headerService: HeaderService,
        public appService: AppService,
    ) {

    }

    ngOnInit(): void {
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(this.sidenavRight);

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined") {
                this.headerService.setHeader(this.lang.indexingModelCreation);

                this.indexingModelClone = JSON.parse(JSON.stringify(this.indexingModel));
                this.creationMode = true;
                this.loading = false;

            } else {
                this.creationMode = false;

                this.http.get("../../rest/indexingModels/" + params['id']).pipe(
                    tap((data: any) => {
                        this.indexingModel = data.indexingModel;

                        this.headerService.setHeader(this.lang.indexingModelModification, this.indexingModel.label);

                        this.indexingModelClone = JSON.parse(JSON.stringify(this.indexingModel));

                    }),
                    finalize(() => this.loading = false),
                    catchError((err: any) => {
                        this.notify.handleErrors(err);
                        return of(false);
                    })
                ).subscribe();
            }
        });


    }

    onSubmit() {
        this.indexingModel.fields = this.indexingForm.getDatas();

        if (this.creationMode) {
            this.http.post("../../rest/indexingModels", this.indexingModel).pipe(
                tap((data: any) => {
                    this.indexingForm.setModification();
                    this.setModification();
                    this.router.navigate(['/administration/indexingModels']);
                    this.notify.success(this.lang.indexingModelAdded);
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.http.put("../../rest/indexingModels/" + this.indexingModel.id, this.indexingModel).pipe(
                tap((data: any) => {
                    this.indexingForm.setModification();
                    this.setModification();
                    this.router.navigate(['/administration/indexingModels']);
                    this.notify.success(this.lang.indexingModelUpdated);
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }

    }

    isModified() {
        let compare: string = '';
        let compareClone: string = '';

        compare = JSON.stringify(this.indexingModel);
        compareClone = JSON.stringify(this.indexingModelClone);

        if (compare !== compareClone) {
            return true;
        } else {
            return false;
        }
    }

    setModification() {
        this.indexingModelClone = JSON.parse(JSON.stringify(this.indexingModel));
    }
}