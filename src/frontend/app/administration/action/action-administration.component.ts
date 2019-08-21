import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatSidenav } from '@angular/material/sidenav';
import { HeaderService }        from '../../../service/header.service';
import { AppService } from '../../../service/app.service';

declare function $j(selector: any): any;

@Component({
    templateUrl: "action-administration.component.html",
    providers: [NotificationService, AppService]
})
export class ActionAdministrationComponent implements OnInit {

    /*HEADER*/
    @ViewChild('snav', { static: true }) public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight  : MatSidenav;

    lang: any = LANG;
    creationMode: boolean;
    action: any = {};
    statuses: any[] = [];
    actionPages: any[] = [];
    categoriesList: any[] = [];
    keywordsList: any[] = [];

    loading: boolean = false;

    constructor(
        public http: HttpClient, 
        private route: ActivatedRoute, 
        private router: Router, 
        private notify: NotificationService, 
        private headerService: HeaderService,
        public appService: AppService) {
        $j("link[href='merged_css.php']").remove();
    }

    ngOnInit(): void {
        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = true;

                this.http.get('../../rest/initAction')
                    .subscribe((data: any) => {
                        this.action = data.action;
                        this.categoriesList = data.categoriesList;
                        this.statuses = data.statuses;

                        this.actionPages = data['actionPages'];
                        this.keywordsList = data.keywordsList;
                        this.headerService.setHeader(this.lang.actionCreation);
                        this.loading = false;
                    });
            }
            else {
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);
                
                this.creationMode = false;

                this.http.get('../../rest/actions/' + params['id'])
                    .subscribe((data: any) => {
                        this.action = data.action;
                        this.categoriesList = data.categoriesList;
                        this.statuses = data.statuses;
                        this.actionPages = data['actionPages'];
                        this.keywordsList = data.keywordsList;
                        this.headerService.setHeader(this.lang.actionCreation, data.action.label_action);
                        this.loading = false;
                    });
            }
        });
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post('../../rest/actions', this.action)
                .subscribe(() => {
                    this.router.navigate(['/administration/actions']);
                    this.notify.success(this.lang.actionAdded);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put('../../rest/actions/' + this.action.id, this.action)
                .subscribe(() => {
                    this.router.navigate(['/administration/actions']);
                    this.notify.success(this.lang.actionUpdated);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}