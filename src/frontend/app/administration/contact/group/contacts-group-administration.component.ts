import { Component, OnInit, ViewChild, TemplateRef, ViewContainerRef, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { HeaderService } from '@service/header.service';
import { FormControl } from '@angular/forms';
import { debounceTime, switchMap, distinctUntilChanged, filter, tap, map, catchError, takeUntil, startWith } from 'rxjs/operators';
import { MatPaginator } from '@angular/material/paginator';
import { MatSidenav } from '@angular/material/sidenav';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { SelectionModel } from '@angular/cdk/collections';
import { AppService } from '@service/app.service';
import { MaarchFlatTreeComponent } from '@plugins/tree/maarch-flat-tree.component';
import { MatAutocompleteTrigger } from '@angular/material/autocomplete';
import { merge, Observable, of, Subject } from 'rxjs';
import { ContactService } from '@service/contact.service';

@Component({
    templateUrl: 'contacts-group-administration.component.html',
    styleUrls: [
        'contacts-group-administration.component.scss'
    ],
    providers: [ContactService]
})
export class ContactsGroupAdministrationComponent implements OnInit {

    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;
    @ViewChild('adminMenuTemplate', { static: true }) adminMenuTemplate: TemplateRef<any>;

    subMenus: any[] = [
        {
            icon: 'fa fa-book',
            route: '/administration/contacts',
            label: this.translate.instant('lang.contactsList'),
            current: false
        },
        {
            icon: 'fa fa-code',
            route: '/administration/contacts/contactsCustomFields',
            label: this.translate.instant('lang.customFieldsAdmin'),
            current: false
        },
        {
            icon: 'fa fa-cog',
            route: '/administration/contacts/contacts-parameters',
            label: this.translate.instant('lang.contactsParameters'),
            current: false
        },
        {
            icon: 'fa fa-users',
            route: '/administration/contacts/contacts-groups',
            label: this.translate.instant('lang.contactsGroups'),
            current: false
        },
        {
            icon: 'fas fa-magic',
            route: '/administration/contacts/duplicates',
            label: this.translate.instant('lang.duplicatesContactsAdmin'),
            current: false
        },
    ];

    creationMode: boolean;
    contactsGroup: any = {
        entities: []
    };
    nbLinkedCorrespondents: number;
    nbFilteredLinkedCorrespondents: number;

    loading: boolean = false;

    relatedCorrespondents: any = [];
    loadingCorrespondents: boolean = false;
    loadingLinkedCorrespondents: boolean = false;
    savingCorrespondents: boolean = false;
    initAutoCompleteContact = true;

    searchResult: any = [];

    displayedColumns = ['type', 'name', 'address'];
    displayedColumnsAdded = ['type', 'contact', 'address', 'actions'];
    dataSource: any;
    // dataSourceLinkedCorrespondents: any;
    dataSourceLinkedCorrespondents: CorrespondentListHttpDao | null;
    selection = new SelectionModel<Element>(true, []);
    private destroy$ = new Subject<boolean>();
    filtersChange = new EventEmitter();
    filterInputControl = new FormControl();

    @ViewChild('paginatorLinkedCorrespondents', { static: false }) paginatorLinkedCorrespondents: MatPaginator;
    @ViewChild('sortLinkedCorrespondents', { static: false }) sortLinkedCorrespondents: MatSort;
    @ViewChild('maarchTree', { static: true }) maarchTree: MaarchFlatTreeComponent;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService,
        private headerService: HeaderService,
        public appService: AppService,
        public contactService: ContactService,
        private viewContainerRef: ViewContainerRef,
    ) { }

    ngOnInit(): void {
        this.loading = true;

        this.headerService.injectInSideBarLeft(this.adminMenuTemplate, this.viewContainerRef, 'adminMenu');
        this.route.params.subscribe(params => {
            if (typeof params['id'] === 'undefined') {
                this.initTree();
                this.headerService.setHeader(this.translate.instant('lang.contactGroupCreation'));
                this.creationMode = true;
                this.loading = false;
            } else {
                this.headerService.setHeader(this.translate.instant('lang.contactsGroupModification'));
                this.creationMode = false;
                this.getContactGroup(params['id']);
            }
        });
    }

    getContactGroup(contactGroupId: number) {
        this.http.get('../rest/contactsGroups/' + contactGroupId).pipe(
            tap((data: any) => {
                this.contactsGroup = data.contactsGroup;
                this.maarchTree.initData(data.entities);
                this.headerService.setHeader(this.translate.instant('lang.contactsGroupModification'), this.contactsGroup.label);
                this.loading = false;
                setTimeout(() => {
                    this.initRelatedCorrespondentList();
                    this.initAutocompleteContacts();
                }, 0);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    initTree() {
        // FOR TEST
        this.http.get('../rest/entities').pipe(
            map((data: any) => {
                data.entities = data.entities.map((entity: any) => {
                    return {
                        text: entity.entity_label,
                        icon: entity.icon,
                        parent_id: entity.parentSerialId,
                        id: entity.serialId,
                        state: {
                            opened: true
                        }
                    };
                });
                return data.entities;
            }),
            tap((entities: any) => {
                /*entities.forEach(element => {
                    if (this.availableEntities.indexOf(+element.id) > -1) {
                        element.state.disabled = false;
                    } else {
                        element.state.disabled = true;
                    }
                });*/
                this.maarchTree.initData(entities);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    initRelatedCorrespondentList() {
        this.dataSourceLinkedCorrespondents = new CorrespondentListHttpDao(this.http);
        this.sortLinkedCorrespondents.sortChange.subscribe(() => this.paginatorLinkedCorrespondents.pageIndex = 0);

        // When list is refresh (sort, page, filters)
        merge(this.sortLinkedCorrespondents.sortChange, this.paginatorLinkedCorrespondents.page, this.filtersChange)
            .pipe(
                takeUntil(this.destroy$),
                startWith({}),
                switchMap(() => {
                    this.loadingLinkedCorrespondents = true;
                    return this.dataSourceLinkedCorrespondents!.getRepoIssues(
                        this.sortLinkedCorrespondents.active, this.sortLinkedCorrespondents.direction, this.paginatorLinkedCorrespondents.pageIndex, '../rest/contactsGroups/' + this.contactsGroup.id + '/correspondents', this.filterInputControl.value);
                }),
                map(data => {
                    this.loadingLinkedCorrespondents = false;
                    // data = this.processPostData(data);
                    this.nbLinkedCorrespondents = data.countAll;
                    this.nbFilteredLinkedCorrespondents = data.count;
                    return data.correspondents;
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    this.loadingLinkedCorrespondents = false;
                    return of(false);
                })
            ).subscribe(data => this.relatedCorrespondents = data);
            // ).subscribe(data => this.relatedCorrespondents = []);

    }

    initAutocompleteContacts() {
        this.filterInputControl = new FormControl();
        this.filterInputControl.valueChanges
            .pipe(
                tap((value) => {
                    this.dataSource = null;
                    if (value.length === 0) {
                        this.paginatorLinkedCorrespondents.pageIndex = 0;
                        this.refreshDao();
                    }
                }),
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                tap((data) => {
                    this.paginatorLinkedCorrespondents.pageIndex = 0;
                    this.refreshDao();
                }),
            ).subscribe();
    }

    refreshDao() {
        this.filtersChange.emit();
    }

    searchContact(search: string, event: Event, trigger: MatAutocompleteTrigger) {
        this.loadingCorrespondents = true;
        this.http.get('../rest/autocomplete/correspondents', { params: { 'limit': '1000', 'search': search } }).pipe(
            tap((data: any) => {
                this.searchResult = data.map((contact: any) => {
                    return {
                        id: contact.id,
                        type: contact.type,
                        name: this.contactService.formatContact(contact),
                        address: this.contactService.formatContactAddress(contact),
                    };
                });
                this.dataSource = new MatTableDataSource(this.searchResult);
                this.loadingCorrespondents = false;
                setTimeout(() => {
                    trigger.openPanel();
                }, 100);
            })
        ).subscribe();
    }

    selectEntities(entities: any[]) {
        const formatedEntities = entities.map((entity: any) => entity.id);
        this.contactsGroup.entities = [...this.contactsGroup.entities, ...formatedEntities];
        this.contactsGroup.entities = this.contactsGroup.entities.filter((entity: any, index: number) => this.contactsGroup.entities.indexOf(entity) === index);
    }

    deselectEntities(entities: any[]) {
        const formatedEntities = entities.map((entity: any) => entity.id);
        this.contactsGroup.entities = this.contactsGroup.entities.filter((entity: any) => formatedEntities.indexOf(entity) === -1);
    }

    saveContactsList(): void {
        this.savingCorrespondents = true;
        this.http.post('../rest/contactsGroups/' + this.contactsGroup.id + '/correspondents', { 'correspondents': this.formatCorrespondents() })
            .subscribe((data: any) => {
                this.notify.success(this.translate.instant('lang.contactAdded'));
                this.selection.clear();
                this.savingCorrespondents = false;
                this.refreshDao();
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    formatCorrespondents() {
        return this.selection.selected.map((correspondent: any) => {
            return {
                type: correspondent.type,
                id: correspondent.id
            };
        });
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post('../rest/contactsGroups', this.contactsGroup)
                .subscribe((data: any) => {
                    this.router.navigate(['/administration/contacts/contacts-groups/' + data.id]);
                    this.notify.success(this.translate.instant('lang.contactsGroupAdded'));
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put('../rest/contactsGroups/' + this.contactsGroup.id, this.contactsGroup)
                .subscribe(() => {
                    this.router.navigate(['/administration/contacts-groups']);
                    this.notify.success(this.translate.instant('lang.contactsGroupUpdated'));

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    preDelete(row: any) {
        const r = confirm(this.translate.instant('lang.reallyWantToDeleteContactFromGroup'));

        if (r) {
            this.removeContact(this.relatedCorrespondents[row], row);
        }
    }

    removeContact(contact: any, row: any) {
        this.http.delete('../rest/contactsGroups/' + this.contactsGroup.id + '/contacts/' + contact['id'])
            .subscribe(() => {
                this.notify.success(this.translate.instant('lang.contactDeletedFromGroup'));
                this.refreshDao();
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    isInGrp(contact: any): boolean {
        let isInGrp = false;
        this.relatedCorrespondents.forEach((row: any) => {
            if (row.id == contact.id) {
                isInGrp = true;
            }
        });
        return isInGrp;
    }

    toggleAll() {
        this.dataSource.data.forEach((row: any) => {
            if (!this.isInGrp(row)) {
                this.selection.select(row);
            }
        });
        if (this.selection.selected.length > 0) {
            this.saveContactsList();
        }
    }

    toggleCorrespondent(element: any) {
        if (!this.isInGrp(element)) {
            this.selection.toggle(element);
            this.saveContactsList();
        }
    }

    open(event: Event, trigger: MatAutocompleteTrigger) {
        event.stopPropagation();
        setTimeout(() => {
            trigger.openPanel();
        }, 100);
    }
}
export interface CorrespondentList {
    correspondents: any[];
    count: number;
    countAll: number;
}
export class CorrespondentListHttpDao {

    constructor(private http: HttpClient) { }

    getRepoIssues(sort: string, order: string, page: number, href: string, search: string): Observable<CorrespondentList> {

        let offset = page * 10;
        const requestUrl = `${href}?limit=10&offset=${offset}&order=${order}&orderBy=${sort}&search=${search}`;

        return this.http.get<CorrespondentList>(requestUrl);
    }
}
