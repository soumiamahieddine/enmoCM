import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../../service/app.service';
import { FunctionsService } from '../../../service/functions.service';
import { HistoryComponent } from '../../history/history.component';
import { PrivilegeService } from '../../../service/privileges.service';

@Component({
    selector: 'contact-list',
    templateUrl: "history-administration.component.html",
    styleUrls: ['history-administration.component.scss'],
    providers: [AppService]
})
export class HistoryAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;

    lang: any = LANG;

    startDateFilter: any = '';
    endDateFilter: any = '';

    @ViewChild('appHistoryList', { static: false }) appHistoryList: HistoryComponent;

    subMenus: any[] = [
        {
            icon: 'fa fa-history',
            route: '/administration/history',
            label: this.lang.history,
            current: true
        },
        {
            icon: 'fa fa-history',
            route: '/administration/history-batch',
            label: this.lang.historyBatch,
            current: false
        }
    ];

    constructor(
        public http: HttpClient,
        public appService: AppService,
        public functions: FunctionsService,
        private privilegeService: PrivilegeService) { }

    ngOnInit(): void {
        if (this.privilegeService.hasCurrentUserPrivilege('view_history_batch')) {
            this.subMenus = [
                {
                    icon: 'fa fa-history',
                    route: '/administration/history',
                    label: this.lang.history,
                    current: true
                },
                {
                    icon: 'fa fa-history',
                    route: '/administration/history-batch',
                    label: this.lang.historyBatch,
                    current: false
                }
            ];
        } else {
            this.subMenus = [
                {
                    icon: 'fa fa-history',
                    route: '/administration/history',
                    label: this.lang.historyBatch,
                    current: true
                }
            ];
        }
    }
}