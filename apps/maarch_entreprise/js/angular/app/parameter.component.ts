import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';
//import { Subject } from 'rxjs/Rx';

declare function $j(selector: any) : any;

declare var angularGlobals : any;
@Component({
    templateUrl : 'Views/parameter.component.html',
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class ParameterComponent implements OnInit {
    coreUrl                     : string;
    test                     : string = 'test';
    pageTitle   : string ;
    mode        : string = null;
    paramId     : string;
    type        : string;
    parametersList : any = null;
    parameter   : any   = {
        id                  : null,
        param_value_string  : null,
        param_value_int     : null,
        param_value_date    : null,
        description         : null

    }

    resultInfo = "";


    constructor(public http: Http, private route: ActivatedRoute) {
    }

    ngOnInit(): void {
        //this.dtOptions= {
        //};
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareParameter();
        
        this.route.params.subscribe((params) => {
            if(this.route.toString().includes('list')){
                this.changeMode('list');                
            }
        });        
    }

    loadParametersList(){
        this.http.get(this.coreUrl + 'rest/parameters')
                .subscribe((data) => {
                    this.parametersList=JSON.parse(data.text());
                    this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i> Paramètres : "+Object.keys(this.parametersList).length+" paramètre(s)";
                    $j('#pageTitle').html(this.pageTitle)
                    //this.dtTrigger.next();
                    setTimeout((function(){
                         $j('#paramsTable').DataTable();
                         
                    }),0);
                    this.parameter ={
                                    id                  : null,
                                    param_value_string  : null,
                                    param_value_int     : null,
                                    param_value_date    : null,
                                    description         : null
                                };
                });
                
                
    }

    prepareParameter() {
        $j('#inner_content').remove();
    }

    getParameterInfos(paramId : string){
        this.http.get(this.coreUrl + 'rest/parameters/'+paramId)
                .subscribe((data) => {
                            var infoParam=JSON.parse(data.text()); 
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
                            $j('#pageTitle').html(this.pageTitle)
                    });
    }
    
    changeMode(mode :string)
    {
        this.mode=mode;
        if(mode=='list'){
            this.loadParametersList();
        }
    }
    updateParameter(paramId: string){
        this.paramId=paramId;
        this.changeMode('update');
        this.getParameterInfos(paramId);
    }

    deleteParameter(paramId : string){
        this.http.delete(this.coreUrl + 'rest/parameters/'+paramId)
        .map(res => res.json())
        .subscribe((data) => {
            if(data.errors){
                this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
            } else{
                this.resultInfo = "Paramètre supprimé avec succès";
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                this.loadParametersList();
            }
        })
    }

    submitParameter() {
        if(this.mode == 'add'){
            this.http.post(this.coreUrl + 'rest/parameters', this.parameter)
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors) {
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {
                    this.resultInfo = "Paramètre créé avec succès";
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                    this.changeMode('list');
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
                    this.resultInfo = "Mise à jour effectuée";
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                    this.changeMode('list');                    
                }
            });
        }
    }

}
