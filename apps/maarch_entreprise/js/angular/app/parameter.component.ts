import { Component, OnInit} from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;
@Component({
    templateUrl : angularGlobals.parameterView,
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css','css/parameter.component.css']
})
export class ParameterComponent implements OnInit {
    coreUrl          : string;
    pageTitle       : string ;
    mode            : string = null;
    paramId         : string;
    type            : string;
    parametersList : any = null;
    parameter   : any   = {
        id                  : null,
        param_value_string  : null,
        param_value_int     : null,
        param_value_date    : null,
        description         : null
    };
    paramDateTemp   :string;
    lang        : any = "";

    resultInfo : string = "";


    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareParameter();
        this.http.get(this.coreUrl + 'rest/parameters/lang')
        .map(res => res.json())
        .subscribe((data) => {
            this.lang = data;
        });

        this.route.params.subscribe((params) => {
            if(this.route.toString().includes('update')){
                this.mode='update';
                this.paramId = params['id'];
                this.getParameterInfos(this.paramId);                
            } else if (this.route.toString().includes('create')){
                this.mode = 'create';
                this.pageTitle = '<i class=\"fa fa-wrench fa-2x\"></i> Paramètre';
                $j('#pageTitle').html(this.pageTitle);
                this.type = 'string';
            }
        });
               
    }

    prepareParameter() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
        $j('#ariane').html("<a href='index.php?reinit=true'>" + applicationName + "</a> ><a href='index.php?page=admin&reinit=true'> Administration</a> > Paramètres");
    }

    getParameterInfos(paramId : string){
        this.http.get(this.coreUrl + 'rest/parameters/'+paramId)
                .map(res => res.json())
                .subscribe((data) => {
                    if(data.errors){
                        this.resultInfo = data.errors;
                        $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                    } else{
                            var infoParam=data; 
                            this.parameter.id = infoParam[0].id;
                            if(infoParam[0].param_value_string != null){
                                this.parameter.param_value_string = infoParam[0].param_value_string
                                this.type = "string";
                            } else if(infoParam[0].param_value_int != null){
                                this.parameter.param_value_int = infoParam[0].param_value_int;
                                this.type = "int";
                            } else if(infoParam[0].param_value_date != null) {
                                this.parameter.param_value_date = infoParam[0].param_value_date;
                                this.type = "date";
                            }
                            this.parameter.description = infoParam[0].description;
                            this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i> Paramètre : "+this.parameter.id;
                            $j('#pageTitle').html(this.pageTitle);
                        }
                    });                
    }
    
    submitParameter() {

        if(this.mode == 'create'){
            if(this.type=='date'){
                //Résolution bug calendrier
                this.parameter.param_value_date = $j("#param_value_date").val();
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
            if(this.type=='date'){
                this.parameter.param_value_date = $j("#param_value_date").val();
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
        }
    }

}
