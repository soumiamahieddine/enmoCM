import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;
declare var tinymce : any;
declare var Prototype : any;
declare var angularGlobals : any;


@Component({
    templateUrl : 'Views/actions.component.html',
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})

export class ActionsComponent implements OnInit {
    coreUrl: string;
    resultInfo: string;
    actions: any[] = [];
    titles:  any[] = [];
    lang  : any[] = [];
    actionsDataTables : any ;
    loading                     : boolean   = false;

    constructor(public http: Http) {
        this.coreUrl = angularGlobals.coreUrl;
    }

    prepareActions() {
        $j('#inner_content').remove();
        this.http.get(this.coreUrl + 'rest/administration/actions')
            .map(res => res.json())
            .subscribe((data) => {
                this.actions = data['actions'];
                this.titles = data['titles'];
                this.lang= data['lang'];
                setTimeout(() => {
                    this.actionsDataTables=$j('#actionsTable').DataTable({
                        "language": {
                            "lengthMenu":   this.lang.display + " _MENU_ " + this.lang.recordsPerPage,
                            "zeroRecords": this.lang.noRecords,
                            "info": this.lang.page + " _PAGE_ "+ this.lang.outOf +" _PAGES_",
                            "infoEmpty": this.lang.noRecords + " " + this.lang.available,
                            "infoFiltered": "(" + this.lang.filteredFrom + " _MAX_ " + this.lang.records + ")",
                            "search" : this.lang.search,
                            "paginate" : {
                                "first" : this.lang.first,
                                "last"  : this.lang.last,
                                "next"  : this.lang.next,
                                "previous" : this.lang.previous
                            }
                        }
                    });
                }, 0);
                this.loading = false;
            });


    }

    ngOnInit(): void {
        this.loading = true;
        this.prepareActions();
    }

    deleteAction(id: number) {
        this.http.delete(this.coreUrl + 'rest/actions/' + id, this.actions)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {

                }
                else
                {
                    var list = this.actions;
                    for(var i = 0; i<list.length;i++){
                        if(list[i].id==id){
                            list.splice(i,1);
                        }
                    }
                    this.actionsDataTables.row($j("#"+id)).remove().draw();
                    this.resultInfo = this.lang.delete_action;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                }
            });

    }

}