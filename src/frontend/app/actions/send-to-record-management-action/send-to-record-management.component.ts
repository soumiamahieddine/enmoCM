import { animate, state, style, transition, trigger } from '@angular/animations';
import { HttpClient } from '@angular/common/http';
import { Component, Inject, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatTableDataSource } from '@angular/material/table';
import { TranslateService } from '@ngx-translate/core';
import { FunctionsService } from '@service/functions.service';
import { NotificationService } from '@service/notification/notification.service';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';

@Component({
    selector: 'app-send-to-record-management',
    templateUrl: 'send-to-record-management.component.html',
    styleUrls: ['send-to-record-management.component.scss'],
    animations: [
        trigger('detailExpand', [
            state('collapsed', style({ height: '0px', minHeight: '0' })),
            state('expanded', style({ height: '*' })),
            transition('expanded <=> collapsed', animate('225ms cubic-bezier(0.4, 0.0, 0.2, 1)')),
        ]),
    ],
})
export class SendToRecordManagementComponent implements OnInit {

    loading: boolean = true;
    checking: boolean = false;

    resources: any[] = [];
    resourcesErrors: any[] = [];

    dataSource = new MatTableDataSource<any>(this.resources);

    noResourceToProcess: boolean = null;

    senderArchiveEntity: string = '';


    recipientArchiveEntities = [];
    entityArchiveRecipient: string = null;

    archivalAgreements = [];
    archivalAgreement: string = null;

    columnsToDisplay = ['chrono', 'subject', 'slipId', 'archiveId', 'retentionFinalDisposition', 'countArchives'];

    actionFormGroup: FormGroup;
    archives: any[] = [];
    folders: any = [];
    folder: string = null;
    linkedResources: any = [];

