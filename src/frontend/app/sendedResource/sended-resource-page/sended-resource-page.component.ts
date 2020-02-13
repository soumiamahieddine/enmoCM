import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { Component, OnInit, ViewChild, EventEmitter, ElementRef, Input, Inject, AfterViewInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { Observable, merge, Subject, of as observableOf, of } from 'rxjs';
import { MatPaginator, MatSort, MatDialog, MatTableDataSource, MAT_DIALOG_DATA, MatDialogRef, MatChipInputEvent, MatSelect } from '@angular/material';
import { takeUntil, startWith, switchMap, map, catchError, filter, exhaustMap, tap, debounceTime, distinctUntilChanged, finalize } from 'rxjs/operators';
import { FormControl } from '@angular/forms';
import { FunctionsService } from '../../../service/functions.service';
import { CdkDragDrop, transferArrayItem } from '@angular/cdk/drag-drop';
import { ContactService } from '../../../service/contact.service';
import { AppService } from '../../../service/app.service';
import { ConfirmComponent } from '../../../plugins/modal/confirm.component';

declare var tinymce: any;

@Component({
    selector: 'app-sended-resource-page',
    templateUrl: "sended-resource-page.component.html",
    styleUrls: ['sended-resource-page.component.scss'],
    providers: [ContactService, AppService],
})
export class SendedResourcePageComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = true;

    readonly separatorKeysCodes: number[] = [COMMA];

    availableEmailModels: any[] = [];
    availableSignEmailModels: any[] = [];

    resourceData: any = null;
    availableSenders: any[] = [];
    currentSender: any = {};

    recipients: any[] = [];

    copies: any[] = [];

    invisibleCopies: any[] = [];

    fruits: any[] = [];

    recipientsInput: FormControl = new FormControl();

    emailSignListForm = new FormControl();
    templateEmailListForm = new FormControl();

    tinymceInput: string = '';

    filteredEmails: Observable<string[]>;
    emailsList: any[] = [];

    currentSelected: any = null;

    showCopies: boolean = false;
    showInvisibleCopies: boolean = false;

    emailId: number = null;
    emailsubject: string = '';
    emailStatus: string = 'WAITING';
    currentEmailAttachTool: string = '';
    emailAttachTool: any = {
        document: {
            icon: 'fa fa-file',
            title: 'Attacher le document principal',
            list: []
        },
        notes: {
            icon: 'fas fa-pen-square',
            title: 'Attacher une annotations',
            list: []
        },
        attachments: {
            icon: 'fa fa-paperclip',
            title: 'Attacher une pièce jointe',
            list: []
        },
    };
    emailAttach: any = {};
    lastClicked: any = Date.now();

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialog: MatDialog,
        public dialogRef: MatDialogRef<SendedResourcePageComponent>,
        public functions: FunctionsService,
        private contactService: ContactService,
    ) { }

    async ngOnInit(): Promise<void> {
        Object.keys(this.emailAttachTool).forEach(element => {
            if (element === 'document') {
                this.emailAttach[element] = {
                    id: this.data.resId,
                    isLinked: false,
                    original: false
                };
            } else {
                this.emailAttach[element] = [];
            }
        });
        this.initEmailModelsList();
        this.initEmailsList();
        this.initSignEmailModelsList();

        await this.getAttachElements();
        await this.getResourceData();
        await this.getUserEmails();
        this.loading = false;
        this.initMce();
    }

    initMce() {
        tinymce.init({
            selector: "textarea#emailSignature",
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
        });
    }

    add(event: MatChipInputEvent, type: string): void {

        const input = event.input;
        const value = event.value;

        if ((value || '').trim()) {
            this[type].push(
                {
                    label: value.trim(),
                    email: value.trim()
                });
        }

        if (input) {
            input.value = '';
        }
    }

    closeModal(state: string = '') {
        tinymce.remove();
        this.dialogRef.close(state);
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
                label: item.label,
                email: item.email
            });
        }
    }

    mergeEmailTemplate(templateId: any) {

        this.templateEmailListForm.reset();

        this.http.post(`../../rest/templates/${templateId}/mergeEmail`, { data: { resId: this.data.resId } }).pipe(
            tap((data: any) => {

                var div = document.createElement('div');

                div.innerHTML = tinymce.get('emailSignature').getContent();

                if (div.getElementsByClassName('signature').length > 0) {

                    const signatureContent = div.getElementsByClassName('signature')[0].innerHTML;

                    div.getElementsByClassName('signature')[0].remove();

                    tinymce.get('emailSignature').setContent(`${div.innerHTML}${data.mergedDocument}<div class="signature">${signatureContent}</div>`);

                } else {
                    tinymce.get('emailSignature').setContent(`${tinymce.get('emailSignature').getContent()}${data.mergedDocument}`);
                }
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    mergeSignEmailTemplate(templateId: any) {

        this.emailSignListForm.reset();

        this.http.get(`../../rest/currentUser/emailSignatures/${templateId}`).pipe(
            tap((data: any) => {
                var div = document.createElement('div');

                div.innerHTML = tinymce.get('emailSignature').getContent();

                if (div.getElementsByClassName('signature').length > 0) {

                    div.getElementsByClassName('signature')[0].remove();

                    tinymce.get('emailSignature').setContent(`${div.innerHTML}<div class="signature">${data.emailSignature.content}</div>`);
                } else {
                    tinymce.get('emailSignature').setContent(`${tinymce.get('emailSignature').getContent()}<div class="signature">${data.emailSignature.content}</div>`);
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
        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/resources/${this.data.resId}?light=true`).pipe(
                tap((data: any) => {
                    this.resourceData = data;
                    this.emailsubject = `[${this.resourceData.chrono}] ${this.resourceData.subject}`;
                    this.emailAttach.document.chrono = this.resourceData.chrono;
                    this.emailAttach.document.label = this.resourceData.subject;

                    if (!this.functions.empty(this.resourceData.senders)) {
                        this.resourceData.senders.forEach((sender: any) => {
                            this.setSender(sender.id);
                        });
                    }
                    resolve(true);
                }),
                catchError((err) => {
                    this.notify.handleSoftErrors(err);
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });
    }

    setSender(id: number) {
        this.http.get(`../../rest/contacts/${id}`).pipe(
            tap((data: any) => {
                if (!this.functions.empty(data.email)) {
                    this.recipients.push(
                        {
                            label: this.contactService.formatContact(data),
                            email: data.email
                        }
                    )
                }
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    getUserEmails() {
        return new Promise((resolve, reject) => {
            this.http.get('../../rest/currentUser/availableEmails').pipe(
                tap((data: any) => {
                    this.availableSenders = data.emails;
                    this.currentSender = this.availableSenders[0];
                    resolve(true);
                }),
                catchError((err) => {
                    this.notify.handleSoftErrors(err);
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getAttachElements() {
        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/resources/${this.data.resId}/emailsInitialization`).pipe(
                tap((data: any) => {
                    Object.keys(data).forEach(element => {
                        if (element === 'resource') {
                            this.emailAttachTool.document.list = [data[element]];
                        } else {
                            this.emailAttachTool[element].list = data[element].map((item: any) => {
                                return {
                                    ...item,
                                    title: item.chrono !== undefined ? `${item.chrono} - ${item.label} (${item.typeLabel})` : `${item.label} (${item.typeLabel})`
                                }
                            });
                        }
                    });

                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });
    }

    initEmailsList() {
        this.recipientsInput.valueChanges.pipe(
            debounceTime(300),
            tap((value) => {
                if (value.length === 0) {
                    this.filteredEmails = of([]);
                }
            }),
            filter(value => value.length > 2),
            switchMap(data => this.http.get('../../rest/autocomplete/correspondents', { params: { "search": data, "searchEmails": 'true' } })),
            tap((data: any) => {
                data = data.filter((contact: any) => !this.functions.empty(contact.email) || contact.type === 'contactGroup').map((contact: any) => {
                    let label = '';
                    if (contact.type === 'user' || contact.type === 'contact') {
                        label = `${contact.firstname} ${contact.lastname}`;
                    } else if (contact.type === 'contactGroup') {
                        label = `${contact.firstname} ${contact.lastname}`;
                    } else {
                        label = `${contact.lastname}`;
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
        this.emailStatus = 'WAITING';
        if (this.emailId === null) {
            if (this.emailsubject === '') {
                const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.confirm, msg: 'Vous allez envoyer un courriel sans sujet, continuer ?' } });

                dialogRef.afterClosed().pipe(
                    filter((data: string) => data === 'ok'),
                    tap(() => {
                        this.createEmail(true);
                    })
                ).subscribe();
            } else {
                this.createEmail(true);
            }

        } else {
            this.updateEmail(true);
        }
    }

    createEmail(closeModal: boolean = true) {
        this.http.post(`../../rest/emails`, this.formatEmail()).pipe(
            tap((data: any) => {
                //this.emailId = data.id;

                if (this.emailStatus === 'DRAFT') {
                    this.notify.success("Brouillon enregitré");
                } else {
                    this.notify.success("Email en cours d'envoi...");
                }

                if (closeModal) {
                    this.closeModal('success');
                }

            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    updateEmail(closeModal: boolean = true) {
        this.http.put(`../../rest/emails/${this.emailId}`, this.formatEmail()).pipe(
            tap(() => {
                if (this.emailStatus === 'DRAFT') {
                    this.notify.success("Brouillon modifié");
                } else {
                    this.notify.success("Email en cours d'envoi...");
                }

                if (closeModal) {
                    this.closeModal('success');
                }
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    saveDraft() {
        this.emailStatus = 'DRAFT';
        if (this.emailId === null) {
            this.createEmail();
        } else {
            this.updateEmail();
        }

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
        if (type === 'document') {
            if (this.emailAttach.document.isLinked === false) {
                this.emailAttach.document.isLinked = true;
                this.emailAttach.document.original = mode === 'pdf' ? false : true;
            }
        } else {
            if (this.emailAttach[type].filter((attach: any) => attach.id === item.id).length === 0) {
                this.emailAttach[type].push({
                    ...item,
                    format: mode !== 'pdf' ? item.format : 'pdf',
                    original: mode === 'pdf' ? false : true
                });
            }
        }
    }

    removeAttachMail(index: number, type: string) {
        if (type === 'document') {
            this.emailAttach.document.isLinked = false;
            this.emailAttach.document.original = false;
        } else {
            this.emailAttach[type].splice(index, 1);
        }
    }

    formatEmail() {
        let objAttach: any = {}
        Object.keys(this.emailAttach).forEach(element => {
            if (!this.functions.empty(this.emailAttach[element])) {
                if (element === 'document') {
                    objAttach = {
                        id: this.emailAttach[element].id,
                        isLinked: this.emailAttach[element].isLinked,
                        original: this.emailAttach[element].original
                    }
                } else if (element === 'notes') {
                    objAttach[element] = this.emailAttach[element].map((item: any) => item.id)
                } else {
                    objAttach[element] = this.emailAttach[element].map((item: any) => {
                        return {
                            id: item.id,
                            original: item.original
                        }
                    })
                }
            }
        });

        let formatSender = this.currentSender;

        delete formatSender.label;

        const data = {
            document: objAttach,
            sender: formatSender,
            recipients: this.recipients.map(recipient => recipient.email),
            cc: this.showCopies ? this.copies.map(copy => copy.email) : [],
            cci: this.showInvisibleCopies ? this.invisibleCopies.map((invCopy => invCopy.email)) : [],
            object: this.emailsubject,
            body: tinymce.get('emailSignature').getContent(),
            isHtml: true,
            status: this.emailStatus
        };

        return data;
    }

    isSelectedAttachMail(item: any, type: string) {
        if (type === 'document') {
            return this.emailAttach.document.isLinked;
        } else {
            return this.emailAttach[type].filter((attach: any) => attach.id === item.id).length > 0;
        }
    }

    test() {
        if (Date.now() - this.lastClicked >= 5000) {
            this.lastClicked = Date.now();
            console.log('modifié');
        }
    }
}