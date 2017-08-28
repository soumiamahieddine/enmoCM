import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;

@Component({
    templateUrl : angularGlobals["parameters-administrationView"],
    styleUrls   : ['css/parameters-administration.component.css'],
    providers   : [NotificationService]
})

export class ParametersAdministrationComponent implements OnInit {
    coreUrl         : string;

    lang            : any           = LANG;
    search          : string        = null;

    parametersList  : any;    

    resultInfo      : string        = "";
    loading         : boolean       = false;
    data            : any           = [];


    constructor(public http: HttpClient, private notify: NotificationService) {
    }

    updateBreadcrumb(applicationName: string){
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Paramètres";
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        
        this.http.get(this.coreUrl + 'rest/administration/parameters')
            .subscribe((data : any) => {
                this.parametersList = data.parametersList;
                this.data = this.parametersList;
                setTimeout(() => {
                    $j("[md2sortby='id']").click();
                }, 0);
                this.loading = false;

            });
    }

    goUrl(){
        location.href = 'index.php?admin=parameters&page=control_param_technic';
    }

    deleteParameter(paramId : string){
        var resp = confirm(this.lang.deleteConfirm+' '+paramId+'?');
        if (resp) {
            this.http.delete(this.coreUrl + 'rest/parameters/'+paramId)
                .subscribe((data : any) => {
                    this.data = data.parameter;
                    this.notify.success(data.success);             
                },(err) => {
                    this.notify.error(JSON.parse(err._body).errors);
                });
        }
    }
 
}
