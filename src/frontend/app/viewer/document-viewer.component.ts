import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpEventType, HttpHeaders, HttpParams } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { HeaderService } from '../../service/header.service';
import { AppService } from '../../service/app.service';
import { tap, catchError, finalize } from 'rxjs/operators';
import { of } from 'rxjs';


@Component({
    selector: 'app-document-viewer',
    templateUrl: "document-viewer.component.html",
    styleUrls: [
        'document-viewer.component.scss',
    ],
    providers: [NotificationService, AppService]
})

export class DocumentViewerComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = false;

    file: any = {
        name: '',
        type: '',
        content: '',
        src: null
    };

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        private headerService: HeaderService,
        public appService: AppService,
    ) {
        (<any>window).pdfWorkerSrc = '../../node_modules/pdfjs-dist/build/pdf.worker.min.js';
    }

    ngOnInit() {

    }

    uploadTrigger(fileInput: any) {
        console.log("upload");
        this.loading = true;
        if (fileInput.target.files && fileInput.target.files[0]) {
            var reader = new FileReader();

            this.file.name = fileInput.target.files[0].name;
            this.file.type = fileInput.target.files[0].type;

            console.log(this.file.name);

            //reader.readAsDataURL(fileInput.target.files[0]);
            reader.readAsArrayBuffer(fileInput.target.files[0]);

            reader.onload = (value: any) => {
                this.file.content = this.getBase64Document(value.target.result);

                if (this.file.type !== 'application/pdf') {
                    this.convertDocument(this.file);
                } else {
                    this.file.src = value.target.result;
                    this.loading = false
                }

                //this.file.content = value.target.result.toString().replace('data:' + this.file.type + ';base64,', '');

                //console.log(this.file.content);
                //this.file.src = value.target.result;
                //window['angularUserAdministrationComponent'].componentAfterUpload(value.target.result);
                //this.submitSignature();
            };
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
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    onError(error: any) {
        console.log(error);
    }

    cleanFile() {
        this.file = {
            name: '',
            type: '',
            content: '',
            src: null
        };
    }

}