import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["action-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/action-administration.component.css']
})
export class ActionAdministrationComponent implements OnInit {

    coreUrl                     : string;
    mode                        : string    = null;
    action                      : any       = {};
    statusList                  : any[]     = [];
    actionPagesList             : any[]     = [];
    lang                        : any       = {};
    categoriesList              : any[]     = [];
    keywordsList                : any[]     = [];

    resultInfo                  : string;
    loading                     : boolean   = false;

    constructor(public http: Http,private route: ActivatedRoute, private router: Router){
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
                this.mode = 'create';
                
                this.http.get(this.coreUrl + 'rest/initAction')
                    .map(res => res.json())
                    .subscribe((data) => {
                        this.action = data.action;
                        this.lang = data.lang;
                        this.lang.pageTitle = this.lang.add+' '+this.lang.action;
                        this.categoriesList = data.categoriesList;
                        this.statusList = data.statusList;

                        this.actionPagesList = data.action_pagesList;
                        this.keywordsList = data.keywordsList;
                        this.loading = false;
                        setTimeout(() => {
                            $j("select").chosen({width:"100%",disable_search_threshold: 10, search_contains: true});     
                        }, 0);
                    });
            }
            else {
                this.mode = 'update';

                this.http.get(this.coreUrl + 'rest/administration/actions/' + params['id'])
                    .map(res => res.json())
                    .subscribe((data) => {
                        this.lang= data.lang;
                        this.action = data.action;
                        this.lang.pageTitle = this.lang.modify_action+' : '+this.action.id;
                        this.categoriesList = data.categoriesList;
                        this.statusList = data.statusList;

                        this.actionPagesList = data.action_pagesList;
                        this.keywordsList = data.keywordsList;
                        this.loading = false;
                        setTimeout(() => {
                            $j("select").chosen({width:"100%",disable_search_threshold: 10, search_contains: true});       
                        }, 0);
                    });
            } 
        });
    }

    onSubmit() {
        //affect value of select
        this.action.actionCategories = $j("#categorieslist").chosen().val();
        this.action.id_status = $j("#status").chosen().val();
        this.action.keyword = $j("#keyword").chosen().val();
        this.action.action_page = $j("#action_page").chosen().val();

        if (this.mode == 'create') {
            this.http.post(this.coreUrl + 'rest/actions', this.action)
            .map(res => res.json())
            .subscribe((data) => {
                this.router.navigate(['/administration/actions']);
                //TO_DO_NOTIF

            },(errors) => {
                console.log(errors);
                //TO_DO_NOTIF_ERRORS
            });
        }else if (this.mode == 'update') {
            this.http.put(this.coreUrl + 'rest/actions/' + this.action.id, this.action)
            .map(res => res.json())
            .subscribe((data) => {
                this.router.navigate(['/administration/actions']);
                //TO_DO_NOTIF

            },(errors) => {
                console.log(errors);
                //TO_DO_NOTIF_ERRORS
            });
        }

    }

    selectAll(event){
        var target = event.target.getAttribute("data-target");
        $j('#' + target + ' option').prop('selected', true);
        $j('#' + target).trigger('chosen:updated');
       
    }

    unselectAll(event){
        var target = event.target.getAttribute("data-target");
        $j('#' + target + ' option').prop('selected', false);
        $j('#' + target).trigger('chosen:updated');
    }

    /*createAction() {
        delete this.action.id;
        delete this.action.coll_categories;
        this.http.post(this.coreUrl + 'rest/actions', this.action)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {

                } else {
                    this.router.navigate(['/administration/actions']);
                    this.resultInfo = this.lang.action_added;
                    setTimeout(() => {
                        $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                        $j("#resultInfo").text(this.lang.action_added);
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                    }, 0);
                }
            }, (errors) => {
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").text(JSON.parse(errors._body).errors);
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            });
    }*/

}