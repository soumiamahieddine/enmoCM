import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService }        from '../../../service/header.service';
import { MatSidenav } from '@angular/material';
import { AppService } from '../../../service/app.service';

declare function $j(selector: any): any;

@Component({
    templateUrl: "parameter-administration.component.html",
    providers: [NotificationService, AppService]
})
export class ParameterAdministrationComponent implements OnInit {

    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    lang                            : any       = LANG;
    loading                         : boolean   = false;

    parameter                       : any       = {};
    type                            : string;
    creationMode                    : boolean;


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
                this.headerService.setHeader(this.lang.parameterCreation);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = true;
                this.loading = false;
            } else {
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = false;
                this.http.get("../../rest/parameters/" + params['id'])
                    .subscribe((data: any) => {
                        this.parameter = data.parameter;
                        this.headerService.setHeader(this.lang.parameterModification, this.parameter.id);
                        if (typeof (this.parameter.param_value_int) == "number") {
                            this.type = "int";
                        } else if (this.parameter.param_value_date) {
                            this.type = "date";
                        } else {
                            this.type = "string";
                        }

                        this.loading = false;
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    onSubmit() {
        if (this.type == 'date') {
            this.parameter.param_value_int = null;
            this.parameter.param_value_string = null;
        }
        else if (this.type == 'int') {
            this.parameter.param_value_date = null;
            this.parameter.param_value_string = null;
        }
        else if (this.type == 'string') {
            this.parameter.param_value_date = null;
            this.parameter.param_value_int = null;
        }

        if (this.creationMode == true) {
            this.http.post('../../rest/parameters', this.parameter)
                .subscribe(() => {
                    this.router.navigate(['administration/parameters']);
                    this.notify.success(this.lang.parameterAdded);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else if (this.creationMode == false) {
            this.http.put('../../rest/parameters/' + this.parameter.id, this.parameter)
                .subscribe(() => {
                    this.router.navigate(['administration/parameters']);
                    this.notify.success(this.lang.parameterUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
