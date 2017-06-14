import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;
var parametersDataTable : any;
@Component({
    templateUrl : 'Views/parameters.component.html',
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css','css/parameter.component.css']
})
export class ParametersComponent implements OnInit {
    coreUrl         : string;
    nbParameters    : number;
    pageTitle       : string ;
    lang            : any   = "";
    parametersList  : any;

    resultInfo = "";


    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareParameter();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.http.get(this.coreUrl + 'rest/parameters')
                .map(res => res.json())
                .subscribe((data) => {
                    if(data.errors){
                        $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                    } else {                        
                        this.parametersList=data.parametersList;
                        this.lang = data.lang;
                        var list = this.parametersList;
                        this.nbParameters = Object.keys(this.parametersList).length;                
                        this.pageTitle = this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i>" + this.lang.parameter + "s : "+this.nbParameters+" "+this.lang.parameter+"(s)";
                        $j('#pageTitle').html(this.pageTitle);
                        var test= this.parametersList;
                        var tempLang = this.lang;
                        setTimeout(function() {
                            parametersDataTable = $j('#paramsTable').DataTable({
                                    "language": {
                                        "lengthMenu":   tempLang.display + " _MENU_ " + tempLang.recordsPerPage,
                                        "zeroRecords": tempLang.noRecords,
                                        "info": tempLang.page + " _PAGE_ "+ tempLang.outOf +" _PAGES_",
                                        "infoEmpty": tempLang.noRecords + " " + tempLang.available,
                                        "infoFiltered": "(" + tempLang.filteredFrom + " _MAX_ " + tempLang.records + ")",
                                        "search" : tempLang.search,
                                        "paginate" : {
                                            "first" : tempLang.first,
                                            "last"  : tempLang.last,
                                            "next"  : tempLang.next,
                                            "previous" : tempLang.previous
                                        }
                                    }
                                }
                            );
                        }, 0);
                    }
                });
    }
    
    prepareParameter() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
        $j('#ariane').html("<a href='index.php?reinit=true'>" + applicationName + "</a> ><a href='index.php?page=admin&reinit=true'> Administration</a> > Paramètres");
    }

    deleteParameter(paramId : string){
        var resp =confirm(this.lang.deleteConfirm+' '+paramId+'?');
        if(!resp){
            return;
        }
        else{
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
                        var list = this.parametersList;
                        for(var i = 0; i<list.length;i++){
                            if(list[i].id==paramId){
                                list.splice(i,1);
                            }
                        }
                        parametersDataTable.row($j("#"+paramId)).remove().draw();
                        this.resultInfo = "Paramètre supprimé avec succès";
                    
                        $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                                $j("#resultInfo").slideUp(500);
                            });
                        this.nbParameters = Object.keys(this.parametersList).length;                
                        this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i>" + this.lang.parameter + "s : "+this.nbParameters+" "+this.lang.parameter+"(s)";
                        $j('#pageTitle').html(this.pageTitle);
                    }
                });
        }
    }

 
}
