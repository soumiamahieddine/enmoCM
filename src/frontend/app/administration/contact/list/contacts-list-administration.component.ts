import { Component, OnInit, ViewChild, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../../translate.component';
import { NotificationService } from '../../../notification.service';
import { HeaderService }        from '../../../../service/header.service';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../../../service/app.service';
import { Observable, merge, Subject, of as observableOf, of } from 'rxjs';
import { MatPaginator, MatSort, MatDialog } from '@angular/material';
import { takeUntil, startWith, switchMap, map, catchError, filter, exhaustMap, tap, debounceTime, distinctUntilChanged } from 'rxjs/operators';
import { ConfirmComponent } from '../../../../plugins/modal/confirm.component';
import { FormControl } from '@angular/forms';

@Component({
    selector: 'contact-list',
    templateUrl: "contacts-list-administration.component.html",
    styleUrls: ['contacts-list-administration.component.scss'],
    providers: [NotificationService, AppService]
})
export class ContactsListAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;

    lang: any = LANG;
    loading: boolean = false;

    filtersChange = new EventEmitter();
    
    data: any;

    displayedColumnsContact: string[] = ['firstname', 'lastname', 'company', 'formatedAddress', 'actions'];

    isLoadingResults = true;
    routeUrl: string = '../../rest/contacts';
    resultListDatabase: ContactListHttpDao | null;
    resultsLength = 0;

    searchContact = new FormControl();
    search: string = '';

    @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
    @ViewChild('tableContactListSort', { static: true }) sort: MatSort;

    private destroy$ = new Subject<boolean>();

    subMenus:any [] = [
        {
            icon: 'fa fa-code',
            route: '/administration/contacts/contactsCustomFields',
            label : this.lang.customFields
        },
        {
            icon: 'fa fa-cog',
            route: '/administration/contacts/contacts-parameters',
            label : this.lang.contactsParameters
        },
        {
            icon: 'fa fa-users',
            route: '/administration/contacts/contacts-groups',
            label : this.lang.contactsGroups
        },
    ];
    
    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        private headerService: HeaderService,
        public appService: AppService,
        public dialog: MatDialog) { }

    ngOnInit(): void {
        this.loading = true;
        this.initContactList();
        this.initAutocompleteContacts();
    }

    initContactList() {
        this.resultListDatabase = new ContactListHttpDao(this.http);
        this.paginator.pageIndex = 0;
        this.sort.active = 'lastname';
        this.sort.direction = 'asc';
        this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

        // When list is refresh (sort, page, filters)
        merge(this.sort.sortChange, this.paginator.page, this.filtersChange)
            .pipe(
                takeUntil(this.destroy$),
                startWith({}),
                switchMap(() => {
                    this.isLoadingResults = true;
                    return this.resultListDatabase!.getRepoIssues(
                        this.sort.active, this.sort.direction, this.paginator.pageIndex, this.routeUrl, this.search);
                }),
                map(data => {
                    this.isLoadingResults = false;
                    data = this.processPostData(data);
                    this.resultsLength = data.count;
                    this.headerService.setHeader(this.lang.administration + ' ' + this.lang.contacts.toLowerCase(), '', '');
                    return data.contacts;
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    this.isLoadingResults = false;
                    return observableOf([]);
                })
            ).subscribe(data => this.data = data);
    }

    processPostData(data: any) {
        data.contacts.forEach((element: any) => {
            let tmpFormatedAddress = [];
            tmpFormatedAddress.push(element.addressNumber);
            tmpFormatedAddress.push(element.addressStreet);
            tmpFormatedAddress.push(element.addressPostcode);
            tmpFormatedAddress.push(element.addressTown);
            tmpFormatedAddress.push(element.addressCountry);
            element.formatedAddress = tmpFormatedAddress.filter(address => !this.isEmptyValue(address)).join(' ');
        });

        return data;
    }

    deleteContact(contact: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete, msg: this.lang.confirmAction } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.delete(`../../rest/contacts/${contact.id}`)),
            tap((data: any) => {
                this.refreshDao();
                this.notify.success(this.lang.contactDeleted);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    toggleContact(contact: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.suspend, msg: this.lang.confirmAction } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.put(`../../rest/contacts/${contact.id}/activation`, {enabled : !contact.enabled})),
            tap((data: any) => {
                this.refreshDao();
                if (!contact.enabled === true) {
                    this.notify.success(this.lang.contactEnabled);
                } else {
                    this.notify.success(this.lang.contactDisabled);
                }
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    refreshDao() {
        this.filtersChange.emit();
    }

    initAutocompleteContacts() {
        this.searchContact.valueChanges
            .pipe(
                tap((value) => {
                    if (value.length === 0) {
                        this.search = '';
                        this.refreshDao();
                    }   
                }),
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                tap((data) => {
                    this.search = data;
                    this.refreshDao();
                }),
            ).subscribe();
    }

    isEmptyValue(value: string) {

        if (value === null) {
            return true;

        } else if (Array.isArray(value)) {
            if (value.length > 0) {
                return false;
            } else {
                return true;
            }
        } else if (String(value) !== '') {
            return false;
        } else {
            return true;
        }
    }
}

export interface ContactList {
    contacts: any[];
    count: number;
}
export class ContactListHttpDao {

    constructor(private http: HttpClient) { }

    getRepoIssues(sort: string, order: string, page: number, href: string, search: string): Observable<ContactList> {
        
        let offset = page * 10;
        const requestUrl = `${href}?limit=10&offset=${offset}&order=${order}&orderBy=${sort}&search=${search}`;

        return this.http.get<ContactList>(requestUrl);
    }
}