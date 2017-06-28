import { Component, OnInit} from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;
@Component({
    templateUrl : angularGlobals['status-administrationView'],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class StatusAdministrationComponent implements OnInit {
    coreUrl         : string;
    pageTitle       : string = "" ;
    mode            : string = null;
    statusId        : string;
    type            : string;
    status   : any   = {
        id              : null,
        description     : null,
        can_be_searched : null,
        can_be_modified : null,
        is_folder_status : null,
        img_related     : null
    };
    paramDateTemp   : string;
    lang        : any = "";

    resultInfo : string = "";


    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareStatus();

        this.route.params.subscribe((params) => {
            if(this.route.toString().includes('update')){
                this.mode     = 'update';
                this.statusId = params['id'];
                this.getStatusInfos(this.statusId);                
            } else if (this.route.toString().includes('create')){
                this.http.get(this.coreUrl + 'rest/status/lang')
                .map(res => res.json())
                .subscribe((data) => {
                    this.lang      = data;
                    this.mode      = 'create';
                    this.pageTitle = this.lang.newStatus;
                });
            }
        });

    }

    prepareStatus() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
        $j('#ariane').html("<a href='index.php?reinit=true'>" + applicationName + "</a> ><a href='index.php?page=admin&reinit=true'> Administration des statuts</a>");
    }

    getStatusInfos(statusId : string){
        this.http.get(this.coreUrl + 'rest/status/'+statusId)
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors){
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {
                    this.status    = data['status'][0];
                    this.lang      = data['lang'];
                    this.pageTitle = this.lang.modify_status + ' : ' + this.status.id;
                }
            });                
    }
    
    submitStatus() {

        if(this.mode == 'create'){
            this.http.post(this.coreUrl + 'rest/status', this.status)
            .map(res => res.json())
            .subscribe((data) => {
                if(data.errors) {
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {
                    this.resultInfo = this.lang.paramCreatedSuccess;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                    this.router.navigate(['administration/status']);
                }
                
            });
        } else if(this.mode == "update"){

            this.http.put(this.coreUrl+'rest/status/'+this.statusId, this.status)
            .map(res => res.json())             
            .subscribe((data) => {
                if(data.errors){
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {
                    this.resultInfo = this.lang.paramUpdatedSuccess;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                    this.router.navigate(['administration/status']);                    
                }
            });
        }
    }

}
