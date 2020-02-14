import { Component, OnInit, ViewChild, EventEmitter, ElementRef, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { Observable, merge, Subject, of as observableOf, of } from 'rxjs';
import { MatPaginator, MatSort, MatDialog, MatTableDataSource } from '@angular/material';
import { takeUntil, startWith, switchMap, map, catchError, filter, exhaustMap, tap, debounceTime, distinctUntilChanged, finalize } from 'rxjs/operators';
import { FormControl } from '@angular/forms';
import { FunctionsService } from '../../service/functions.service';
import { PrivilegeService } from '../../service/privileges.service';
import { SendedResourcePageComponent } from './sended-resource-page/sended-resource-page.component';

@Component({
    selector: 'app-sended-resource-list',
    templateUrl: "sended-resource-list.component.html",
    styleUrls: ['sended-resource-list.component.scss'],
})
export class SendedResourceListComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = true;

    filtersChange = new EventEmitter();


    dataSource: any;
    displayedColumns: string[] = ['creationDate'];

    sendedResources: any[] = [];

    resultsLength = 0;

    currentFilter: string = '';
    filterTypes: any[] = [];


    @Input('resId') resId: number = null;

    @ViewChild(MatSort, { static: false }) sort: MatSort;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public dialog: MatDialog,
        public functions: FunctionsService,
        public privilegeService: PrivilegeService) { }

    async ngOnInit(): Promise<void> {
        this.loadList();
    }

    async loadList() {
        this.sendedResources = [];
        this.loading = true;
        await this.initAcknowledgementReceipList();
        await this.initEmailList();
        await this.initMessageExchange();
        await this.initShippings()
        this.initFilter();

        setTimeout(() => {
            this.dataSource = new MatTableDataSource(this.sendedResources);
            this.dataSource.sort = this.sort;
        }, 0);
        this.loading = false;
    }

    initAcknowledgementReceipList() {
        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/resources/${this.resId}/acknowledgementReceipts?type=ar`).pipe(
                map((data: any) => {
                    data = data.map((item: any) => {
                        return {
                            id: item.id,
                            sender: false,
                            recipients: item.format === 'html' ? item.contact.email : `${item.contact.firstname} ${item.contact.lastname}`,
                            creationDate: item.creationDate,
                            sendDate: item.sendDate,
                            type: 'acknowledgementReceipt',
                            typeColor: '#7d5ba6',
                            desc: item.format === 'html' ? this.lang.ARelectronic : this.lang.ARPaper,
                            status: item.format === 'html' && item.sendDate === null ? 'ERROR' : 'SENT',
                            hasAttach: false,
                            hasNote: false,
                            hasMainDoc: false,
                            canManage: false
                        }
                    })
                    return data;
                }),
                tap((data: any) => {
                    this.sendedResources = this.sendedResources.concat(data);

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

    initEmailList() {
        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/resources/${this.resId}/emails?type=email`).pipe(
                map((data: any) => {
                    data.emails = data.emails.map((item: any) => {
                        return {
                            id: item.id,
                            sender: item.sender.email,
                            recipients: item.recipients,
                            creationDate: item.creation_date,
                            sendDate: item.send_date,
                            type: 'email',
                            typeColor: '#5bc0de',
                            desc: item.object,
                            status: item.status,
                            hasAttach: !this.functions.empty(item.document.attachments),
                            hasNote: !this.functions.empty(item.document.notes),
                            hasMainDoc: item.document.isLinked,
                            canManage: true
                        }
                    })
                    return data.emails;
                }),
                tap((data: any) => {
                    this.sendedResources = this.sendedResources.concat(data);

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

    initMessageExchange() {
        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/resources/${this.resId}/messageExchanges`).pipe(
                map((data: any) => {
                    data.messageExchanges = data.messageExchanges.map((item: any) => {
                        return {
                            id: item.messageId,
                            sender: item.sender,
                            recipients: item.recipient,
                            creationDate: item.creationDate,
                            sendDate: item.receptionDate,
                            operationDate: item.operationDate,
                            type: 'm2m_ARCHIVETRANSFER',
                            typeColor: '#F99830',
                            desc: this.lang.m2m_ARCHIVETRANSFER,
                            status: item.status,
                            hasAttach: false,
                            hasNote: false,
                            hasMainDoc: false,
                            canManage: false
                        }
                    })
                    return data.messageExchanges;
                }),
                tap((data: any) => {
                    this.sendedResources = this.sendedResources.concat(data);

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

    initShippings() {
        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/resources/${this.resId}/shippings`).pipe(
                map((data: any) => {
                    data = data.map((item: any) => {
                        return {
                            id: item.id,
                            sender: item.userLabel,
                            recipients: item.userLabel,
                            creationDate: item.creationDate,
                            sendDate: item.creationDate,
                            type: 'shipping',
                            typeColor: '#9440D5',
                            desc: 'Envoi maileva',
                            status: 'SENT',
                            hasAttach: item.creationDate === 'attachment',
                            hasNote: false,
                            hasMainDoc: item.creationDate === 'resource',
                            canManage: false
                        }
                    })
                    return data;
                }),
                tap((data: any) => {
                    this.sendedResources = this.sendedResources.concat(data);

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

    initFilter() {
        this.sendedResources.forEach((element: any) => {
            if (this.filterTypes.filter(type => type.id === element.type).length === 0) {
                this.filterTypes.push({
                    id: element.type,
                    label: this.lang[element.type]
                });
            }
        });
    }

    processPostData(data: any) {
        return data;
    }

    filterType(ev: any) {
        this.currentFilter = ev.value;
        this.dataSource.filter = ev.value;
    }

    openPromptMail(row: any = null) {

        if (row.canManage) {
            const dialogRef = this.dialog.open(SendedResourcePageComponent, { maxWidth: '90vw', width: '750px', disableClose: true, data: { title: `Toto`, resId: this.resId, emailId: row.id } });

            dialogRef.afterClosed().pipe(
                filter((data: string) => data === 'success'),
                tap(() => {
                    this.loadList();
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        }

    }
}