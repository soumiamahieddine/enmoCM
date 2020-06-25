import { Component, Inject, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef, MatDialog } from '@angular/material/dialog';
import { LANG } from '../translate.component';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../../service/notification/notification.service';
import { map, tap, catchError, finalize } from 'rxjs/operators';
import { FunctionsService } from '../../service/functions.service';
import { FormControl } from '@angular/forms';
import { SortPipe } from '../../plugins/sorting.pipe';
import { SummarySheetComponent } from '../list/summarySheet/summary-sheet.component';
import { of } from 'rxjs/internal/observable/of';


@Component({
    templateUrl: 'printed-folder-modal.component.html',
    styleUrls: ['printed-folder-modal.component.scss'],
    providers: [SortPipe]
})
export class PrintedFolderModalComponent implements OnInit {
    loading: boolean = true;

    lang: any = LANG;

    document: any[] = [];

    mainDocument: boolean = false;
    summarySheet: boolean = false;
    withSeparator: boolean = false;
    isLoadingResults: boolean = false;

    mainDocumentInformation: any = {};

    printedFolderElement: any = {
        attachments: [],
        notes: [],
        emails: [],
        acknowledgementReceipts: [],
        linkedResources : [],
        linkedResourcesAttachments : [],
    };

    selectedPrintedFolderElement: any = {};

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<PrintedFolderModalComponent>,
        public functions: FunctionsService,
        private sortPipe: SortPipe,
        public dialog: MatDialog) {
    }

    async ngOnInit(): Promise<void> {
        Object.keys(this.printedFolderElement).forEach(element => {
            this.selectedPrintedFolderElement[element] = new FormControl({ value: [], disabled: false });
        });

        this.getMainDocInfo();
        this.getAttachments();
        this.getEmails();
        this.getAcknowledgementReceips();
        this.getNotes();
        await this.getLinkedResources();

        this.loading = false;
    }

    getMainDocInfo() {
        return new Promise((resolve) => {
            this.http.get(`../rest/resources/${this.data.resId}/fileInformation`).pipe(
                map((data: any) => {
                    data = {
                        ...data.information,
                        id: this.data.resId,
                    };
                    return data;
                }),
                tap((data) => {
                    this.mainDocumentInformation = data;
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

    getAttachments() {
        return new Promise((resolve) => {
            this.http.get('../rest/resources/' + this.data.resId + '/attachments').pipe(
                map((data: any) => {
                    data.attachments = data.attachments.map((attachment: any) => {
                        return {
                            id: attachment.resId,
                            label: attachment.title,
                            chrono: !this.functions.empty(attachment.chrono) ? attachment.chrono : this.lang.undefined,
                            type: attachment.typeLabel,
                            creationDate: attachment.creationDate,
                            canConvert: attachment.canConvert,
                            status: attachment.status
                        };
                    });
                    return data.attachments;
                }),
                tap((data) => {

                    this.printedFolderElement.attachments = this.sortPipe.transform(data, 'chrono');
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

    getLinkedResources() {
        return new Promise((resolve) => {
            this.http.get(`../rest/resources/${this.data.resId}/linkedResources`).pipe(
                tap(async (data: any) => {
                    for (let index = 0; index < data.linkedResources.length; index++) {
                        this.printedFolderElement.linkedResources.push({
                            id: data.linkedResources[index].resId,
                            label: data.linkedResources[index].subject,
                            chrono: !this.functions.empty(data.linkedResources[index].chrono) ? data.linkedResources[index].chrono : this.lang.undefined,
                            creationDate: data.linkedResources[index].documentDate,
                            canConvert: data.linkedResources[index].canConvert
                        });
                        await this.getLinkedAttachments(data.linkedResources[index]);
                    }
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

    getLinkedAttachments(resourceMaster: any) {
        return new Promise((resolve) => {
            this.http.get(`../rest/resources/${resourceMaster.resId}/attachments`).pipe(
                map((data: any) => {
                    data.attachments = data.attachments.map((attachment: any) => {
                        return {
                            id: attachment.resId,
                            label: attachment.title,
                            resIdMaster : resourceMaster.resId,
                            chronoMaster: resourceMaster.chrono,
                            chrono: !this.functions.empty(attachment.chrono) ? attachment.chrono : this.lang.undefined,
                            type: attachment.typeLabel,
                            creationDate: attachment.creationDate,
                            canConvert: attachment.canConvert,
                            status: attachment.status
                        };
                    });
                    return data.attachments;
                }),
                tap((data) => {
                    this.printedFolderElement.linkedResourcesAttachments = this.printedFolderElement.linkedResourcesAttachments.concat(this.sortPipe.transform(data, 'chronoMaster'));
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

    getEmails() {
        return new Promise((resolve) => {
            this.http.get(`../rest/resources/${this.data.resId}/emails?type=email`).pipe(
                map((data: any) => {
                    data.emails = data.emails.map((item: any) => {
                        return {
                            id: item.id,
                            recipients: item.recipients,
                            creationDate: item.creation_date,
                            label: !this.functions.empty(item.object) ? item.object : `<i>${this.lang.emptySubject}<i>`,
                            canConvert: true
                        };
                    });
                    return data.emails;
                }),
                tap((data: any) => {
                    this.printedFolderElement.emails = this.sortPipe.transform(data, 'creationDate');

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

    getNotes() {
        return new Promise((resolve) => {
            this.http.get(`../rest/resources/${this.data.resId}/notes`).pipe(
                map((data: any) => {
                    data.notes = data.notes.map((item: any) => {
                        return {
                            id: item.id,
                            creator: `${item.firstname} ${item.lastname}`,
                            creationDate: item.creation_date,
                            label: item.value,
                            canConvert: true
                        };
                    });
                    return data.notes;
                }),
                tap((data: any) => {
                    this.printedFolderElement.notes = this.sortPipe.transform(data, 'creationDate');

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

    getAcknowledgementReceips() {
        return new Promise((resolve) => {
            this.http.get(`../rest/resources/${this.data.resId}/acknowledgementReceipts?type=ar`).pipe(
                map((data: any) => {
                    data = data.map((item: any) => {
                        let email;
                        if (!this.functions.empty(item.contact.email)) {
                            email = item.contact.email;
                        } else {
                            email = this.lang.contactDeleted;
                        }
                        let name;
                        if (!this.functions.empty(item.contact.firstname) && !this.functions.empty(item.contact.lastname)) {
                            name = `${item.contact.firstname} ${item.contact.lastname}`;
                        } else {
                            name = this.lang.contactDeleted;
                        }

                        return {
                            id: item.id,
                            sender: false,
                            recipients: item.format === 'html' ? email : name,
                            creationDate: item.creationDate,
                            label: item.format === 'html' ? this.lang.ARelectronic : this.lang.ARPaper,
                            canConvert: true
                        };
                    });
                    return data;
                }),
                tap((data: any) => {
                    this.printedFolderElement.acknowledgementReceipts = this.sortPipe.transform(data, 'creationDate');

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

    toggleAllElements(state: boolean, type: any) {

        if (state) {
            this.selectedPrintedFolderElement[type].setValue(this.printedFolderElement[type].filter((item: any) => item.canConvert).map((item: any) => item.id));
        } else {
            this.selectedPrintedFolderElement[type].setValue([]);
        }
    }

    onSubmit() {
        this.isLoadingResults = true;

        this.http.post(`../rest/resources/folderPrint`, this.formatPrintedFolder(), { responseType: 'blob' }).pipe(
            tap((data: any) => {
                const downloadLink = document.createElement('a');
                downloadLink.href = window.URL.createObjectURL(data);
                let today: any;
                let dd: any;
                let mm: any;
                let yyyy: any;

                today = new Date();
                dd = today.getDate();
                mm = today.getMonth() + 1;
                yyyy = today.getFullYear();

                if (dd < 10) {
                    dd = '0' + dd;
                }
                if (mm < 10) {
                    mm = '0' + mm;
                }
                today = dd + '-' + mm + '-' + yyyy;
                downloadLink.setAttribute('download', 'export_maarch_' + today + '.pdf');
                document.body.appendChild(downloadLink);
                downloadLink.click();
            }),
            finalize(() => this.isLoadingResults = false),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    formatPrintedFolder() {
        const printedFolder: any = {
            withSeparator: this.withSeparator,
            summarySheet: this.summarySheet,
            resources: []
        };
        const resource = {
            resId: this.data.resId,
            document: this.mainDocument,
        };
        Object.keys(this.printedFolderElement).forEach(element => {
            resource[element] = this.selectedPrintedFolderElement[element].value.length === this.printedFolderElement[element].length ? 'ALL' : this.selectedPrintedFolderElement[element].value;
        });

        printedFolder.resources.push(resource);

        return printedFolder;
    }

    openSummarySheet(): void {

        const dialogRef = this.dialog.open(SummarySheetComponent, {
            panelClass: 'maarch-full-height-modal',
            width: '800px',
            data: {
                paramMode: true
            }
        });
        dialogRef.afterClosed().pipe(
            tap((data: any) => {
                this.summarySheet = data;
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    isEmptySelection() {
        let state = true;
        Object.keys(this.printedFolderElement).forEach(element => {
            if (this.selectedPrintedFolderElement[element].value.length > 0) {
                state = false;
            }
        });

        if (this.summarySheet || this.mainDocument) {
            state = false;
        }

        return state;
    }
}
