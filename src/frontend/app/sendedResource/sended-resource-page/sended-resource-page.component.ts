import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { Component, OnInit, ViewChild, EventEmitter, ElementRef, Input, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { Observable, merge, Subject, of as observableOf, of } from 'rxjs';
import { MatPaginator, MatSort, MatDialog, MatTableDataSource, MAT_DIALOG_DATA, MatDialogRef, MatChipInputEvent, MatSelect } from '@angular/material';
import { takeUntil, startWith, switchMap, map, catchError, filter, exhaustMap, tap, debounceTime, distinctUntilChanged, finalize } from 'rxjs/operators';
import { FormControl } from '@angular/forms';
import { FunctionsService } from '../../../service/functions.service';
import { CdkDragDrop, transferArrayItem } from '@angular/cdk/drag-drop';

@Component({
    selector: 'app-sended-resource-page',
    templateUrl: "sended-resource-page.component.html",
    styleUrls: ['sended-resource-page.component.scss'],
})
export class SendedResourcePageComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = true;

    readonly separatorKeysCodes: number[] = [COMMA];

    availableEmailModels: any[] = [];
    availableSignEmailModels: any[] = [];

    mainDocList: any[] = [];
    notesList: any[] = [];
    attachmentsList: any[] = [];

    resourceData: any = null;
    availableSenders: any[] = [];
    currentSender: any = {};

    recipients: any[] = [];

    copies: any[] = [];

    invisibleCopies: any[] = [];

    fruits: any[] = [];

    recipientsInput: FormControl = new FormControl();
    tinymceInput: string = '';

    filteredEmails: Observable<string[]>;
    emailsList: any[] = [];

    currentSelected: any = null;

    showCopies: boolean = false;
    showInvisibleCopies: boolean = false;

    tinyMCEConfig = {
        language: this.lang.langISO.replace('-', '_'),
        language_url: `../../node_modules/tinymce-i18n/langs/${this.lang.langISO.replace('-', '_')}.js`,
        menubar: false,
        statusbar: false,
        plugins: [
            'autolink', 'autoresize'
        ],
        external_plugins: {
            'maarch_b64image': "../../src/frontend/plugins/tinymce/maarch_b64image/plugin.min.js"
        },
        toolbar_sticky: true,
        toolbar_drawer: 'floating',
        toolbar:
            'undo redo | fontselect fontsizeselect | bold italic underline strikethrough forecolor | maarch_b64image | \
          alignleft aligncenter alignright alignjustify \
          bullist numlist outdent indent | removeformat'
    }

    emailsubject: string = '';
    emailAttach: any = {
        document: {
            isLinked: false,
            original: false
        },
        notes: [],
        attachments: []
    };

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<SendedResourcePageComponent>,
        private functions: FunctionsService
    ) { }

    async ngOnInit(): Promise<void> {

        this.emailAttach.document.id = this.data.resId;

        this.loading = false;
        this.getAttachElements();
        this.getResourceData();
        this.initEmailModelsList();
        this.getUserEmails();
        this.initEmailsList();
        this.initSignEmailModelsList();

    }

    add(event: MatChipInputEvent, type: string): void {

        const input = event.input;
        const value = event.value;

        if ((value || '').trim()) {
            this[type].push(
                {
                    label: '',
                    email: value.trim()
                });
        }

        if (input) {
            input.value = '';
        }
    }

    addEmail(item: any, type: string) {
        this[type].splice(this[type].length - 1, 1);

        if (item.type === 'contactGroup') {
            this.http.get(`../../rest/contactsGroups/${item.id}`).pipe(
                map((data: any) => {
                    data = data.contactsGroup.contacts.filter((contact: any) => !this.functions.empty(contact.email)).map((contact: any) => {
                        return {
                            label: contact.contact,
                            email: contact.email
                        }
                    });
                    return data;
                }),
                tap((data: any) => {
                    this[type] = this[type].concat(data);
                }),
                catchError((err) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this[type].push({
                label: 'toto',
                email: item.email
            });
        }
    }

    mergeEmailTemplate(templateId: number) {
        this.currentSelected = '';

        this.http.post(`../../rest/templates/${templateId}/mergeEmail`, { data: { resId: this.data.resId } }).pipe(
            tap((data: any) => {

                var div = document.createElement('div');

                div.innerHTML = this.tinymceInput.trim();

                if (div.getElementsByClassName('signature').length > 0) {

                    const signatureContent = div.getElementsByClassName('signature')[0].innerHTML;

                    div.getElementsByClassName('signature')[0].remove();

                    this.tinymceInput = `${div.innerHTML}${data.mergedDocument}<div class="signature">${signatureContent}</div>`;

                } else {
                    this.tinymceInput += data.mergedDocument;
                }
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    mergeSignEmailTemplate(templateId: number) {
        this.currentSelected = '';

        this.http.get(`../../rest/currentUser/emailSignatures/${templateId}`).pipe(
            tap((data: any) => {
                var div = document.createElement('div');

                div.innerHTML = this.tinymceInput.trim();


                if (div.getElementsByClassName('signature').length > 0) {

                    div.getElementsByClassName('signature')[0].remove();

                    this.tinymceInput = `${div.innerHTML}<div class="signature">${data.emailSignature.content}</div>`;
                } else {
                    this.tinymceInput += `<div class="signature">${data.emailSignature.content}</div>`;
                }
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    remove(item: any, type: string): void {

        const index = this[type].indexOf(item);

        if (index >= 0) {
            this[type].splice(index, 1);
        }
    }

    getResourceData() {
        this.http.get(`../../rest/resources/${this.data.resId}?light=true`).pipe(
            tap((data: any) => {
                this.resourceData = data;
                this.emailsubject = `[${this.resourceData.chrono}] ${this.resourceData.subject}`;
                this.emailAttach.document.label = this.resourceData.subject;

                if (!this.functions.empty(this.resourceData.senders)) {
                    this.resourceData.senders.forEach((sender: any) => {
                        this.setSender(sender.id);
                    });
                }
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    setSender(id: number) {
        this.http.get(`../../rest/contacts/${id}`).pipe(
            tap((data: any) => {
                this.recipients.push(
                    {
                        email: data.email
                    }
                )
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    getUserEmails() {
        this.http.get('../../rest/users/21/availableEmails').pipe(
            tap((data: any) => {
                this.availableSenders = data.emails;
                this.currentSender = this.availableSenders[0];
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    getAttachElements() {

        this.mainDocList = [
            {
                id: 100,
                label: 'Réservation Bal',
                typeLabel: 'Document principal',
                pdfVersion: 131,
                creator: 'Bernard Blier',
                size: '40ko'
            }
        ];
        this.attachmentsList = [
            {
                id: 100,
                label: 'je suis une pj',
                typeLabel: 'Projet de réponse',
                pdfVersion: 131,
                creator: 'Bernard Blier',
                size: '40ko'
            },
            {
                id: 102,
                label: 'je suis une pj 2',
                typeLabel: 'Projet de réponse',
                pdfVersion: 131,
                creator: 'Bernard Blier',
                size: '40ko'
            }
        ];

        this.notesList = [
            {
                id: 100,
                label: 'Je suis une note',
                typeLabel: 'Note',
                pdfVersion: null,
                creator: 'Bernard Blier',
                size: null
            }
        ];
    }

    initEmailsList() {
        this.recipientsInput.valueChanges.pipe(
            debounceTime(300),

            filter(value => value.length > 2),
            tap(() => this.loading = true),
            switchMap(data => this.http.get('../../rest/autocomplete/correspondents', { params: { "search": data, "searchEmails": 'true' } })),
            tap((data: any) => {
                data = data.filter((contact: any) => !this.functions.empty(contact.email) || contact.type === 'contactGroup').map((contact: any) => {
                    let label = '';
                    if (contact.type === 'user') {
                        label = `${contact.firstname} ${contact.lastname} (${contact.email})`;
                    } else if (contact.type === 'contactGroup') {
                        label = `${contact.firstname} ${contact.lastname}`;
                    } else {
                        label = `${contact.lastname} (${contact.email})`;
                    }
                    return {
                        id: contact.id,
                        type: contact.type,
                        label: label,
                        email: contact.email
                    }
                });
                this.filteredEmails = of(data);
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }


    initEmailModelsList() {
        this.http.get(`../../rest/resources/${this.data.resId}/emailTemplates`).pipe(
            tap((data: any) => {
                this.availableEmailModels = data.templates;
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    initSignEmailModelsList() {
        this.http.get(`../../rest/currentUser/emailSignatures`).pipe(
            tap((data: any) => {
                this.availableSignEmailModels = data.emailSignatures;
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    resetAutocomplete() {
        this.filteredEmails = of([]);
    }

    onSubmit() {
        console.log(this.formatEmail());

        /*this.http.post(`../../rest/emails`, this.formatEmail()).pipe(
            tap(() => {
                this.notify.success("Email en cours d'envoi...")
                this.dialogRef.close('success');
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();*/
    }

    drop(event: CdkDragDrop<string[]>) {

        if (event.previousContainer !== event.container) {
            transferArrayItem(event.previousContainer.data,
                event.container.data,
                event.previousIndex,
                event.currentIndex);
        }
    }

    toggleAttachMail(item: any, type: string, mode: string) {
        if (type === 'maindocument') {
            if (this.emailAttach.document.isLinked === false) {
                this.emailAttach.document.isLinked = true;
                this.emailAttach.document.original = mode === 'pdf' ? false : true;
            }
        } else if (type === 'attachment') {
            if (this.emailAttach.attachments.filter((attach: any) => attach.id === item.id).length === 0) {
                this.emailAttach.attachments.push({
                    id: item.id,
                    label: item.label,
                    original: mode === 'pdf' ? false : true
                });
            }
        } else if (type === 'note') {
            if (this.emailAttach.notes.filter((noteId: any) => noteId === item.id).length === 0) {
                this.emailAttach.notes.push({
                    id: item.id,
                    label: item.label
                });
            }
        }
    }

    removeAttachMail(index: number, type: string) {
        console.log(index);
        console.log(type);
        
        if (type === 'document') {
            this.emailAttach.document.isLinked = true;
            this.emailAttach.document.original = false;
        } else if (type === 'attachments') {
            this.emailAttach.attachments.splice(index, 1);
        } else if (type === 'notes') {
            this.emailAttach.notes.splice(index, 1);
        }
    }

    formatEmail() {
        Object.keys(this.emailAttach).forEach(element => {
            if (this.functions.empty(this.emailAttach[element])) {
                delete this.emailAttach[element];
            }
        });
        const data = {
            sender: this.currentSender,
            recipients: this.recipients.map(recipient => recipient.email),
            cc: this.copies.map(copy => copy.email),
            cci: this.invisibleCopies.map((invCopy => invCopy.email)),
            object: this.emailsubject,
            body: this.tinymceInput,
            isHtml: true,
            status: 'WAITING'
        };

        return Object.assign({}, this.emailAttach, data);
    }
}