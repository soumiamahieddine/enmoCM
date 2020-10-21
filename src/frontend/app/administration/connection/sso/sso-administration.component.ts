import { HttpClient } from '@angular/common/http';
import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '@service/app.service';
import { HeaderService } from '@service/header.service';
import { NotificationService } from '@service/notification/notification.service';
import { AdministrationService } from '../../administration.service';

@Component({
    selector: 'app-sso-administration',
    templateUrl: './sso-administration.component.html',
    styleUrls: ['./sso-administration.component.scss']
})
export class SsoAdministrationComponent implements OnInit {

    loading: boolean = true;

    @ViewChild('adminMenuTemplate', { static: true }) adminMenuTemplate: TemplateRef<any>;
    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: false }) sort: MatSort;

    subMenus: any[] = [
        {
            icon: 'fas fa-users-cog',
            route: '/administration/sso',
            label: this.translate.instant('lang.sso'),
            current: true
        }
    ];

    mapping: any[] = [
        {
            maarchId: 'userId',
            ssoId: 'id',
            separator: null
        },
        {
            maarchId: 'userName',
            ssoId: 'uid',
            separator: null
        },
        {
            maarchId: 'email',
            ssoId: 'codeunite',
            separator: null
        },
        {
            maarchId: 'groupId',
            ssoId: 'qualification',
            separator: ','
        },
        {
            maarchId: 'entityId',
            ssoId: 'departement_uid',
            separator: ','
        },
    ];

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        public appService: AppService,
        private headerService: HeaderService,
        private viewContainerRef: ViewContainerRef,
        public adminService: AdministrationService,
    ) { }

    ngOnInit(): void {
        this.headerService.injectInSideBarLeft(this.adminMenuTemplate, this.viewContainerRef, 'adminMenu');
        this.headerService.setHeader(this.translate.instant('lang.administration') + ' ' + this.translate.instant('lang.ssoConnections'));
        this.getConnection();
    }

    getConnection() {
        this.loading = false;
    }

}
