import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;

@Component({
    templateUrl : angularGlobals['statuses-administrationView'],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class StatusListAdministrationComponent implements OnInit {
    coreUrl                     : string;
    nbStatus                    : number;
    lang                        : any           = "";
    table                       : any
    statusList                  : any;

    resultInfo                  : string        = "";
    loading                     : boolean       = false;


    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.prepareStatus();
        this.updateBreadcrumb(angularGlobals.applicationName);

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/administration/status')
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
                    setTimeout(() => {
                    this.table = $j('#statusTable').DataTable({
                        "dom": '<"datatablesLeft"p><"datatablesRight"f><"datatablesCenter"l>rt<"datatablesCenter"i><"clear">',
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        "oLanguage": {
                            "sLengthMenu": "<i class='fa fa-bars'></i> _MENU_",
                            "sZeroRecords": this.lang.noResult,
                            "sInfo": "_START_ - _END_ / _TOTAL_ "+this.lang.record,
                            "sSearch": "",
                            "oPaginate": {
                                "sFirst":    "<<",
                                "sLast":    ">>",
                                "sNext":    this.lang.next+" <i class='fa fa-caret-right'></i>",
                                "sPrevious": "<i class='fa fa-caret-left'></i> "+this.lang.previous
                            },
                            "sInfoEmpty": this.lang.noRecord,
                            "sInfoFiltered": "(filtré de _MAX_ "+this.lang.record+")"
                        },
                        "order": [[ 2, "asc" ]],
                        "columnDefs": [
                            { "orderable": false, "targets": [0,3] }
                        ]
                    });
                    $j('.dataTables_filter input').attr("placeholder", this.lang.search);
                    $j('dataTables_filter input').addClass('form-control');
                    $j(".datatablesLeft").css({"float":"left"});
                    $j(".datatablesCenter").css({"text-align":"center"});
                    $j(".datatablesRight").css({"float":"right"});      

                }, 0);
                this.loading = false;
                }
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }
    
    prepareStatus() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
        $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Statuts";
    }

    deleteStatus(statusId : string, statusIdentifier : string){
        var resp = confirm(this.lang.deleteConfirm+' '+statusId+'?');
        if(resp){
            this.http.delete(this.coreUrl + 'rest/status/'+statusIdentifier)
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
                        this.table.row($j("#"+statusId)).remove().draw();
                        this.resultInfo = "Statut supprimé avec succès";
                    
                        $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                        this.nbStatus = Object.keys(this.statusList).length;
                    }
                });
        }
    }
 
}