    @ViewChild(MatPaginator) paginator: MatPaginator;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        public dialogRef: MatDialogRef<SendToRecordManagementComponent>,
        private _formBuilder: FormBuilder,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public functions: FunctionsService
    ) {
        this.actionFormGroup = this._formBuilder.group({
            folder: [''],
            packageName: ['', Validators.required],
            slipId: [{ value: '', disabled: true }, Validators.required],
            slipDate: [new Date(), Validators.required],
            archivalAgreement: [{ value: '', disabled: false }, Validators.required],
            entityArchiveRecipient: [{ value: '', disabled: false }, Validators.required],
            entityLabelTransferEntity: [{ value: '', disabled: true }, Validators.required],
            producerTransferEntity: [{ value: '', disabled: true }, Validators.required],
            senderArchiveEntity: [{ value: '', disabled: true }, Validators.required],
            archiveId: [{ value: '', disabled: true }, Validators.required],
            archiveDescriptionLevel: [{ value: 'File', disabled: false }, Validators.required],
            doctype: [{ value: '', disabled: true }, Validators.required],
            entityRetentionRule: [{ value: '', disabled: true }, Validators.required],
            doctypeRetentionFinalDisposition: [{ value: '', disabled: true }, Validators.required]
        });
    }

    ngOnInit(): void {
        this.getData();
    }

    getData() {
        this.checking = false;
        // this.noResourceToProcess = false;
        this.http.post(`../rest/resourcesList/users/${this.data.userId}/groups/${this.data.groupId}/baskets/${this.data.basketId}/actions/${this.data.action.id}/checkSendToRecordManagement`, { resources: this.data.resIds }).pipe(
            tap((data: any) => {
                this.resourcesErrors = data.errors;

                Object.keys(data.success).forEach((resId: any, index: number) => {
                    if (Object.keys(data.success).length === 1) {
                        this.linkedResources = data.success[resId].additionalData.linkedResources;
                        this.folders = data.success[resId].additionalData.folders;
                    }
                    this.resources.push({
                        chrono: data.success[resId].data.metadata.alt_identifier,
                        subject: data.success[resId].data.metadata.subject,
                        slipId: data.success[resId].data.slipInfo.slipId,
                        archiveId: data.success[resId].data.slipInfo.archiveId,
                        retentionFinalDisposition: data.success[resId].data.doctype.retentionFinalDisposition,
                        archives: data.success[resId].archiveUnits,
                        folder: data.success[resId].additionalData.folders.length > 0 ? data.success[resId].additionalData.folders[0] : null,
                        countArchives : data.success[resId].archiveUnits.length
                    });
                });
                this.archivalAgreements = data.archivalAgreements;
                this.recipientArchiveEntities = data.recipientArchiveEntities;
                this.senderArchiveEntity = data.senderArchiveEntity;

                setTimeout(() => {
                    this.dataSource.paginator = this.paginator;
                    this.loading = false;
                }, 0);
            }),
            finalize(() => this.checking = false),
            catchError((err: any) => {
                console.log(err);
                
                if (!this.functions.empty(err.error.lang)) {
                    this.resourcesErrors.push(this.translate.instant('lang.' + err.error.lang));
                } else {
                    this.resourcesErrors.push(err.error.errors);
                }
                this.loading = false;
                return of(false);
            })
        ).subscribe();
    }

    onSubmit(mode: string) {
        this.loading = true;
        if (this.data.resIds.length > 0) {
            this.executeAction(mode);
        }
    }

    executeAction(mode: string) {
        const realResSelected: number[] = this.data.resIds;

        this.http.put(this.data.processActionRoute, { resources: realResSelected, data: this.formatData(mode) }).pipe(
            tap((data: any) => {
                if (mode === 'download' && !this.functions.empty(data.data.encodedFile)) {
                    const downloadLink = document.createElement('a');
                    downloadLink.href = `data:application/zip;base64,${data.data.encodedFile}`;
                    let today: any;
                    let dd: any;
                    let mm: any;
                    let yyyy: any;

                    today = new Date();
                    dd = today.getDate();
                    mm = today.getMonth() + 1;
                    yyyy = today.getFullYear();

                    if (dd < 10) {
                        dd = '0' + dd;
                    }
                    if (mm < 10) {
                        mm = '0' + mm;
                    }
                    today = dd + '-' + mm + '-' + yyyy;
                    downloadLink.setAttribute('download', 'seda_package_' + today + '.zip');
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    this.dialogRef.close('success');
                } else if (!data) {
                    this.dialogRef.close('success');
                }
                if (data && data.errors != null) {
                    this.notify.error(data.errors);
                }
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    formatData(mode: string) {
        const dataToSend = {
            'archivalAgreement': this.archivalAgreement,
            'entityArchiveRecipient': this.entityArchiveRecipient,
            'folder': this.resources.length === 1 ? this.resources[0].folder.id : null,
            'actionMode' : mode
        };
        return dataToSend;
    }

    archivalAgreementSelected(ev: any) {
        const archivalAgreement = this.archivalAgreements.filter((element: any) => element.id === ev.value);
        this.actionFormGroup.patchValue({ entityArchiveRecipient: archivalAgreement[0].archiveEntityRegNumber });
    }

    entityArchiveRecipientSelected(ev: any) {
        if (!this.functions.empty(this.actionFormGroup.get('archivalAgreement').value) && !this.functions.empty(ev.value)) {
            const archivalAgreement = this.archivalAgreements.filter((element: any) => element.id === this.actionFormGroup.get('archivalAgreement').value && element.archiveEntityRegNumber === ev.value);
            if (archivalAgreement.length === 0) {
                this.actionFormGroup.patchValue({ archivalAgreement: null });
            }
        }
    }

    getFolderLabel(folder: any) {
        if (this.resources.length === 1) {
            return this.folders.find((item: any) => item.id === folder);
        } else {
            return folder;
        }
    }

    isValid() {
        if (this.resources.length === 1) {
            return !this.functions.empty(this.archivalAgreement) && !this.functions.empty(this.entityArchiveRecipient) && !this.functions.empty(this.resources[0].folder);
        } else {
            return !this.functions.empty(this.archivalAgreement) && !this.functions.empty(this.entityArchiveRecipient);
        }
    }
}