import { Component, OnInit} from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;
@Component({
    templateUrl : angularGlobals['parameter-administrationView'],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class ParameterAdministrationComponent implements OnInit {
    coreUrl                 : string;
    pageTitle               : string;
    creationMode            : boolean       = true;
    type                    : string;
    parameter               : any           = {};
    paramDateTemp           : string;
    lang                    : any           = "";

    resultInfo              : string        = "";
    loading                 : boolean       = false;

    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void {
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareParameter();
        this.updateBreadcrumb(angularGlobals.applicationName);

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined"){
                this.creationMode = true;

                this.http.get(this.coreUrl + 'rest/administration/parameters/new')
                    .map(res => res.json())
                    .subscribe((data) => {
                        this.lang = data.lang;
                        this.type = 'string';
                        this.pageTitle = this.lang.newParameter;

                        this.loading = false;
                        setTimeout(() => {
                            //$j("select").chosen({width:"100%",disable_search_threshold: 10, search_contains: true}); 
                        }, 0);
                }, () => {
                    location.href = "index.php";
                });
            } else {
                this.creationMode = false;
                this.http.get(this.coreUrl + 'rest/administration/parameters/'+params['id'])
                    .map(res => res.json())
                    .subscribe((data) => {
                        this.parameter = data.parameter;
                        this.lang = data.lang
                        this.type = data.type;

                        this.loading = false;
                        setTimeout(() => {
                            //$j("select").chosen({width:"100%",disable_search_threshold: 10, search_contains: true});   
                        }, 0);

                    }, () => {
                        location.href = "index.php";
                    }); 
            }
        });
               
    }

    prepareParameter() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/parameters\"' style='cursor: pointer'>Paramètres</a> > Modification";
        }
    }

    
    onSubmit() {
        if(this.type=='date'){
            this.parameter.param_value_date = $j("#paramValue").val();
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

        /*if(this.mode == 'create'){
            this.http.post(this.coreUrl + 'rest/parameters', this.parameter)
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors) {
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                    this.parameter.param_value_date=null;
                    this.parameter.param_value_int=null;
                    this.parameter.param_value_string=null;
                } else {
                    this.resultInfo = this.lang.paramCreatedSuccess;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                    this.router.navigate(['administration/parameters']);
                }
                
            });
        } else if(this.mode == "update"){

            this.http.put(this.coreUrl+'rest/parameters/'+this.paramId,this.parameter)
            .map(res => res.json())             
            .subscribe((data) => {
                if(data.errors){
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {
                    this.resultInfo = this.lang.paramUpdatedSuccess;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                    this.router.navigate(['administration/parameters']);                    
                }
            });
        }*/
    }
}
