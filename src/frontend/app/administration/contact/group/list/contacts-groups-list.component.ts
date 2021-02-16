import { Component, ViewChild, OnInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { HeaderService } from '@service/header.service';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { AppService } from '@service/app.service';
import { FunctionsService } from '@service/functions.service';
import { AdministrationService } from '../../../administration.service';
import { SelectionModel } from '@angular/cdk/collections';
import { catchError, exhaustMap, filter, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { ConfirmComponent } from '@plugins/modal/confirm.component';
import { MatDialog } from '@angular/material/dialog';
import { ContactsGroupFormModalComponent } from '../form/modal/contacts-group-form-modal.component';
import { Router } from '@angular/router';
import { PrivilegeService } from '@service/privileges.service';

@Component({
    selector: 'app-contacts-groups-list',
    templateUrl: 'contacts-groups-list.component.html',
    styleUrls: ['contacts-groups-list.component.scss']
})

export class ContactsGroupsListComponent implements OnInit {

    @Input() allPerimeters: boolean = false;
    @Input() contactGroupFormMode: 'route' | 'modal' = 'route';
    @Input() showAddButton: boolean = false;

    search: string = null;

    contactsGroups: any[] = [];
    userEntitiesIds: number[] = [];
    titles: any[] = [];

    loading: boolean = false;


    displayedColumns = ['select', 'label', 'description', 'nbCorrespondents', 'shared', 'owner', 'actions'];
    filterColumns = ['label', 'description'];
    selection = new SelectionModel<Element>(true, []);

    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: false }) sort: MatSort;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        private headerService: HeaderService,
        public dialog: MatDialog,
        public appService: AppService,
        public functions: FunctionsService,
        public adminService: AdministrationService,
        public privilegeService: PrivilegeService,
        private router: Router
    ) { }

    async ngOnInit(): Promise<void> {
        this.userEntitiesIds = this.headerService.user.entities.map((entity: any) => entity.id);
        this.loading = true;
        await this.getContactsGroups();
        this.loading = false;
    }

    getContactsGroups() {
        return new Promise((resolve) => {
            const param = !this.allPerimeters ? '?profile=true' : '';

            this.http.get('../rest/contactsGroups' + param)
                .subscribe((data) => {
                    this.contactsGroups = data['contactsGroups'].map((contactGroup: any) => {
                        return {
                            ...contactGroup,
                            allowed: !this.isLocked(contactGroup)
                        };
                    });
                    setTimeout(() => {
                        this.adminService.setDataSource('admin_contacts_groups', this.contactsGroups, this.sort, this.paginator, this.filterColumns);
                    }, 0);
                    resolve(true);
                }, (err) => {
                    this.notify.handleErrors(err);
                });
        });
    }

    deleteContactsGroup(row: any) {
        const contactsGroup = this.contactsGroups[row];
        const r = confirm(this.translate.instant('lang.confirmAction') + ' ' + this.translate.instant('lang.delete') + ' « ' + contactsGroup.label + ' »');

        if (r) {
            this.http.delete('../rest/contactsGroups/' + contactsGroup.id)
                .subscribe(() => {
                    const lastElement = this.contactsGroups.length - 1;
                    this.contactsGroups[row] = this.contactsGroups[lastElement];
                    this.contactsGroups[row].position = row;
                    this.contactsGroups.splice(lastElement, 1);
                    this.adminService.setDataSource('admin_contacts_groups', this.contactsGroups, this.sort, this.paginator, this.filterColumns);
                    this.notify.success(this.translate.instant('lang.contactsGroupDeleted'));
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    mergeContactsGroups() {
        let selectedcontactsGroupsLabels: any = this.contactsGroups.filter((contactGroup: any) => this.selection.selected.indexOf(contactGroup.id) > -1).map((contactGroup: any) => contactGroup.label);
        selectedcontactsGroupsLabels = selectedcontactsGroupsLabels.join(', ');
        console.log(selectedcontactsGroupsLabels);
        const msg = this.translate.instant('lang.mergeConfirm', { 0: selectedcontactsGroupsLabels });
        const dialogRef = this.dialog.open(ConfirmComponent, { panelClass: 'maarch-modal', autoFocus: false, disableClose: true, data: { title: `${this.translate.instant('lang.merge')}`, msg: msg } });
        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            // exhaustMap(() => this.http.post(`../rest/contactsGroups/merge`, data)),
            tap(() => {
                this.notify.success(this.translate.instant('lang.attachmentMerged'));
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    toggleContactGroup(element: any) {
        this.selection.toggle(element.id);
    }

    isAllSelected() {
        const numSelected = this.selection.selected.length;
        const numRows = this.contactsGroups.filter(element => element.allowed).length;
        return numSelected === numRows;
    }

    toggleAllContactsGroups() {
        this.isAllSelected() ? this.selection.clear() : this.contactsGroups.filter(element => element.allowed).forEach(element => this.selection.select(element.id));
    }

    isLocked(element: any) {
        if (this.allPerimeters) {
            return false;
        } else {
            return !(element.entities.filter((entity: any) => this.userEntitiesIds.indexOf(entity) > -1).length > 0 || element.owner === this.headerService.user.id);

        }
    }

    goTo(element: any) {
        if (this.contactGroupFormMode === 'modal') {
            this.openContactsGroupModal(element);
        } else {
            this.router.navigate([`/administration/contacts/contacts-groups/${element.id}`]);
        }
    }

    openContactsGroupModal(element: any = null) {
        const dialogRef = this.dialog.open(ContactsGroupFormModalComponent, {
            panelClass: 'maarch-modal',
            disableClose: true,
            width: '99%',
            height: '99%',
            data: {
                modalTitle: element !== null ? element.label : null,
                contactGroupId: element !== null ? element.id : null,
                canAddCorrespondents: (element !== null && element.owner === this.headerService.user.id) || this.privilegeService.hasCurrentUserPrivilege('add_correspondent_in_shared_groups_on_profile'),
                canModifyGroupInfo: (element !== null && element.owner === this.headerService.user.id),
                allPerimeters: this.allPerimeters
            }
        });
        dialogRef.afterClosed().pipe(
            tap(async (res: any) => {
                await this.getContactsGroups();
                if (!this.functions.empty(res) && res.state === 'create') {
                    const newGrp = this.contactsGroups.find((grp: any) => grp.id === res.id);
                    this.openContactsGroupModal(newGrp);
                }
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
