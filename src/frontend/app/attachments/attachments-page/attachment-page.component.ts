import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { catchError, tap, finalize, exhaustMap, filter } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../../notification.service';
import { MatDialog, MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { AppService } from '../../../service/app.service';
import { SortPipe } from '../../../plugins/sorting.pipe';
import { FormControl, Validators, FormGroup } from '@angular/forms';
import { DocumentViewerComponent } from '../../viewer/document-viewer.component';
import { PrivilegeService } from '../../../service/privileges.service';
import { HeaderService } from '../../../service/header.service';
import { ConfirmComponent } from '../../../plugins/modal/confirm.component';

@Component({
    selector: 'app-attachment-page',
    templateUrl: "attachment-page.component.html",
    styleUrls: [
        'attachment-page.component.scss',
        '../../indexation/indexing-form/indexing-form.component.scss'
    ],
    providers: [AppService, SortPipe],
})

export class AttachmentPageComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = true;
    sendingData: boolean = false;

    attachmentsTypes: any[] = [];
    attachment: any;

    versions: any[] = [];
    hidePanel: boolean = false;
    newVersion: boolean = false;

    attachFormGroup: FormGroup = null;

    editMode: boolean = false;

    @ViewChild('appAttachmentViewer', { static: false }) appAttachmentViewer: DocumentViewerComponent;

    constructor(
        public http: HttpClient,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialog: MatDialog,
        public dialogRef: MatDialogRef<AttachmentPageComponent>,
        public appService: AppService,
        private notify: NotificationService,
        private sortPipe: SortPipe,
        private headerService: HeaderService,
        private privilegeService: PrivilegeService) {
    }

    ngOnInit(): void {
        this.hidePanel = this.data.hidePanel !== undefined ? this.data.hidePanel : false;

        this.http.get('../../rest/attachmentsTypes').pipe(
            tap((data: any) => {
                Object.keys(data.attachmentsTypes).forEach(templateType => {
                    if (data.attachmentsTypes[templateType].show) {
                        this.attachmentsTypes.push({
                            id: templateType,
                            ...data.attachmentsTypes[templateType]
                        });
                    }
                });

            }),
            exhaustMap(() => this.http.get("../../rest/attachments/" + this.data.resId)),
            tap((data: any) => {
                //this.attachment = data;

                if ((this.privilegeService.hasCurrentUserPrivilege('manage_attachments') || this.headerService.user.id === data.typist) && data.status !== 'SIGN') {
                    this.editMode = true;
                }

                this.attachment = {
                    typist: new FormControl({ value: data.typist, disabled: true }, [Validators.required]),
                    typistLabel: new FormControl({ value: data.typistLabel, disabled: true }, [Validators.required]),
                    creationDate: new FormControl({ value: data.creationDate, disabled: true }, [Validators.required]),
                    modificationDate: new FormControl({ value: data.modificationDate, disabled: true }),
                    modifiedBy: new FormControl({ value: data.modifiedBy, disabled: true }),
                    signatory: new FormControl({ value: data.signatory, disabled: true }),
                    signDate: new FormControl({ value: data.signDate, disabled: true }),
                    resId: new FormControl({ value: this.data.resId, disabled: true }, [Validators.required]),
                    chrono: new FormControl({ value: data.chrono, disabled: true }),
                    originId: new FormControl({ value: data.originId, disabled: true }),
                    resIdMaster: new FormControl({ value: data.resIdMaster, disabled: true }, [Validators.required]),
                    status: new FormControl({ value: data.status, disabled: true }, [Validators.required]),
                    relation: new FormControl({ value: data.relation, disabled: true }, [Validators.required]),
                    title: new FormControl({ value: data.title, disabled: !this.editMode }, [Validators.required]),
                    contact: new FormControl({ value: null, disabled: !this.editMode }),
                    type: new FormControl({ value: data.type, disabled: !this.editMode }, [Validators.required]),
                    validationDate: new FormControl({ value: data.validationDate !== null ? new Date(data.validationDate) : null, disabled: !this.editMode }),
                    signedResponse: new FormControl({ value: data.signedResponse, disabled: false }),
                    encodedFile: new FormControl({ value: '_CURRENT_FILE', disabled: !this.editMode }, [Validators.required])
                };

                this.versions = data.versions;

                this.attachFormGroup = new FormGroup(this.attachment);

                this.loading = false;
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                this.dialogRef.close('');
                return of(false);
            })
        ).subscribe();
    }

    onSubmit() {
        this.getAttachmentValues();
    }

    createNewVersion() {
        this.sendingData = true;
        this.http.post(`../../rest/attachments`, this.getAttachmentValues(true)).pipe(
            tap((data: any) => {
                this.notify.success(this.lang.newVersionAdded);
                this.dialogRef.close('success');
            }),
            finalize(() => this.sendingData = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    updateAttachment() {
        this.sendingData = true;
        this.http.put(`../../rest/attachments/${this.attachment.resId.value}`, this.getAttachmentValues()).pipe(
            tap((data: any) => {
                this.notify.success(this.lang.attachmentUpdated);
                this.dialogRef.close('success');
            }),
            finalize(() => this.sendingData = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    enableForm(state: boolean) {
        Object.keys(this.attachment).forEach(element => {
            if (['status', 'typistLabel', 'creationDate', 'relation', 'modificationDate', 'modifiedBy'].indexOf(element) === -1) {

                if (state) {
                    this.attachment[element].enable();
                } else {
                    this.attachment[element].disable();
                }
            }

        });
    }

    getAttachmentValues(newAttachment: boolean = false) {
        let attachmentValues = {};
        Object.keys(this.attachment).forEach(element => {
            if (this.attachment[element] !== undefined && (this.attachment[element].value !== null && this.attachment[element].value !== undefined)) {
                if (element === 'validationDate') {
                    let day = this.attachment[element].value.getDate();
                    let month = this.attachment[element].value.getMonth() + 1;
                    let year = this.attachment[element].value.getFullYear();
                    attachmentValues[element] = ('00' + day).slice(-2) + '-' + ('00' + month).slice(-2) + '-' + year + ' 23:59:59';
                } else {
                    attachmentValues[element] = this.attachment[element].value;
                }
                if (element === 'encodedFile') {
                    if (this.attachment[element].value === '_CURRENT_FILE') {
                        attachmentValues['encodedFile'] = null;
                    }
                    attachmentValues['format'] = this.appAttachmentViewer.getFile().format;
                }
            }
        });

        if (newAttachment) {
            attachmentValues['originId'] = this.attachment['originId'].value !== null ? this.attachment['originId'].value : attachmentValues['resId'];

            attachmentValues['relation'] = this.attachment['relation'].value + 1;
            delete attachmentValues['resId'];
        }

        return attachmentValues;
    }

    setDatasViewer(ev: any) {
        let datas: any = {};
        Object.keys(this.attachment).forEach(element => {
            if (['title', 'validationDate', 'effectiveDate'].indexOf(element) > -1) {
                datas['attachment_' + element] = this.attachment[element].value;
            }
        });
        if (ev === 'setData') {
            this.appAttachmentViewer.setDatas(datas);
        } else if (ev === 'cleanFile') {
            this.attachment['encodedFile'].setValue(null);
        } else {
            datas['resId'] = this.attachment['resIdMaster'].value;
            this.attachment.encodedFile.setValue(this.appAttachmentViewer.getFile().content);
            this.appAttachmentViewer.setDatas(datas);
            this.setNewVersion();
        }
    }

    getAttachType(attachType: any) {
        this.appAttachmentViewer.loadTemplatesByResId(this.attachment['resIdMaster'].value, attachType);
    }

    setNewVersion() {
        if (!this.newVersion) {
            const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.createNewVersion, msg: this.lang.confirmAction } });

            dialogRef.afterClosed().pipe(
                filter((data: string) => data === 'ok'),
                tap(() => {
                    this.newVersion = true;
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }

    }

    deleteSignedVersion() {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete, msg: this.lang.confirmAction } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.put(`../../rest/signatureBook/${this.attachment['resId'].value}/unsign`, {})),
            tap(() => {
                this.attachment.status.setValue('A_TRA');
                this.attachment.signedResponse.setValue(null);
                if (this.privilegeService.hasCurrentUserPrivilege('manage_attachments') || this.headerService.user.id === this.attachment['typist'].value) {
                    this.editMode = true;
                    this.enableForm(this.editMode);
                }
                this.notify.success(this.lang.signedVersionDeleted);
                this.dialogRef.close('success');
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    isEmptyField(field: any) {

        if (field.value === null) {
            return true;

        } else if (Array.isArray(field.value)) {
            if (field.value.length > 0) {
                return false;
            } else {
                return true;
            }
        } else if (String(field.value) !== '') {
            return false;
        } else {
            return true;
        }
    }
}