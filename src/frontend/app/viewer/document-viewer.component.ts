import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { HeaderService } from '../../service/header.service';
import { AppService } from '../../service/app.service';
import { tap, catchError, finalize, filter } from 'rxjs/operators';
import { of } from 'rxjs';
import { ConfirmComponent } from '../../plugins/modal/confirm.component';
import { MatDialogRef, MatDialog } from '@angular/material';
import { AlertComponent } from '../../plugins/modal/alert.component';
import { SortPipe } from '../../plugins/sorting.pipe';


@Component({
    selector: 'app-document-viewer',
    templateUrl: "document-viewer.component.html",
    styleUrls: [
        'document-viewer.component.scss',
    ],
    providers: [NotificationService, AppService, SortPipe]
})

export class DocumentViewerComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = false;
    noConvertedFound: boolean = false;

    file: any = {
        name: '',
        type: '',
        content: null,
        src: null
    };

    allowedExtensions: any[] = [];
    maxFileSize: number = 0;

    dialogRef: MatDialogRef<any>;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        private headerService: HeaderService,
        public appService: AppService,
        private dialog: MatDialog,
        private sortPipe: SortPipe
    ) {
        (<any>window).pdfWorkerSrc = '../../node_modules/pdfjs-dist/build/pdf.worker.min.js';
    }

    ngOnInit() {
        this.http.get('../../rest/indexing/fileInformations').pipe(
            tap((data: any) => {
                this.allowedExtensions = data.informations.allowedFiles.map((ext: any) => {
                    return {
                        extension: '.' + ext.extension.toLowerCase(),
                        mimeType: ext.mimeType,
                    }
                });
                this.allowedExtensions = this.sortPipe.transform(this.allowedExtensions, 'extension');

                this.maxFileSize = data.informations.maximumSize;
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    uploadTrigger(fileInput: any) {
        this.file = {
            name: '',
            type: '',
            content: null,
            src: null
        };
        this.noConvertedFound = false;
        this.loading = true;

        if (fileInput.target.files && fileInput.target.files[0] && this.checkFile(fileInput.target.files[0])) {
            var reader = new FileReader();

            this.file.name = fileInput.target.files[0].name;
            this.file.type = fileInput.target.files[0].type;

            reader.readAsArrayBuffer(fileInput.target.files[0]);

            reader.onload = (value: any) => {
                this.file.content = this.getBase64Document(value.target.result);

                if (this.file.type !== 'application/pdf') {
                    this.convertDocument(this.file);
                } else {
                    this.file.src = value.target.result;
                    this.loading = false;
                }
            };
        } else {
            this.loading = false;
        }
    }

    getBase64Document(buffer: ArrayBuffer) {
        let TYPED_ARRAY = new Uint8Array(buffer);
        const STRING_CHAR = TYPED_ARRAY.reduce((data, byte) => {
            return data + String.fromCharCode(byte);
        }, '');

        return btoa(STRING_CHAR);
    }

    base64ToArrayBuffer(base64: string) {
        var binary_string = window.atob(base64);
        var len = binary_string.length;
        var bytes = new Uint8Array(len);
        for (var i = 0; i < len; i++) {
            bytes[i] = binary_string.charCodeAt(i);
        }
        return bytes.buffer;
    }

    convertDocument(file: any) {
        this.http.post('../../rest/convertedFile', { name: file.name, base64: file.content }).pipe(
            tap((data: any) => {
                this.file.src = this.base64ToArrayBuffer(data.encodedResource);
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.noConvertedFound = true;
                //this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    onError(error: any) {
        console.log(error);
    }

    cleanFile() {
        this.dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete, msg: this.lang.confirmAction } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            tap(() => {
                this.file = {
                    name: '',
                    type: '',
                    content: null,
                    src: null
                };
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();

    }

    getFile() {
        return this.file;
    }

    dndUploadFile(event: any) {
        const fileInput = {
            target: {
                files: [
                    event[0]
                ]
            }
        }
        this.uploadTrigger(fileInput);
    }

    checkFile(file: any) {

        if (this.allowedExtensions.map(ext => ext.mimeType).indexOf(file.type) === -1) {
            this.dialog.open(AlertComponent, { autoFocus: false, disableClose: true, data: { title: 'Extension non autorisé !', msg: '<u>Extension autorisée</u> : <br/>' + this.allowedExtensions.map(ext => ext.extension).filter((elem: any, index: any, self: any) => index === self.indexOf(elem)).join(', ') } });
            return false;
        } else if (file.size > this.maxFileSize) {
            this.dialog.open(AlertComponent, { autoFocus: false, disableClose: true, data: { title: 'Taille maximale dépassée ! ', msg: 'Taille maximale : ' + this.maxFileSize } });
            return false;
        } else {
            return true;
        }
    }

}