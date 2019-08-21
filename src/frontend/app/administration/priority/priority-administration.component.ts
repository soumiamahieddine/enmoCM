import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatSidenav } from '@angular/material/sidenav';
import { HeaderService } from '../../../service/header.service';
import { AppService } from '../../../service/app.service';

declare function $j(selector: any): any;

@Component({
    templateUrl: "priority-administration.component.html",
    providers: [NotificationService, AppService]
})
export class PriorityAdministrationComponent implements OnInit {

    /*HEADER*/
    @ViewChild('snav', { static: false }) public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2', { static: false }) public sidenavRight  : MatSidenav;
    
    id              : string;
    creationMode    : boolean;
    lang            : any       = LANG;
    loading         : boolean   = false;

    priority        : any       = {
        useDoctypeDelay : false,
        color           : "#135f7f",
        delays          : "0",
        working_days    : "false",
        default_priority: false
    };

    constructor( 
        public http: HttpClient, 
        private route: ActivatedRoute, 
        private router: Router, 
        private notify: NotificationService, 
        private headerService: HeaderService,
        public appService: AppService
    ) {
        $j("link[href='merged_css.php']").remove();
    }

    ngOnInit(): void {
        this.loading = true;

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined") {
                this.headerService.setHeader(this.lang.priorityCreation);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = true;
                this.loading = false;
            } else {
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = false;
                this.id = params['id'];
                this.http.get("../../rest/priorities/" + this.id)
                    .subscribe((data: any) => {
                        this.priority = data.priority;
                        this.headerService.setHeader(this.lang.priorityModification, this.priority.label);
                        this.priority.useDoctypeDelay = this.priority.delays != null;
                        if (this.priority.working_days === true) {
                            this.priority.working_days = "true";
                        } else {
                            this.priority.working_days = "false";
                        }
                        this.loading = false;
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    onSubmit() {
        if (this.priority.useDoctypeDelay == false) {
            this.priority.delays = null;
        }
        this.priority.working_days = this.priority.working_days == "true";
        if (this.creationMode) {
            this.http.post("../../rest/priorities", this.priority)
                .subscribe(() => {
                    this.notify.success(this.lang.priorityAdded);
                    this.router.navigate(["/administration/priorities"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put("../../rest/priorities/" + this.id, this.priority)
                .subscribe(() => {
                    this.notify.success(this.lang.priorityUpdated);
                    this.router.navigate(["/administration/priorities"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
