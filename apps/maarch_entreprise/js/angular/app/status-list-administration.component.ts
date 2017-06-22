import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;
var statusDataTable : any;
@Component({
    templateUrl : angularGlobals['status-list-administrationView'],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class StatusListAdministrationComponent implements OnInit {
    coreUrl         : string;
    nbStatus        : number;
    pageTitle       : string ;
    lang            : any   = "";
    statusList      : any;

    resultInfo :string = "";


    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareStatus();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.http.get(this.coreUrl + 'rest/status')
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors){
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {                        
                    this.statusList = data.statusList;
                    this.lang       = data.lang;
                    this.nbStatus = Object.keys(this.statusList).length;                
                    this.pageTitle = this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i>" + this.lang.status + "s : "+this.nbStatus+" "+this.lang.status+"(s)";
                    $j('#pageTitle').html(this.pageTitle);
                    var test= this.statusList;
                    var tempLang = this.lang;
                    setTimeout(function() {
                        statusDataTable = $j('#paramsTable').DataTable({
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
    
    prepareStatus() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
        $j('#ariane').html("<a href='index.php?reinit=true'>" + applicationName + "</a> ><a href='index.php?page=admin&reinit=true'> Administration</a> > Statuts");
    }

    deleteStatus(statusId : string){
        var resp =confirm(this.lang.deleteConfirm+' '+statusId+'?');
        if(resp){
            this.http.delete(this.coreUrl + 'rest/status/'+statusId)
                .map(res => res.json())
                .subscribe((data) => {
                    if(data.errors){
                        this.resultInfo = data.errors;
                        $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                    } else{
                        var list = this.statusList;
                        for(var i = 0; i<list.length;i++){
                            if(list[i].id==statusId){
                                list.splice(i, 1);
                            }
                        }
                        statusDataTable.row($j("#"+statusId)).remove().draw();
                        this.resultInfo = "Statut supprimé avec succès";
                    
                        $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                        this.nbStatus = Object.keys(this.statusList).length;                
                        this.pageTitle = "<i class=\"fa fa-wrench fa-2x\"></i>" + this.lang.parameter + "s : "+this.nbStatus+" "+this.lang.parameter+"(s)";
                        $j('#pageTitle').html(this.pageTitle);
                    }
                });
        }
    }

 
}
