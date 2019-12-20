import { Component, ViewChild, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../../translate.component';
import { NotificationService } from '../../../notification.service';
import { HeaderService }        from '../../../../service/header.service';
import { MatPaginator } from '@angular/material/paginator';
import { MatSidenav } from '@angular/material/sidenav';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { AppService } from '../../../../service/app.service';

declare function $j(selector: any): any;

@Component({
    templateUrl: "contacts-groups-administration.component.html",
    providers: [NotificationService, AppService]
})

export class ContactsGroupsAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight  : MatSidenav;
    
    lang: any = LANG;
    search: string = null;

    contactsGroups: any[] = [];
    titles: any[] = [];

    loading: boolean = false;

    subMenus:any [] = [
        {
            icon: 'fa fa-book',
            route: '/administration/contacts',
            label : this.lang.contactsList
        },
        {
            icon: 'fa fa-cog',
            route: '/administration/contacts/contacts-parameters',
            label : this.lang.contactsParameters
        },
        {
            icon: 'fa fa-code',
            route: '/administration/contacts/contactsCustomFields',
            label : this.lang.customFields
        },
    ];
    
    displayedColumns = ['label', 'description', 'nbContacts', 'public', 'owner', 'actions',];
    dataSource = new MatTableDataSource(this.contactsGroups);
    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: false }) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        private headerService: HeaderService,
        public appService: AppService
    ) {
        $j("link[href='merged_css.php']").remove();
    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.administration + ' ' + this.lang.contactsGroups);

        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = true;

        this.http.get('../../rest/contactsGroups')
            .subscribe((data) => {
                this.contactsGroups = data['contactsGroups'];
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.contactsGroups);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }

    deleteContactsGroup(row: any) {
        var contactsGroup = this.contactsGroups[row];
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + contactsGroup.label + ' »');

        if (r) {
            this.http.delete('../../rest/contactsGroups/' + contactsGroup.id)
                .subscribe(() => {
                    var lastElement = this.contactsGroups.length - 1;
                    this.contactsGroups[row] = this.contactsGroups[lastElement];
                    this.contactsGroups[row].position = row; 
                    this.contactsGroups.splice(lastElement, 1);

                    this.dataSource = new MatTableDataSource(this.contactsGroups);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                    this.notify.success(this.lang.contactsGroupDeleted);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
