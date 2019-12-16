import {
    Component,
    OnInit,
    AfterViewInit,
    Input,
    NgZone,
    EventEmitter,
    Output,
    HostListener
} from '@angular/core';
import './onlyoffice-api.js';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Subject, Observable, of } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';

declare var DocsAPI: any;

@Component({
    selector: 'onlyoffice-viewer',
    template: `<button mat-mini-fab color="warn" style="position: absolute;right: 4px;top: 22px;" (click)="quit()">
    <mat-icon class="fa fa-times" style="height:auto;"></mat-icon>
  </button><div id="placeholder"></div>`,
})
export class EcplOnlyofficeViewerComponent implements OnInit, AfterViewInit {

    @Input() editMode: boolean = false;
    @Input() file: any = {};
    @Input() params: any = {};

    @Output() triggerAfterUpdatedDoc = new EventEmitter<string>();
    @Output() triggerCloseEditor = new EventEmitter<string>();

    editorConfig: any;
    docEditor: any;
    key: string = '';
    documentLoaded: boolean = false;
    canUpdateDocument: boolean = false;
    isSaving: boolean = false;

    tmpFilename: string = '';

    private eventAction = new Subject<any>();


    @HostListener('window:message', ['$event'])
    onMessage(e: any) {
        console.log(e);
        const response = JSON.parse(e.data);

        // EVENT TO CONSTANTLY UPDATE CURRENT DOCUMENT
        if (response.event === 'onDownloadAs') {
            this.getEncodedDocument(response.data);
            // EVENT TO AVOID INFINITE LOOP WITH MODIFIED EVENT + UPDATE DOC
        }
    }
    constructor(public http: HttpClient) { }

    quit() {
        this.docEditor.destroyEditor();
        this.triggerCloseEditor.emit();
    }

    getDocument() {
        this.isSaving = true;
        this.docEditor.downloadAs();
    }

    getEncodedDocument(data: any) {
        this.http.get('../../rest/onlyOffice/encodedFile', { params: { url: data } }).pipe(
            tap((data: any) => {
                //console.log(data.encodedFile);
                this.file.content = data.encodedFile;
                this.isSaving = false;
                this.triggerAfterUpdatedDoc.emit();
                this.eventAction.next(this.file);
                this.eventAction.complete();
            })
        ).subscribe();
    }

    getEditorMode(extension: string) {
        if (['csv', 'fods', 'ods', 'ots', 'xls', 'xlsm', 'xlsx', 'xlt', 'xltm', 'xltx'].indexOf(extension) > -1) {
            return 'spreadsheet';
        } else if (['fodp', 'odp', 'otp', 'pot', 'potm', 'potx', 'pps', 'ppsm', 'ppsx', 'ppt', 'pptm', 'pptx'].indexOf(extension) > -1) {
            return 'presentation';
        } else {
            return 'text';
        }
    }


    ngOnInit() {
        this.key = this.generateUniqueId();
        this.http.post(`../../${this.params.docUrl}`, { objectId: this.params.objectId, objectType: this.params.objectType, onlyOfficeKey: this.key, data : this.params.dataToMerge }).pipe(
            tap((data: any) => {
                this.tmpFilename = data.filename;

                this.file = {
                    name: this.key,
                    format: data.filename.split('.').pop(),
                    type: null,
                    contentMode: 'base64',
                    content: null,
                    src: null
                };

                this.initOfficeEditor();
            })
        ).subscribe();
    }

    generateUniqueId(length: number = 5) {
        var result = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }

    ngAfterViewInit() {

    }

    initOfficeEditor() {
        this.editorConfig = {
            documentType: this.getEditorMode(this.file.format),
            document: {
                fileType: this.file.format,
                key: this.key,
                title: 'Edition',
                url: `http://cchaplin:maarch@192.168.1.20/maarch_courrier_develop/${this.params.docUrl}?filename=${this.tmpFilename}`,
                permissions: {
                    comment: false,
                    download: true,
                    edit: this.editMode,
                    print: true,
                    review: false
                }
            },
            editorConfig: {
                callbackUrl: 'http://cchaplin:maarch@192.168.1.20/maarch_courrier_develop/rest/test',
                lang: 'fr',
                region: 'fr-FR',
                mode: 'edit',
                customization: {
                    chat: false,
                    comments: false,
                    compactToolbar: false,
                    feedback: false,
                    forcesave: false,
                    goback: false,
                    hideRightMenu: true,
                    showReviewChanges: false,
                    zoom: -2,
                },
                user: {
                    id: "1",
                    name: "Bernard BLIER"
                },
                "plugins": {  
               },
            },
        };
        this.docEditor = new DocsAPI.DocEditor('placeholder', this.editorConfig);
    }

    isLocked() {
        if (this.isSaving) {
            return true;
        } else {
            return false;
        }
    }

    getFile() {
        // return this.file;
        this.getDocument();
        return this.eventAction.asObservable();
    }

    ngOnDestroy() {
        this.eventAction.complete();
    }
}
