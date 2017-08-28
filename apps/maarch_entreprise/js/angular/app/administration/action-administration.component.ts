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
    actionCreation              : boolean;
    action                      : any       = {};
    statusList                  : any[]     = [];
    actionPagesList             : any[]     = [];
    categoriesList              : any[]     = [];
    keywordsList                : any[]     = [];

    loading                     : boolean   = false;

    constructor(public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService){
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/actions\"' style='cursor: pointer'>Actions</a> > Modification";
        }
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
                this.actionCreation = true;
                
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
                this.actionCreation = false;

                this.http.get(this.coreUrl + 'rest/administration/actions/' + params['id'])
                    .subscribe((data : any) => {
                        this.action = data.action;
                        this.lang.pageTitle = this.lang.modify_action+' : '+this.action.id;
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
        if (this.actionCreation) {
            this.http.post(this.coreUrl + 'rest/actions', this.action)
            .subscribe((data : any) => {
                this.router.navigate(['/administration/actions']);
                this.notify.success(data.success);

            },(err) => {
                this.notify.error(JSON.parse(err._body).errors);
            });
        }else{
            this.http.put(this.coreUrl + 'rest/actions/' + this.action.id, this.action)
            .subscribe((data : any) => {
                this.router.navigate(['/administration/actions']);
                this.notify.success(data.success);

            },(err) => {
                this.notify.error(JSON.parse(err._body).errors);
            });
        }
    }
}