import { Component, OnInit, Input, EventEmitter, Output, Inject, ViewChildren, QueryList } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { catchError, tap, finalize, exhaustMap } from 'rxjs/operators';
import { of, forkJoin } from 'rxjs';
import { NotificationService } from '../../notification.service';
import { MatDialog, MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { AppService } from '../../../service/app.service';
import { DocumentViewerComponent } from '../../viewer/document-viewer.component';
import { SortPipe } from '../../../plugins/sorting.pipe';
import { FormControl, FormGroup, Validators } from '@angular/forms';

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

    @Input('resId') resId: number = null;


    @ViewChildren('appDocumentViewer') appDocumentViewer: QueryList<DocumentViewerComponent>;

    constructor(
        public http: HttpClient,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<AttachmentCreateComponent>,
        public appService: AppService,
        private notify: NotificationService,
        private sortPipe: SortPipe) {
    }

    ngOnInit(): void {
        this.loadAttachmentTypes();
    }

    loadMailresource(resIdMaster: number) {

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
                this.attachmentsTypes = this.sortPipe.transform(this.attachmentsTypes, 'label')

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
            finalize(() => this.sendingData),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    isValid() {
        let state = true;
        this.attachFormGroup.forEach(formgroup => {
            if (formgroup.status === 'INVALID') {
                state = false;
            }
        });
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

    setEncodedFile(i: number) {
        this.attachments[i].encodedFile.setValue(this.appDocumentViewer.toArray()[i].getFile().content);
    }

    newPj() {
        this.attachments.push({
            title: new FormControl({ value: '', disabled: false }, [Validators.required]),
            contact: new FormControl({ value: null, disabled: false }),
            type: new FormControl({ value: '', disabled: false }, [Validators.required]),
            validationDate: new FormControl({ value: null, disabled: false }),
            encodedFile: new FormControl({ value: '', disabled: false }, [Validators.required])
        });

        this.attachFormGroup.push(new FormGroup(this.attachments[this.attachments.length-1]));
    }

    removePj(i: number) {
        this.attachments.splice(i,1);
        this.attachFormGroup.splice(i,1);
    }

    getAttachType(attachType: any, i: number) {
        this.appDocumentViewer.toArray()[i].loadTemplatesByResId(this.data.resIdMaster, attachType);
    }
}