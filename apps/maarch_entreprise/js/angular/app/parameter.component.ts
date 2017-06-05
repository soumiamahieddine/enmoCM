import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;
@Component({
    templateUrl : 'Views/parameter.component.html',
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class ParameterComponent implements OnInit {
    coreUrl                     : string;
    test                     : string = 'test';
    
    mode        : string = "update";
    paramId     : string;
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
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareParameter();
        this.route.params.subscribe(params => {
            this.paramId = params['id'];
            if(this.paramId!=null){
            this.http.get(this.coreUrl + 'rest/parameters/'+this.paramId)
            .map(res => res.json())
            .subscribe((data) => {
                if(data.error){
                    console.log(data.errors)
                    return;
                }

                this.parameter.id=data.id;
            })
        }
        });
        
        
    }

    prepareParameter() {
        console.log("PREPARE");
        $j('#inner_content').remove();
    }

    submitParameter(){
        console.log(this.parameter);
        this.http.post(this.coreUrl + 'rest/parameters', this.parameter)
        .map(res => res.json())
        .subscribe((data) => {
            if(data.errors) {
                this.resultInfo = data.errors
                console.log(this.resultInfo);
            } else {
                console.log('SUCCES');
            }
        });;
    }

}
