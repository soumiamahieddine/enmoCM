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
    template: `<button (click)="quit()">close</button><div id="placeholder"></div>`,
})
export class EcplOnlyofficeViewerComponent implements OnInit, AfterViewInit {
    @Input() id: string;
    @Input() onlyofficeName: string;
    @Input() onlyofficeType: string;
    @Input() onlyofficeKey: string;
    @Input() resId: number;
    @Input() editMode: boolean = false;

    @Output() triggerEvent = new EventEmitter<string>();

    editorConfig: any;
    docEditor: any;
    showModalWindow: boolean = false;

    @HostListener('window:message',['$event'])
    onMessage(e: any) {
        console.log(e);
        const response = JSON.parse(e.data);
        
        if (response.event === 'onDownloadAs') {
            this.onDownloadAs(response.data);
        }
    }
    constructor(private zone: NgZone, public http: HttpClient) { }    

    quit() {
        this.docEditor.downloadAs();
        //this.docEditor.destroyEditor();
    }

    onDocumentStateChange(event: any) {
        if (event.data) {
            console.log('The document changed');
            console.log(event);
        } else {
            console.log('Changes are collected on document editing service');
        }
    }

    onDownloadAs(url: any) {
        const optionRequete = {
            headers: new HttpHeaders({ 
              'Access-Control-Allow-Origin':'*',
            })
          };
        this.http.get(url, optionRequete).pipe(
            tap((data: any) => {
                console.log(data);
            }),
            catchError((err: any) => {
                console.log(err)
                return of(false);
            })
        ).subscribe();
    }
    

    ngOnInit() { }

    ngAfterViewInit() {
        this.editorConfig = {
            documentType: 'text',
            document: {
                fileType: 'odt',
                key: 'toto',
                title: this.onlyofficeName,
                url: `http://10.2.95.76/rep_standard.odt`,
                permissions: {
                    comment: false,
                    download: true,
                    edit: this.editMode,
                    print: true,
                    review: false
                }
            },
            editorConfig: {
                callbackUrl: 'http://10.2.95.76/maarch_courrier_develop/rest/test',
                lang: 'fr-FR',
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
                    zoom: 100
                },
                user: {
                    id: "1",
                    name: "Bernard BLIER"
                },
            },
        };
        this.docEditor = new DocsAPI.DocEditor('placeholder', this.editorConfig);
    }
}
