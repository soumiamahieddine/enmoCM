import { Component, OnInit, ViewChild, EventEmitter, ElementRef, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { HeaderService } from '../../service/header.service';
import { Observable, merge, Subject, of as observableOf, of } from 'rxjs';
import { MatPaginator, MatSort, MatDialog, MatTableDataSource } from '@angular/material';
import { takeUntil, startWith, switchMap, map, catchError, filter, exhaustMap, tap, debounceTime, distinctUntilChanged, finalize } from 'rxjs/operators';
import { FormControl } from '@angular/forms';
import { FunctionsService } from '../../service/functions.service';
import { LatinisePipe } from 'ngx-pipes';
import { PrivilegeService } from '../../service/privileges.service';

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

    typeColor = {
        startDate: '#b5cfd8',
        endDate: '#7393a7',
        actions: '#7d5ba6',
        systemActions: '#7d5ba6',
        users: '#009dc5',
    };

    currentFilter: string = '';
    filterTypes: any[] = [];


    @Input('resId') resId: number = null;

    @ViewChild(MatSort, { static: false }) sort: MatSort;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        private headerService: HeaderService,
        public dialog: MatDialog,
        public functions: FunctionsService,
        private latinisePipe: LatinisePipe,
        public privilegeService: PrivilegeService) { }

    async ngOnInit(): Promise<void> {
        this.sendedResources = [];
        await this.initAcknowledgementReceipList();
        await this.initEmailList();
        this.initFilter();

        setTimeout(() => {
            this.dataSource = new MatTableDataSource(this.sendedResources);
            this.dataSource.sort = this.sort;
        }, 0);
        
        this.loading = false;
        
    }

    initAcknowledgementReceipList() {
        return new Promise((resolve, reject) => {
            this.http.get(`../../rest/resources/${this.resId}/acknowledgementReceipts`).pipe(
                map((data: any) => {
                    data = data.map((item: any) => {
                        return {
                            id: item.id,
                            sender : false,
                            recipients : item.format === 'html' ? item.contact.email : `${item.contact.firstname} ${item.contact.lastname}`,
                            creationDate : item.creationDate,
                            sendDate : item.sendDate,
                            type: 'acknowledgementReceipt',
                            typeColor: '#7d5ba6',
                            desc: item.format === 'html' ? this.lang.ARelectronic : this.lang.ARPaper,
                            status : item.format === 'html' && item.sendDate === null ? 'ERROR' : 'SENT',
                            hasAttach : false,
                            hasNote : false,
                            hasMainDoc : false
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
            this.http.get(`../../rest/resources/${this.resId}/emails`).pipe(
                map((data: any) => {
                    data.emails = data.emails.map((item: any) => {
                        return {
                            id: item.id,
                            sender : item.sender.email,
                            recipients : item.recipients,
                            creationDate : item.creation_date,
                            sendDate : item.send_date,
                            type: 'email',
                            typeColor: '#5bc0de',
                            desc: item.object,
                            status : item.status,
                            hasAttach : !this.functions.empty(item.document.attachments),
                            hasNote : !this.functions.empty(item.document.notes),
                            hasMainDoc : item.document.isLinked
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

    initFilter() {
        this.sendedResources.forEach((element: any) => {
            if (this.filterTypes.filter(type => type.id === element.type).length === 0) {
                this.filterTypes.push( {
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
}