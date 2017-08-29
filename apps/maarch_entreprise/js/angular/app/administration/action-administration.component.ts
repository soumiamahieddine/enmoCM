import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["action-administrationView"],
    styleUrls   : ['css/action-administration.component.css'],
    providers   : [NotificationService]
})
export class ActionAdministrationComponent implements OnInit {
    lang                        : any       = LANG;
    _search                     : string    = '';
    coreUrl                     : string;
    creationMode                : boolean;
    action                      : any       = {};
    statusList                  : any[]     = [];
    actionPagesList             : any[]     = [];
    categoriesList              : any[]     = [];
    keywordsList                : any[]     = [];

    loading                     : boolean   = false;

    constructor(public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService){
    }

    updateBreadcrumb(applicationName: string) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>"+this.lang.administration+"</a> > <a onclick='location.hash = \"/administration/actions\"' style='cursor: pointer'>"+this.lang.actions+"</a> > ";

        if(this.creationMode == true){
            breadCrumb += this.lang.actionCreation;
        } else {
            breadCrumb += this.lang.actionModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    }

    prepareActions() {
        $j('#inner_content').remove();
    }

    ngOnInit(): void {
        this.prepareActions();

        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;

        this.updateBreadcrumb(angularGlobals.applicationName);

        this.route.params.subscribe(params => {
            if(typeof params['id']== "undefined"){
                this.creationMode = true;
                
                this.http.get(this.coreUrl + 'rest/initAction')
                    .subscribe((data : any) => {
                        this.action = data.action;
                        this.categoriesList = data.categoriesList;
                        this.statusList = data.statusList;

                        this.actionPagesList = data.action_pagesList;
                        this.keywordsList = data.keywordsList;
                        this.loading = false;
                    });
            }
            else {
                this.creationMode = false;

                this.http.get(this.coreUrl + 'rest/administration/actions/' + params['id'])
                    .subscribe((data : any) => {
                        this.action = data.action;
                        this.categoriesList = data.categoriesList;
                        this.statusList = data.statusList;

                        this.actionPagesList = data.action_pagesList;
                        this.keywordsList = data.keywordsList;
                        this.loading = false;
                    });
            } 
        });
    }

    clearSearch(){
        this._search = '';
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + 'rest/actions', this.action)
            .subscribe((data : any) => {
                this.router.navigate(['/administration/actions']);
                this.notify.success(this.lang.actionAdded+' « '+this.action.label_action+' »');

            },(err) => {
                this.notify.error(JSON.parse(err._body).errors);
            });
        }else{
            this.http.put(this.coreUrl + 'rest/actions/' + this.action.id, this.action)
            .subscribe((data : any) => {
                this.router.navigate(['/administration/actions']);
                this.notify.success(this.lang.actionUpdated+' « '+this.action.label_action+' »');

            },(err) => {
                this.notify.error(JSON.parse(err._body).errors);
            });
        }
    }
}