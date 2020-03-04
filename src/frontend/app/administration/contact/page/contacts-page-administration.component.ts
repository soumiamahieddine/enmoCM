import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../../translate.component';
import { NotificationService } from '../../../notification.service';
import { HeaderService } from '../../../../service/header.service';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../../../service/app.service';
import { MatDialog } from '@angular/material';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
    templateUrl: "contacts-page-administration.component.html",
    styleUrls: ['contacts-page-administration.component.scss'],
    providers: [NotificationService, AppService]
})
export class ContactsPageAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;

    lang: any = LANG;
    loading: boolean = false;

    creationMode: boolean = true;

    subMenus:any [] = [
        {
            icon: 'fa fa-book',
            route: '/administration/contacts/list',
            label : this.lang.contactsList,
            current: false
        },
        {
            icon: 'fa fa-code',
            route: '/administration/contacts/contactsCustomFields',
            label : this.lang.customFieldsAdmin,
            current: false
        },
        {
            icon: 'fa fa-cog',
            route: '/administration/contacts/contacts-parameters',
            label : this.lang.contactsParameters,
            current: false
        },
        {
            icon: 'fa fa-users',
            route: '/administration/contacts/contacts-groups',
            label : this.lang.contactsGroups,
            current: false
        },
    ];
    
    contactId: number = null;

    constructor(
        public http: HttpClient,
        private route: ActivatedRoute,
        private router: Router,
        private headerService: HeaderService,
        public appService: AppService,
        public dialog: MatDialog) { }

    ngOnInit(): void {

        this.loading = true;

        this.route.params.subscribe((params: any) => {
            if (typeof params['id'] == "undefined") {
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.headerService.setHeader(this.lang.contactCreation);
                this.creationMode = true;
                this.loading = false;

            } else {
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(this.sidenavRight);

                this.headerService.setHeader(this.lang.contactModification);

                this.creationMode = false;

                this.contactId = params['id'];

                this.loading = false;
            }
        });
    }

    goToList() {
        this.router.navigate(["/administration/contacts/list"]);
    }
}
