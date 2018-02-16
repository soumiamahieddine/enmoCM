import { Component, OnInit} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals['parameter-administrationView'],
    providers   : [NotificationService]
})
export class ParameterAdministrationComponent implements OnInit {
    coreUrl                 : string;
    lang                    : any       = LANG;

    type                    : string;
    parameter               : any       = {};

    creationMode            : boolean;
    loading                 : boolean   = false;


    constructor(public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/parameters\"' style='cursor: pointer'>" + this.lang.parameters + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.parameterCreation;
        } else {
            breadCrumb += this.lang.parameterModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined"){
                this.creationMode = true;
                this.updateBreadcrumb(angularGlobals.applicationName);
                this.loading = false;
            } else {
                this.creationMode = false;
                this.http.get(this.coreUrl + "rest/parameters/" + params['id'])
                    .subscribe((data : any) => {
                        this.parameter = data.parameter;
                        this.updateBreadcrumb(angularGlobals.applicationName);

                        if (this.parameter.param_value_int) {
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
        if(this.type == 'date'){
            this.parameter.param_value_int=null;
            this.parameter.param_value_string=null;
        }
        else if(this.type == 'int'){
            this.parameter.param_value_date=null;
            this.parameter.param_value_string=null;
        }
        else if (this.type == 'string'){
            this.parameter.param_value_date=null;
            this.parameter.param_value_int=null;
        }

        if(this.creationMode == true){
            this.http.post(this.coreUrl + 'rest/parameters', this.parameter)
                .subscribe((data : any) => {
                    this.router.navigate(['administration/parameters']);
                    this.notify.success(this.lang.parameterAdded);
                },(err) => {
                    this.notify.error(err.error.errors);
                });
        } else if(this.creationMode == false){
            this.http.put(this.coreUrl + 'rest/parameters/' + this.parameter.id, this.parameter)
                .subscribe((data : any) => {
                    this.router.navigate(['administration/parameters']);
                    this.notify.success(this.lang.parameterUpdated);
                },(err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
