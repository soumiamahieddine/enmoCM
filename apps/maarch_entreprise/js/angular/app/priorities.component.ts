import { Component, OnInit} from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;
var prioritiesDataTable : any;

@Component({
    templateUrl : angularGlobals.prioritiesView,
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css','css/parameter.component.css']
})

export class PrioritiesComponent implements OnInit {
    coreUrl         :string;
    prioritiesList  :any;
    resultInfo      :string = "";
    prioritiesDataTable :any;

    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {

    }

    ngOnInit(): void{
        this.coreUrl = angularGlobals.coreUrl;
        this.preparePriorities();
        this.http.get(this.coreUrl + 'rest/priorities')
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors){
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {
                    this.prioritiesList = data.prioritiesList;
                    setTimeout(function(){
                        prioritiesDataTable = $j('#prioritiesTable').DataTable();
                    } ,0);
                }
            })
    }

    deletePriority(priorityId: string){
        var resp = confirm('Confirmer?');
        if(resp){
            var intId = parseInt(priorityId);
            this.http.delete(this.coreUrl + 'rest/priorities/'+intId)
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors){
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                                    $j("#resultInfo").slideUp(500);
                    });
                } else {
                    var list = this.prioritiesList;
                    for(var i=0;i<list.length;i++){
                        if(list[i].id==priorityId){
                            list.splice(i,1)
                        }                        
                    }
                    prioritiesDataTable.row($j('#'+priorityId)).remove().draw();
                }
            })
        }
    }

    preparePriorities() {
        $j('#inner_content').remove();
    }
}