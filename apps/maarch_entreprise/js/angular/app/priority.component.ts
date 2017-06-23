import { Component, OnInit} from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;
@Component({
    templateUrl : angularGlobals.priorityView,
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css','css/parameter.component.css']
})

export class PriorityComponent implements OnInit {
    coreUrl     :string;
    
    mode        :string = null;
    priority    :any = {
        id              :null,
        label_priority  :null,
        color_priority  :'#ffffff',
        working_days    :'Y',
        delays          :'*'
    };
    priorityId  :any;
    resultInfo  :string;

    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void{
        this.coreUrl = angularGlobals.coreUrl;
        this.preparePriority();
        this.route.params.subscribe((params) => {
            if(this.route.toString().includes('update')){
                this.mode='update';
                
                this.priorityId = params['id'];
                this.getPriorityInfos(this.priorityId);                
            } else if (this.route.toString().includes('create')){
                this.mode = 'create';
            }
        });
    }

    preparePriority() {
        $j('#inner_content').remove();
    }

    getPriorityInfos(priorityId : string){
        var intId = parseInt(priorityId);
        this.http.get(this.coreUrl + 'rest/priorities/'+intId)
                .map(res => res.json())
                .subscribe((data) => {
                    if(data.errors){
                        console.log("errors");
                        this.resultInfo = data.errors;
                        $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                    } else{

                            var infoPriority=data; 
                            this.priority.id = infoPriority[0].id;
                            this.priority.label_priority = infoPriority[0].label_priority;
                            this.priority.color_priority = infoPriority[0].color_priority;
                            this.priority.working_days = infoPriority[0].working_days;
                            this.priority.delays = infoPriority[0].delays;
                        }
                    });
    }

    submitPriority(){
        if(this.mode=='create'){
            this.http.post(this.coreUrl + 'rest/priorities', this.priority)
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors){
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else{
                    this.resultInfo = 'Priorité créée avec succès';
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                        this.router.navigate(['administration/priorities'])
                }
            });
        } else if(this.mode=='update'){
            this.http.put(this.coreUrl + 'rest/priorities/'+this.priorityId, this.priority)
            .map( res => res.json())
            .subscribe((data) => {
                if(data.errors){
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {
                    this.resultInfo = 'Priorité mise à jour avec succès';
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                        this.router.navigate(['administration/priorities'])
                }
            });
        }
    }


}