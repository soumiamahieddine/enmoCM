import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare var angularGlobals : any;

@Component({
    templateUrl : angularGlobals["parameters-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css','css/parameters-administration.component.css']
})

export class ParametersAdministrationComponent implements OnInit {
    coreUrl         : string;

    parametersList  : any;
    pageTitle       : string;
    lang            : any           = "";
    table           : any;
    

    resultInfo      : string        = "";
    loading         : boolean       = false;


    constructor(public http: Http) {
    }

    prepareParameter() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Paramètres";
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        
        this.prepareParameter();
        this.updateBreadcrumb(angularGlobals.applicationName);

        this.http.get(this.coreUrl + 'rest/administration/parameters')
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors){
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {                  
                    this.parametersList = data.parametersList;
                    this.lang = data.lang;
                    setTimeout(() => {
                    this.table = $j('#paramsTable').DataTable({
                        "dom": '<"datatablesLeft"l><"datatablesRight"f><"datatablesCenter"p>rt<"datatablesCenter"i><"clear">',
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
                        "order": [[ 0, "asc" ]],
                        "columnDefs": [
                            { "orderable": false, "targets": 3 }
                        ],
                        "fnInitComplete": function () {
                            $j('#paramsTable').show();
                        },
                        stateSave: true
                    });
                    $j('.dataTables_filter input').attr("placeholder", this.lang.search);
                    $j('dataTables_filter input').addClass('form-control');
                    $j(".datatablesLeft").css({"float":"left"});
                    $j(".datatablesCenter").css({"text-align":"center"});
                    $j(".datatablesRight").css({"float":"right"});      

                }, 0);
                this.loading = false;
                }
            });
    }

    goUrl(){
        location.href = 'index.php?admin=parameters&page=control_param_technic';
    }

    deleteParameter(paramId : string){
        var resp =confirm(this.lang.deleteConfirm+' '+paramId+'?');
        if (resp) {
            this.http.delete(this.coreUrl + 'rest/parameters/'+paramId)
                .map(res => res.json())
                .subscribe((data) => {
                    for(var i = 0; i<this.parametersList.length;i++){
                        if(this.parametersList[i].id==paramId){
                            this.parametersList.splice(i,1);
                        }
                    }
                    this.table.row($j("#"+paramId)).remove().draw();
                    successNotification(data.success);               
                },(err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        }
    }
 
}
