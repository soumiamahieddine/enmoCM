import { Component, OnInit, NgZone } from '@angular/core';
import { HttpClient } from '@angular/common/http';

declare function $j (selector: any) : any;
declare var angularGlobals : any;

@Component({
    templateUrl : 'Views/reports.component.html',   
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', '../maarch_entreprise/css/reports.css']
})
export class ReportsComponent implements OnInit 
{

    test42          : string = "Ptit test OKLM";
    label           : string;
    coreUrl         : string;
    groups          : any;
    checkboxes      : any;
    indexGroups     : any;
    tabId           : any;
    arrayArgsPut    : any = [];
    lang            : any = [];

    constructor(public http: HttpClient) {
    }

    prepareState() {
        $j('#inner_content').remove();
     }

     ngOnInit(): void {
        this.prepareState();
        this.coreUrl = angularGlobals.coreUrl;

        this.http.get(this.coreUrl + 'rest/report/groups')
            .subscribe((data) => {
                this.groups = data['group'];
                this.lang = data['lang']; 
            });
    }

    loadGroup() { 
        this.http.get(this.coreUrl + 'rest/report/groups/'+ this.groups[$j("#group_id").prop("selectedIndex") - 1].group_id) // SELECTED ANDGULAR  .selected()
            .subscribe((data) => {
                this.checkboxes = data;
                console.log(this.checkboxes[0].id);     
            });
        $j("#formCategoryId").removeClass("hide");
    }

    clickOnCategory(id:any) {
        $j(".category").addClass("hide");
        $j("#"+id).removeClass("hide");
    }

    updateDB() {
        for(var i = 0; i<$j(":checkbox").length;i++) {
            this.arrayArgsPut.push ({id : this.checkboxes[i].id, checked : $j(":checkbox")[i].checked});
        }
        console.log(this.arrayArgsPut);    
        this.http.put(this.coreUrl + 'rest/report/groups/'+this.groups[$j("#group_id").prop("selectedIndex") - 1].group_id, this.arrayArgsPut) // SELECTED ANDGULAR  .selected()
            .subscribe((data) => {
                this.arrayArgsPut = [];
            });
    }   
}