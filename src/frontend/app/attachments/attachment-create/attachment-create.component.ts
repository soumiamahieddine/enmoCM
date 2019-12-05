import { Component, OnInit, Input, EventEmitter, Output, Inject, ViewChildren, QueryList, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { catchError, tap, finalize, exhaustMap, filter } from 'rxjs/operators';
import { of, forkJoin } from 'rxjs';
import { NotificationService } from '../../notification.service';
import { MatDialog, MAT_DIALOG_DATA, MatDialogRef, MatTabGroup } from '@angular/material';
import { AppService } from '../../../service/app.service';
import { DocumentViewerComponent } from '../../viewer/document-viewer.component';
import { SortPipe } from '../../../plugins/sorting.pipe';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { ConfirmComponent } from '../../../plugins/modal/confirm.component';

@Component({
    templateUrl: "attachment-create.component.html",
    styleUrls: [
        'attachment-create.component.scss',
        '../../indexation/indexing-form/indexing-form.component.scss'
    ],
    providers: [AppService, SortPipe],
})

export class AttachmentCreateComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = true;

    sendingData: boolean = false;

    attachmentsTypes: any[] = [];

    creationMode: boolean = true;

    attachFormGroup: FormGroup[] = [];

    attachments: any[] = [];

    now: Date = new Date();

    indexTab: number = 0;

    @Input('resId') resId: number = null;


    @ViewChildren('appDocumentViewer') appDocumentViewer: QueryList<DocumentViewerComponent>;

    constructor(
        public http: HttpClient,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<AttachmentCreateComponent>,
        public appService: AppService,
        private notify: NotificationService,
        private sortPipe: SortPipe,
        public dialog: MatDialog) {
    }

    ngOnInit(): void {
        this.loadAttachmentTypes();
    }

    loadAttachmentTypes() {
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
                this.attachmentsTypes = this.sortPipe.transform(this.attachmentsTypes, 'label');      
            }),
            exhaustMap(() => this.http.get(`../../rest/resources/${this.data.resIdMaster}?light=true`)),
            tap((data: any) => {
                this.attachments.push({
                    title: new FormControl({ value: data.subject, disabled: false }, [Validators.required]),
                    contact: new FormControl({ value: '', disabled: false }),
                    type: new FormControl({ value: '', disabled: false }, [Validators.required]),
                    validationDate: new FormControl({ value: '', disabled: false }),
                    encodedFile: new FormControl({ value: '', disabled: false }, [Validators.required])
                });

                this.attachFormGroup.push(new FormGroup(this.attachments[0]));
            }),
            finalize(() => this.loading = false)
        ).subscribe();
    }

    selectAttachType(attachment: any, type: any) {
        attachment.type = type.id;
    }

    formatAttachments() {
        let formattedAttachments: any[] = [];
        this.attachments.forEach((element, index: number) => {
            formattedAttachments.push({
                resIdMaster: this.data.resIdMaster,
                type: element.type.value,
                title: element.title.value,
                validationDate: element.validationDate.value !== '' ? element.validationDate.value : null,
                encodedFile: this.appDocumentViewer.toArray()[index].getFile().content,
                format: this.appDocumentViewer.toArray()[index].getFile().format
            });
        });

        return formattedAttachments;
    }

    onSubmit() {
        if (this.isValid()) {
            this.sendingData = true;
            const attach = this.formatAttachments();
            let arrayRoutes: any = [];
    
            this.attachments.forEach((element, index: number) => {
                arrayRoutes.push(this.http.post('../../rest/attachments', attach[index]));
            });
    
            forkJoin(arrayRoutes).pipe(
                tap(() => {
                    this.notify.success(this.lang.attachmentAdded);
                    this.dialogRef.close('success');
                }),
                finalize(() => this.sendingData = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.notify.error(this.lang.mustCompleteAllAttachments);
        }
        
    }

    isValid() {
        let state = true;
        this.attachFormGroup.forEach(formgroup => {
            if (formgroup.status === 'INVALID') {
                state = false;
            }
            Object.keys(formgroup.controls).forEach(key => {
                formgroup.controls[key].markAsTouched();
            });
        });
        return state;
    }

    isPjValid(index: number) {
        let state = true;
        if (this.attachFormGroup[index].status === 'INVALID') {
            state = false;
        }
        return state;
    }

    isDocLoading() {
        let state = false;
        this.appDocumentViewer.toArray().forEach((app, index: number) => {
            if (app.isEditingTemplate()) {
                state = true;
            }
        });
        return state;
    }

    setDatasViewer(i: number) {
        let datas: any = {};
        Object.keys(this.attachments[i]).forEach(element => {
            if (['title', 'validationDate'].indexOf(element) > -1) {
                datas['attachment_' + element] = this.attachments[i][element].value;
            }
        });
        datas['resId'] = this.data.resIdMaster;
        this.attachments[i].encodedFile.setValue(this.appDocumentViewer.toArray()[i].getFile().content);
        this.appDocumentViewer.toArray()[i].setDatas(datas);
    }

    newPj() {
        this.attachments.push({
            title: new FormControl({ value: '', disabled: false }, [Validators.required]),
            contact: new FormControl({ value: null, disabled: false }),
            type: new FormControl({ value: '', disabled: false }, [Validators.required]),
            validationDate: new FormControl({ value: null, disabled: false }),
            encodedFile: new FormControl({ value: '', disabled: false }, [Validators.required])
        });

        this.attachFormGroup.push(new FormGroup(this.attachments[this.attachments.length - 1]));
        this.indexTab = this.attachments.length - 1;
    }

    removePj(i: number) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete+ ' : PJ n°'+ (i+1), msg: this.lang.confirmAction } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            tap(() => {
                this.indexTab = 0;
                this.attachments.splice(i, 1);
                this.attachFormGroup.splice(i, 1);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();

    }

    getAttachType(attachType: any, i: number) {
        this.appDocumentViewer.toArray()[i].loadTemplatesByResId(this.data.resIdMaster, attachType);
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