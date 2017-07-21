import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { LANG } from './translate.component';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["actions-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})

export class ActionsAdministrationComponent implements OnInit {
    coreUrl                 : string;
    lang                    : any           = LANG;

    actions                 : any[]         = [];
    titles                  : any[]         = [];
    table                   : any;

    resultInfo              : string        = "";
    loading                 : boolean       = false;

    constructor(public http: Http) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Actions";
        }
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        
        this.loading = true;

        this.updateBreadcrumb(angularGlobals.applicationName);
        $j('#inner_content').remove();

        this.http.get(this.coreUrl + 'rest/administration/actions')
            .map(res => res.json())
            .subscribe((data) => {
                this.actions = data['actions'];
                this.titles = data['titles'];
                setTimeout(() => {
                    this.table = $j('#actionsTable').DataTable({
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
                        "order": [[ 1, "asc" ]],
                        "columnDefs": [
                            { "orderable": false, "targets": 4 }
                        ],
                        "fnInitComplete": function () {
                            $j('#actionsTable').show();
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
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }

    deleteAction(id: number) {
        let r = confirm(this.lang.deleteMsg+' ?');

        if (r) {
            this.http.delete(this.coreUrl + 'rest/actions/' + id)
                .map(res => res.json())
                .subscribe((data) => {
                    var list = this.actions;
                    for(var i = 0; i<list.length;i++){
                        if(list[i].id==id){
                            list.splice(i,1);
                        }
                    }
                    this.table.row($j("#"+id)).remove().draw();
                    successNotification(data.success);
                    
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        }
    }

}