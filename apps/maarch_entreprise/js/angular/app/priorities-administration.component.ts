import { Component, OnInit} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare var angularGlobals : any;

@Component({
    templateUrl : angularGlobals["priorities-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class PrioritiesAdministrationComponent implements OnInit {

    coreUrl         : string;
    lang            : any       = LANG;
    loading         : boolean   = false;

    priorities      : any[]     = [];

    datatable       : any;


    constructor(public http: HttpClient) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Priorités";
        }
    }
    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.updateBreadcrumb(angularGlobals.applicationName);

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/priorities')
            .subscribe((data : any) => {
                this.priorities = data.priorities;
                //setTimeout(() => {
                //    this.datatable = $j('#prioritiesTable').DataTable({
                //        "dom": '<"datatablesLeft"p><"datatablesRight"f><"datatablesCenter"l>rt<"datatablesCenter"i><"clear">',
                //        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                //        "oLanguage": {
                //            "sLengthMenu": "<i class='fa fa-bars'></i> _MENU_",
                //            "sZeroRecords": this.lang.noResult,
                //            "sInfo": "_START_ - _END_ / _TOTAL_ "+this.lang.record,
                //            "sSearch": "",
                //            "oPaginate": {
                //                "sFirst":    "<<",
                //                "sLast":    ">>",
                //                "sNext":    this.lang.next+" <i class='fa fa-caret-right'></i>",
                //                "sPrevious": "<i class='fa fa-caret-left'></i> "+this.lang.previous
                //            },
                //            "sInfoEmpty": this.lang.noRecord,
                //            "sInfoFiltered": "(filtré de _MAX_ "+this.lang.record+")"
                //        },
                //        "order": [[ 1, "asc" ]],
                //        "columnDefs": [
                //            { "orderable": false, "targets": [3,5] }
                //        ]
                //    });
                //    $j('.dataTables_filter input').attr("placeholder", this.lang.search);
                //    $j('dataTables_filter input').addClass('form-control');
                //    $j(".datatablesLeft").css({"float":"left"});
                //    $j(".datatablesCenter").css({"text-align":"center"});
                //    $j(".datatablesRight").css({"float":"right"});
                //} ,0);

                this.loading = false;
            }, () => {
                location.href = "index.php";
            })
    }

    deletePriority(id: string) {
        let r = confirm("Voulez-vous vraiment supprimer cette priorité ?");

        if (r) {
            this.http.delete(this.coreUrl + "rest/priorities/" + id)
                .subscribe((data : any) => {
                    this.priorities = data.priorities;
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                })
        }
    }
}