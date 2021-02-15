import { Component, OnInit, ViewChild, TemplateRef, ViewContainerRef, EventEmitter, Input, AfterViewInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { HeaderService } from '@service/header.service';
import { FormControl, NgForm } from '@angular/forms';
import { debounceTime, switchMap, distinctUntilChanged, filter, tap, map, catchError, takeUntil, startWith } from 'rxjs/operators';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { SelectionModel } from '@angular/cdk/collections';
import { AppService } from '@service/app.service';
import { MaarchFlatTreeComponent } from '@plugins/tree/maarch-flat-tree.component';
import { MatAutocompleteTrigger } from '@angular/material/autocomplete';
import { merge, Observable, of, Subject } from 'rxjs';
import { ContactService } from '@service/contact.service';
import { FunctionsService } from '@service/functions.service';

@Component({
    selector: 'app-contacts-group-form',
    templateUrl: 'contacts-group-form.component.html',
    styleUrls: ['contacts-group-form.component.scss'],
    providers: [ContactService]
})
export class ContactsGroupFormComponent implements OnInit, AfterViewInit {

    @Input() contactGroupId: number = null;
    @Input() hideSaveButton: boolean = false;

    creationMode: boolean = true;

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
    filterInputControl = new FormControl('');

    @ViewChild('contactsGroupTreeTemplate', { static: true }) contactsGroupTreeTemplate: TemplateRef<any>;
    @ViewChild('paginatorLinkedCorrespondents', { static: false }) paginatorLinkedCorrespondents: MatPaginator;
    @ViewChild('sortLinkedCorrespondents', { static: false }) sortLinkedCorrespondents: MatSort;
    @ViewChild('maarchTree', { static: false }) maarchTree: MaarchFlatTreeComponent;
    @ViewChild('contactsGroupFormUp', { static: false }) contactsGroupFormUp: NgForm;

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
        private functionsService: FunctionsService,
    ) { }

    ngOnInit(): void {
        this.headerService.injectInSideBarLeft(this.contactsGroupTreeTemplate, this.viewContainerRef, 'contactsGroupTree');
    }

    ngAfterViewInit(): void {
        if (this.contactGroupId === null) {
            this.creationMode = true;
            this.initTree();
        } else {
            this.creationMode = false;
            this.getContactGroup(this.contactGroupId);
        }
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
                    data = this.processPostData(data);
                    if (this.filterInputControl.value === '') {
                        this.nbLinkedCorrespondents = data.count;
                    }
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

    processPostData(data: any) {
        data.correspondents =  data.correspondents.map((item: any) => {
            return {
                ...item,
                address: !this.functionsService.empty(item.address) ? item.address : this.translate.instant('lang.unavailable')
            };
        });

        return data;
    }

    initAutocompleteContacts() {
        this.filterInputControl = new FormControl('');
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
                    const formatedContact = this.contactService.formatContactAddress(contact);
                    return {
                        id: contact.id,
                        type: contact.type,
                        name: this.contactService.formatContact(contact),
                        address: !this.functionsService.empty(formatedContact) ? formatedContact : this.translate.instant('lang.unavailable')
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

    isValid() {
        return this.contactsGroupFormUp !== undefined && this.contactsGroupFormUp.form.valid;
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
