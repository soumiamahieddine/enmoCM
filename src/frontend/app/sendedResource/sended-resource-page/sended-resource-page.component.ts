import { COMMA } from '@angular/cdk/keycodes';
import { Component, OnInit, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { Observable, of } from 'rxjs';
import { MatDialog, MAT_DIALOG_DATA, MatDialogRef, MatChipInputEvent } from '@angular/material';
import { switchMap, map, catchError, filter, exhaustMap, tap, debounceTime } from 'rxjs/operators';
import { FormControl } from '@angular/forms';
import { FunctionsService } from '../../../service/functions.service';
import { CdkDragDrop, transferArrayItem } from '@angular/cdk/drag-drop';
import { ContactService } from '../../../service/contact.service';
import { AppService } from '../../../service/app.service';
import { ConfirmComponent } from '../../../plugins/modal/confirm.component';
import { PrivilegeService } from '../../../service/privileges.service';
import { HeaderService } from '../../../service/header.service';

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

    emailCreatorId: number = null;
    emailId: number = null;
    emailsubject: string = '';
    emailStatus: string = 'WAITING';
    emailContent: string = '';
    currentEmailAttachTool: string = '';
    emailAttachTool: any = {
        document: {
            icon: 'fa fa-file',
            title: this.lang.attachMainDocument,
            list: []
        },
        notes: {
            icon: 'fas fa-pen-square',
            title: this.lang.attachNote,
            list: []
        },
        attachments: {
            icon: 'fa fa-paperclip',
            title: this.lang.attachAttachment,
            list: []
        },
    };
    emailAttach: any = {};
    lastClicked: any = Date.now();

    canManage: boolean = false;
    pdfMode: boolean = false;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialog: MatDialog,
        public dialogRef: MatDialogRef<SendedResourcePageComponent>,
        public functions: FunctionsService,
        private contactService: ContactService,
        public privilegeService: PrivilegeService,
        public headerService: HeaderService
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

        if (this.data.emailId && this.data.emailType === 'email') {
            await this.getEmailData(this.data.emailId);
        } else if (this.data.emailId && this.data.emailType === 'acknowledgementReceipt') {
            await this.getAcknowledgementReceiptData(this.data.emailId);
        }
        this.loading = false;
        setTimeout(() => {
            this.initMce();
        }, 0);
    }

    initMce() {
        tinymce.init({
            selector: "textarea#emailSignature",
            readonly: this.emailStatus === 'SENT',
            suffix: '.min',
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
            toolbar: this.emailStatus !== 'SENT' ?
                'undo redo | fontselect fontsizeselect | bold italic underline strikethrough forecolor | maarch_b64image | \
            alignleft aligncenter alignright alignjustify \
            bullist numlist outdent indent | removeformat' : false
        });
    }

    add(event: MatChipInputEvent, type: string): void {

        const input = event.input;
        const value = event.value;

        if ((value || '').trim()) {
            this[type].push(
                {
                    label: value.trim(),
                    email: value.trim(),
                    badFormat : this.isBadEmailFormat(value.trim())
                });
        }

        if (input) {
            input.value = '';
        }
    }

    isBadEmailFormat(email: string) {
        const regex = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/g;

        if (email.trim().match(regex) !== null) {
            return false
        } else {
            return true;
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

    getEmailData(emailId: number) {
        return new Promise((resolve) => {
            this.http.get(`../../rest/emails/${emailId}`).pipe(
                tap((data: any) => {
                    this.emailCreatorId = data.userId;
                    this.currentSender = {
                        label: data.sender.email,
                        email: data.sender.email
                    };
                    this.recipients = data.recipients.map((item: any) => {
                        return {
                            label: item,
                            email: item,
                            badFormat : this.isBadEmailFormat(item)
                        }
                    });
                    this.copies = data.cc.map((item: any) => {
                        return {
                            label: item,
                            email: item,
                            badFormat : this.isBadEmailFormat(item)
                        }
                    });;
                    this.invisibleCopies = data.cci.map((item: any) => {
                        return {
                            label: item,
                            email: item,
                            badFormat : this.isBadEmailFormat(item)
                        }
                    });

                    this.showCopies = this.copies.length > 0;
                    this.showInvisibleCopies = this.invisibleCopies.length > 0;


                    this.emailsubject = data.object;
                    this.emailStatus = data.status;

                    this.emailContent = data.body;

                    Object.keys(data.document).forEach(element => {
                        if (['id', 'isLinked', 'original'].indexOf(element) === -1) {
                            data.document[element].forEach((dataAttach: any) => {
                                const elem = this.emailAttachTool[element].list.filter((item: any) => item.id === dataAttach.id || item.id === dataAttach);
                                if (elem.length > 0) {
                                    this.emailAttach[element] = elem.map((item: any) => {
                                        return {
                                            ...item,
                                            format: dataAttach.original || dataAttach.original === undefined ? item.format : 'pdf',
                                            original: dataAttach.original
                                        }
                                    })
                                }
                            });
                        } else if (element === 'isLinked' && data.document.isLinked === true) {
                            this.emailAttach.document.isLinked = true;
                            this.emailAttach.document.original = data.document.original;
                        }
                    });
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

    getAcknowledgementReceiptData(emailId: number) {
        return new Promise((resolve) => {
            this.http.get(`../../rest/acknowledgementReceipts/${emailId}`).pipe(
                tap((data: any) => {
                    this.currentSender = {
                        label: data.acknowledgementReceipt.userLabel,
                        email: data.acknowledgementReceipt.userLabel
                    };
                    this.recipients = [{
                        label: !this.functions.empty(data.acknowledgementReceipt.contact) ? this.contactService.formatContact(data.acknowledgementReceipt.contact) : this.lang.contactDeleted,
                        email: !this.functions.empty(data.acknowledgementReceipt.contact.email) ? data.acknowledgementReceipt.contact.email : this.lang.contactDeleted
                    }];

                    this.emailsubject = this.lang.shipping;

                    this.emailStatus = 'SENT';
                }),
                exhaustMap(() => this.http.get(`../../rest/acknowledgementReceipts/${emailId}/content`)),
                tap((data: any) => {
                    this.pdfMode = data.format === 'pdf';

                    if (this.pdfMode) {
                        this.emailContent = data.encodedDocument;

                    } else {
                        this.emailContent = atob(data.encodedDocument);
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

    getResourceData() {
        return new Promise((resolve) => {
            this.http.get(`../../rest/resources/${this.data.resId}?light=true`).pipe(
                tap((data: any) => {
                    this.resourceData = data;
                    this.emailsubject = `[${this.resourceData.chrono}] ${this.resourceData.subject}`;
                    this.emailsubject = this.emailsubject.substring(0, 70);
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
        return new Promise((resolve) => {
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
        return new Promise((resolve) => {
            this.http.get(`../../rest/resources/${this.data.resId}/emailsInitialization`).pipe(
                tap((data: any) => {
                    Object.keys(data).forEach(element => {
                        if (element === 'resource') {
                            this.emailAttachTool.document.list = [data[element]];
                        } else {
                            this.emailAttachTool[element].list = data[element].map((item: any) => {
                                return {
                                    ...item,
                                    original: item.original !== undefined ? item.original : true,
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
            filter(value => value !== null),
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

    onSubmit(textMode: string = 'html') {
        this.emailStatus = 'WAITING';
        if (this.data.emailId === null) {
            if (!this.isAllEmailRightFormat()) {
                this.notify.error(this.lang.badEmailsFormat);
            } else {
                if (this.emailsubject === '') {
                    const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.confirm, msg: this.lang.warnEmptySubject } });
    
                    dialogRef.afterClosed().pipe(
                        filter((data: string) => data === 'ok'),
                        tap(() => {
                            this.createEmail(textMode, true);
                        })
                    ).subscribe();
                } else {
                    this.createEmail(textMode, true);
                }
            }
        } else {
            this.updateEmail(textMode, true);
        }
    }

    createEmail(textMode: string = 'html', closeModal: boolean = true) {
        this.http.post(`../../rest/emails`, this.formatEmail(textMode)).pipe(
            tap(() => {
                if (this.emailStatus === 'DRAFT') {
                    this.notify.success(this.lang.draftSaved);
                } else {
                    this.notify.success(`${this.lang.sendingEmail}...`);
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

    deleteEmail() {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete, msg: this.lang.confirmAction } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.delete(`../../rest/emails/${this.data.emailId}`)),
            tap(() => {
                this.notify.success(this.lang.emailDeleted);
                this.closeModal('success');
            }),
            catchError((err) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    updateEmail(textMode: string = 'html', closeModal: boolean = true) {
        this.http.put(`../../rest/emails/${this.data.emailId}`, this.formatEmail()).pipe(
            tap(() => {
                if (this.emailStatus === 'DRAFT') {
                    this.notify.success(this.lang.draftUpdated);
                } else {
                    this.notify.success(`${this.lang.sendingEmail}...`);
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
        if (this.data.emailId === null) {
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

    formatEmail(textMode: string = 'html') {
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
        
        let formatSender = {
            email: this.currentSender.email,
            entityId: !this.functions.empty(this.currentSender.entityId) ? this.currentSender.entityId : null
        };

        const data = {
            document: objAttach,
            sender: formatSender,
            recipients: this.recipients.map(recipient => recipient.email),
            cc: this.showCopies ? this.copies.map(copy => copy.email) : [],
            cci: this.showInvisibleCopies ? this.invisibleCopies.map((invCopy => invCopy.email)) : [],
            object: this.emailsubject,
            body: textMode === 'html' ? tinymce.get('emailSignature').getContent() : tinymce.get('emailSignature').getContent({ format: 'text' }),
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

    canManageMail() {
        if ((this.data.emailId === null) || (this.emailStatus !== 'SENT' && this.headerService.user.id === this.emailCreatorId)) {
            this.recipientsInput.enable();
            return true;
        } else {
            this.recipientsInput.disable();
            return false;
        }
    }

    isAllEmailRightFormat() {
        let state  = true;
        const allEmail = this.recipients.concat(this.copies).concat(this.invisibleCopies);
        
        allEmail.map(item => item.email).forEach(email => {
            if (this.isBadEmailFormat(email)) {
                state = false;
            }
        });

        return state;
    }
}
