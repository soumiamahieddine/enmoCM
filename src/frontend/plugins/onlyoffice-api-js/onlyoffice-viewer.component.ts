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
import { catchError, tap, filter, exhaustMap, first, finalize } from 'rxjs/operators';
import { LANG } from '../../app/translate.component';
import { ConfirmComponent } from '../modal/confirm.component';
import { MatDialogRef, MatDialog } from '@angular/material';
import { NotificationService } from '../../app/notification.service';

declare var DocsAPI: any;

@Component({
    selector: 'onlyoffice-viewer',
    template: `<div *ngIf="loading" style="display:block;padding: 10px;">{{lang.checkOnlyofficeServer}}...</div><button mat-mini-fab color="warn" [title]="lang.closeEditor" style="position: absolute;right: 6px;top: 12px;" (click)="quit()"><mat-icon class="fa fa-times" style="height:auto;"></mat-icon></button><div id="placeholder"></div>`
})
export class EcplOnlyofficeViewerComponent implements OnInit, AfterViewInit {

    lang: any = LANG;

    loading: boolean = true;

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

    appUrl: string = '';
    onlyfficeUrl: string = '';

    private eventAction = new Subject<any>();
    dialogRef: MatDialogRef<any>;


    @HostListener('window:message', ['$event'])
    onMessage(e: any) {
        //console.log(e);
        const response = JSON.parse(e.data);

        // EVENT TO CONSTANTLY UPDATE CURRENT DOCUMENT
        if (response.event === 'onDownloadAs') {
            this.getEncodedDocument(response.data);
        }
    }
    constructor(public http: HttpClient, public dialog: MatDialog, private notify: NotificationService) { }

    quit() {
        this.dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.close, msg: this.lang.confirmCloseEditor } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            tap(() => {
                this.docEditor.destroyEditor();
                this.triggerCloseEditor.emit();
            })
        ).subscribe();
    }

    getDocument() {
        this.isSaving = true;
        this.docEditor.downloadAs();
    }

    getEncodedDocument(data: any) {
        this.http.get('../../rest/onlyOffice/encodedFile', { params: { url: data } }).pipe(
            tap((data: any) => {
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


    async ngOnInit() {
        this.key = this.generateUniqueId();

        await this.getServerConfiguration();

        await this.checkServerStatus();

        await this.getMergedFileTemplate();

        this.initOfficeEditor();

        this.loading = false;
    }

    getServerConfiguration() {
        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/onlyOffice/configuration`).pipe(
                tap((data: any) => {
                    if (data.enabled) {
                        const protocol = data.serverSsl ? 'https://': 'http://';
                        const port = data.serverPort ? `:${data.serverPort}`: ':80';
                        this.onlyfficeUrl = `${protocol}${data.serverUri}${port}`;
                        this.appUrl = data.coreUrl;
                        resolve(true);
                    } else {
                        this.triggerCloseEditor.emit();
                    }
                }),
                catchError((err) => {
                    this.notify.handleErrors(err);
                    this.triggerCloseEditor.emit();
                    return of(false);
                }),
            ).subscribe();
        }); 
    }


    checkServerStatus() {
        return new Promise((resolve, reject) => {
            const regex = /127\.0\.0\.1/g;
            const regex2 = /localhost/g;
            if (this.appUrl.match(regex) !== null || this.appUrl.match(regex2) !== null) {
                this.notify.error(`${this.lang.errorOnlyoffice1}`);
                this.triggerCloseEditor.emit();
            } else {
                this.http.get(`../../rest/onlyOffice/available`).pipe(
                    tap((data: any) => {
                        if (data.isAvailable) {
                            resolve(true);
                        } else {
                            this.notify.error(`${this.lang.errorOnlyoffice2} ${this.onlyfficeUrl}`);
                            this.triggerCloseEditor.emit();
                        }
                    }),
                    catchError((err) => {
                        this.notify.error(`${this.lang[err.error.lang]}`);
                        this.triggerCloseEditor.emit();
                        return of(false);
                    }),
                ).subscribe();
            }
        });
    }

    getMergedFileTemplate() {
        return new Promise((resolve, reject) => {
            this.http.post(`../../${this.params.docUrl}`, { objectId: this.params.objectId, objectType: this.params.objectType, onlyOfficeKey: this.key, data: this.params.dataToMerge }).pipe(
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
                    resolve(true);
                }),
                catchError((err) => {
                    this.notify.handleErrors(err);
                    this.triggerCloseEditor.emit();
                    return of(false);
                }),
            ).subscribe();
        });
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
                url: `${this.appUrl}${this.params.docUrl}?filename=${this.tmpFilename}`,
                permissions: {
                    comment: false,
                    download: true,
                    edit: this.editMode,
                    print: true,
                    review: false
                }
            },
            editorConfig: {
                callbackUrl: `${this.appUrl}rest/onlyOfficeCallback`,
                lang: this.lang.language,
                region: this.lang.langISO,
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
                    name: " "
                },
            },
        };
        this.docEditor = new DocsAPI.DocEditor('placeholder', this.editorConfig, this.onlyfficeUrl);
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
