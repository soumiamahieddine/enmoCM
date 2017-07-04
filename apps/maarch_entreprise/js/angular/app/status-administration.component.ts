import { Component, OnInit} from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare var angularGlobals : any;
@Component({
    templateUrl : angularGlobals['status-administrationView'],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/status-administration.component.css']
})
export class StatusAdministrationComponent implements OnInit {
    coreUrl             : string;
    pageTitle           : string            = "" ;
    mode                : string            = null;
    statusIdentifier    : string;
    status              : any               = {
                                                id              : null,
                                                label_status     : null,
                                                can_be_searched : null,
                                                can_be_modified : null,
                                                is_folder_status : null,
                                                img_filename     : null
                                            };
    lang                : any               = "";
    statusImages        : any               = "";

    loading             : boolean           = false;


    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void {
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareStatus();

        this.route.params.subscribe((params) => {
            if (this.route.toString().includes('status/new')){
                this.http.get(this.coreUrl + 'rest/status/new')
                .map(res => res.json())
                .subscribe((data) => {
                    this.lang         = data['lang'];
                    this.statusImages = data['statusImages'];
                    this.mode         = 'create';
                    this.pageTitle    = this.lang.newStatus;
                    this.updateBreadcrumb(angularGlobals.applicationName);
                });
            } else {
                this.mode     = 'update';
                this.statusIdentifier = params['identifier'];
                this.getStatusInfos(this.statusIdentifier);
            }
            setTimeout(() => {
                $j(".help").tooltipster({
                    theme: 'tooltipster-maarch',
                    interactive: true
                });
            }, 0);
        });
        this.loading = false;
    }

    prepareStatus() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > "+
                                        "<a onclick='location.hash = \"/administration\"' style='cursor: pointer'>"+this.lang.admin+"</a> > "+
                                        "<a onclick='location.hash = \"/administration/status\"' style='cursor: pointer'>"+this.lang.admin_status+"</a> > ";
        if(this.mode == 'create'){
            breadCrumb += this.lang.newItem;
        } else {
            breadCrumb += this.lang.modification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    }

    getStatusInfos(statusIdentifier : string){
        this.http.get(this.coreUrl + 'rest/status/'+statusIdentifier)
            .map(res => res.json())
            .subscribe((data) => {
                this.status    = data['status'][0];
                if(this.status.can_be_searched == 'Y'){
                    this.status.can_be_searched = true;
                }else{
                    this.status.can_be_searched = false;
                }
                if(this.status.can_be_modified == 'Y'){
                    this.status.can_be_modified = true;
                }else{
                    this.status.can_be_modified = false;
                }
                if(this.status.is_folder_status == 'Y'){
                    this.status.is_folder_status = true;
                }else{
                    this.status.is_folder_status = false;
                }
                this.lang         = data['lang'];
                this.statusImages = data['statusImages'];
                this.pageTitle    = this.lang.modify_status + ' : ' + this.status.id;
                this.updateBreadcrumb(angularGlobals.applicationName);
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });                
    }

    selectImage(image_name : string){
        this.status.img_filename = image_name;
    }
    
    submitStatus() {
        if(this.mode == 'create'){
            this.http.post(this.coreUrl + 'rest/status', this.status)
            .map(res => res.json())
            .subscribe((data) => {
                successNotification(this.lang.newStatusAdded + ' : ' + this.status.label_status);
                this.router.navigate(['administration/status']);
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });
        } else if(this.mode == "update"){

            this.http.put(this.coreUrl+'rest/status/'+this.statusIdentifier, this.status)
            .map(res => res.json())             
            .subscribe((data) => {
                successNotification(this.lang.statusUpdated + ' : ' + this.status.label_status);
                this.router.navigate(['administration/status']);                    
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });
        }
    }

}
