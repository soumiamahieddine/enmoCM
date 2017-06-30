import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;
declare var tinymce : any;
declare var Prototype : any;
declare var angularGlobals : any;


@Component({
    templateUrl : 'Views/action.component.html',
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'js/angular/app/Css/actions.component.css']
})
export class ActionComponent implements OnInit {

    coreUrl                     : string;
    resultInfo                  : string;
    actions                     : any       = {
                                                id: "",
                                                history: "",
                                                id_status: "",
                                                action_page: "",
                                                keyword: "",
                                                is_folder_action: "",
                                                label_action: "",
                                                category_id: [],
                                                coll_categories: [],
                                                };
    statuts                     : any[]     = [];
    tabAction_page              : any       = {
                                                module: "",
                                                action: [],
                                                };
    lang                        : any       = {};
    keywords                    : any[]     = [];
    leftCategories              : any[]     = [];
    rightCategories             : any[]     = [];

    constructor(public http: Http,private route: ActivatedRoute, private router: Router){
    }

    prepareActions() {
        $j('#inner_content').remove();
        this.route.params.subscribe(params => {
            if(typeof params['id']== "undefined"){
                this.initAction();
            }
            else {
                this.getAction(params['id']);
            }
        });
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.prepareActions();
    }

    getAction(id: number) {
        $j('#create-action').remove();
        this.http.get(this.coreUrl + 'rest/administration/actions/' + id)
            .map(res => res.json())
            .subscribe((data) => {
                var tab = data;
                this.lang= data.lang;
                this.actions.id = id;
                this.actions.label_action = tab.label_action;
                this.actions.keyword = tab.keyword;
                this.actions.id_status = tab.id_status;
                this.actions.is_folder_action = tab.is_folder_action;
                this.actions.action_page = tab.action_page;
                this.actions.history = tab.history;
                this.actions.category_id = tab['category_id'];
                this.actions.coll_categories = tab['coll_categories'];
                this.statuts = tab['statuts'];

                //Add in right select
                var ind = 0;
                for (var i = 0; i < tab['coll_categories'].length; i++) {
                    for (var j = 0; j < tab['category_id'].length; j++) {
                        if (tab['category_id'][j].category_id == tab['coll_categories'][i].id) {
                            this.rightCategories[ind] = tab['coll_categories'][i];
                            ind++;
                        }
                    }
                }

                //Add in left select
                ind = 0;
                var bool = false;

                for (var i = 0; i < tab['coll_categories'].length; i++) {
                    for (var j = 0; j < this.rightCategories.length; j++) {
                        if (this.rightCategories[j].id == tab['coll_categories'][i].id) {
                            bool = true;
                        }
                    }
                    if (!bool) {
                        this.leftCategories[ind] = tab['coll_categories'][i];
                        ind++;
                    }
                    bool = false;
                }
                this.tabAction_page.module = tab['tab_action_page']['modules'];
                this.tabAction_page.actions = tab['tab_action_page']['actions'];
                this.keywords = tab['keywords'];
            });


    }

    updateAction() {
        this.actions.category_id = this.rightCategories;
        this.http.put(this.coreUrl + 'rest/actions/' + this.actions.id, this.actions)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {

                } else {
                    this.router.navigate(['/administration/actions']);
                    setTimeout(() => {
                        $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                        $j("#resultInfo").text(this.lang.action_modified);
                        $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                            $j("#resultInfo").slideUp(500);
                        });
                    }, 0);

                }
            },(errors) => {
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").text(JSON.parse(errors._body).errors);
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            });

    }

    createAction() {
        this.actions.category_id = this.rightCategories;
        delete this.actions.id;
        delete this.actions.coll_categories;
        this.http.post(this.coreUrl + 'rest/actions', this.actions)
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
    }

    move(id: string, mode: string) {
        if (mode == "add") {
            var catClick = $j('#' + id).val();
            for (var i = 0; i < this.leftCategories.length; i++) {
                for (var j = 0; j < catClick.length; j++) {
                    if (this.leftCategories[i].id == catClick[j]) {
                        this.rightCategories.push(this.leftCategories[i]);
                        this.leftCategories.splice(i, 1);
                    }
                }

            }

        }

        if (mode == "remove") {
            var catClick = $j('#' + id).val();
            for (var i = 0; i < this.rightCategories.length; i++) {
                for (var j = 0; j < catClick.length; j++) {
                    if (this.rightCategories[i].id == catClick[j]) {
                        this.leftCategories.push(this.rightCategories[i]);
                        this.rightCategories.splice(i, 1);
                    }
                }

            }

        }
    }



    moveClick(id: string, mode: string) {

        var find=false;
        if (mode == "add") {
            var catClick = $j('#' + id).val();
            var i=0;
            while(i<this.leftCategories.length && !find){
                if(this.leftCategories[i].id ==catClick[0]){
                    this.rightCategories.push(this.leftCategories[i]);
                    this.leftCategories.splice(i, 1);
                    find=true;
                }
                i++;
            }
        }

        if (mode == "remove") {
            var catClick = $j('#' + id).val();
            var i=0;
            while(i<this.rightCategories.length && !find){
                if(this.rightCategories[i].id ==catClick[0]){
                    this.leftCategories.push(this.rightCategories[i]);
                    this.rightCategories.splice(i, 1);
                    find=true;
                }
                i++;
            }
        }
    }

    initAction(){
        $j('#update-action').remove();
        this.http.get(this.coreUrl + 'rest/initAction')
            .map(res => res.json())
            .subscribe((data) => {
                var tab = data;
                this.lang= data.lang;
                this.leftCategories=tab['coll_categories'];
                this.actions.id_status =tab['statuts'][0].id;
                this.statuts=tab['statuts'];
                this.tabAction_page.module=tab['tab_action_page']['modules'];
                this.tabAction_page.actions=tab['tab_action_page']['actions'];
                this.actions.action_page=tab['tab_action_page']['actions'][0].name;
                this.actions.history=tab.history;
                this.actions.is_folder_action=tab.is_folder_action;
                this.actions.keyword=tab['keywords'][0].value;
                this.keywords=tab['keywords'];
            });
    }
}