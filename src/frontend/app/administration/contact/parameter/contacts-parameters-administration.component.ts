import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../../translate.component';
import { NotificationService } from '../../../notification.service';
import { HeaderService }        from '../../../../service/header.service';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../../../service/app.service';
import { MatTableDataSource, MatPaginator, MatSort } from '@angular/material';

declare function $j(selector: any): any;

@Component({
    templateUrl: "contacts-parameters-administration.component.html",
    providers: [NotificationService, AppService]
})
export class ContactsParametersAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;

    lang: any = LANG;

    subMenus:any [] = [
        {
            icon: 'fa fa-book',
            route: '/administration/contacts/list',
            label : this.lang.contactsList
        },
        {
            icon: 'fa fa-users',
            route: '/administration/contacts/contacts-groups',
            label : this.lang.contactsGroups
        },
        {
            icon: 'fa fa-code',
            route: '/administration/contacts/contactsCustomFields',
            label : this.lang.customFields
        },
    ];

    contactsFilling: any = {
        'enable': false,
        'first_threshold': '33',
        'second_threshold': '66',
    };

    contactsParameters: any = [];

    arrRatingColumns: String[] = [];
    fillingColor = {
        'first_threshold': '#ff9e9e',
        'second_threshold': '#f6cd81',
        'third_threshold': '#ccffcc',
    };

    loading: boolean = false;

    dataSource = new MatTableDataSource(this.contactsParameters);
    displayedColumns = ['identifier', 'mandatory', 'filling', 'searchable', 'displayable'];

    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: false }) sort: MatSort;

    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        private headerService: HeaderService,
        public appService: AppService) {
            $j("link[href='merged_css.php']").remove();
    }

    ngOnInit(): void {

        this.loading = true;

        this.headerService.setHeader(this.lang.contactsParameters);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.http.get('../../rest/contactsParameters')
            .subscribe((data: any) => {
                this.contactsFilling = data.contactsFilling;
                this.contactsParameters = data.contactsParameters;
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.contactsParameters);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            });
    }

    addCriteria(event: any, criteria: any, type: string) {
        this.contactsParameters.forEach((col: any, i: number) => {
            if (col.id == criteria.id) {
                this.contactsParameters[i][type] = event.checked;
            }
        });

        this.onSubmit();
    }

    onSubmit() {
        if (this.contactsFilling.first_threshold >= this.contactsFilling.second_threshold) {
            this.contactsFilling.second_threshold = this.contactsFilling.first_threshold + 1;
        }
        this.http.put('../../rest/contactsParameters', {'contactsFilling': this.contactsFilling, 'contactsParameters': this.contactsParameters})
            .subscribe(() => {
                this.notify.success(this.lang.parameterUpdated);

            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    toggleFillingContact() {
        this.contactsFilling.enable == true ? this.contactsFilling.enable = false : this.contactsFilling.enable = true;
        this.onSubmit();
    }
}
