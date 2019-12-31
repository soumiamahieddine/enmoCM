import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService }        from '../../../service/header.service';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../../service/app.service';

declare function $j(selector: any): any;

@Component({
    templateUrl: "diffusionModel-administration.component.html",
    styleUrls: ['diffusionModel-administration.component.css'],
    providers: [NotificationService, AppService]
})
export class DiffusionModelAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight  : MatSidenav;
    
    lang                            : any       = LANG;
    loading                         : boolean   = false;

    diffusionModel                  : any       = {};
    idCircuit                       : number;
    itemTypeList                    : any       = [];
    creationMode                    : boolean;
    listDiffModified                : boolean   = false;


    displayedColumns    = ['firstname', 'lastname'];
    dataSource          : any;

    constructor(
        public http: HttpClient, 
        private route: ActivatedRoute, 
        private router: Router, 
        private notify: NotificationService, 
        private headerService: HeaderService,
        public appService: AppService
    ) {
        $j("link[href='merged_css.php']").remove();
    }

    ngOnInit(): void {

        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.headerService.setHeader(this.lang.diffusionModelCreation);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(this.sidenavRight);
                
                this.creationMode = true;
                this.loading = false;
                this.itemTypeList =[{"id":"VISA_CIRCUIT", "label": this.lang.visaWorkflow},{"id":"AVIS_CIRCUIT", "label": this.lang.avis}]
                this.diffusionModel.object_type = 'VISA_CIRCUIT';
                this.diffusionModel.diffusionList = [];
            } else {
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(this.sidenavRight);

                this.creationMode = false;
                this.http.get("../../rest/listTemplates/" + params['id'])
                .subscribe((data: any) => {
                        this.diffusionModel = data['listTemplate'];
                        this.headerService.setHeader(this.lang.diffusionModelModification, this.diffusionModel.title);
                        if (this.diffusionModel.diffusionList[0]) {
                            this.idCircuit = this.diffusionModel.diffusionList[0].id;
                        }
                        this.loading = false;

                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    addElemListModelVisa(element: any) {
        this.listDiffModified = true;
        var item_mode = '';
        var item_mode2 = '';

        if (this.diffusionModel.object_type == 'AVIS_CIRCUIT') {
            item_mode = 'avis';
            item_mode2 = 'avis';
        } else {
            item_mode = 'sign';
            item_mode2 = 'visa';
        }
        var newElemListModel = {
            "id": '',
            "item_type": 'user_id',
            "item_mode": item_mode,
            "item_id": element.id,
            "sequence": this.diffusionModel.diffusionList.length,
            "idToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.descriptionToDisplay
        };

        this.diffusionModel.diffusionList.push(newElemListModel);
        if (this.diffusionModel.diffusionList.length > 1) {
            this.diffusionModel.diffusionList[this.diffusionModel.diffusionList.length-2].item_mode = item_mode2;
        }
    }

    updateDiffListVisa(template: any): any {
        this.listDiffModified = true;
        this.diffusionModel.diffusionList.forEach((listModel: any, i: number) => {
            listModel.sequence = i;
            if (this.diffusionModel.object_type == 'AVIS_CIRCUIT') {
                listModel.item_mode = "avis";
            } else {
                if (i == (this.diffusionModel.diffusionList.length - 1)) {
                    listModel.item_mode = "sign";
                } else {
                    listModel.item_mode = "visa";
                }
            }
        });
    }

    removeDiffListVisa(template: any, i: number): any {
        this.listDiffModified = true;
        this.diffusionModel.diffusionList.splice(i, 1);

        if (this.diffusionModel.diffusionList.length > 0) {
            this.diffusionModel.diffusionList.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (this.diffusionModel.object_type == 'AVIS_CIRCUIT') {
                    listModel.item_mode = "avis";
                } else {
                    if (i == (this.diffusionModel.diffusionList.length - 1)) {
                        listModel.item_mode = "sign";
                    } else {
                        listModel.item_mode = "visa";
                    }
                }
            });
        }
    }

    loadDiffList() {
        this.http.get("../../rest/listTemplates/" + this.idCircuit)
            .subscribe((data: any) => {
                this.diffusionModel = data['listTemplate'];
                if (this.diffusionModel.diffusionList[0]) {
                    this.idCircuit = this.diffusionModel.diffusionList[0].id;
                }
                this.loading = false;
                this.listDiffModified = false;

            }, () => {
                location.href = "index.php";
            });
    }

    saveDiffListVisa() {
        this.listDiffModified = false;
        var newDiffList = {
            "object_id": this.diffusionModel.object_id,
            "object_type": this.diffusionModel.object_type,
            "title": this.diffusionModel.title,
            "description": this.diffusionModel.description,
            "items": Array()
        };
        if (this.idCircuit == null) {
            this.diffusionModel.diffusionList.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (this.diffusionModel.object_type == 'AVIS_CIRCUIT') {
                    listModel.item_mode = "avis";
                } else {
                    if (i == (this.diffusionModel.diffusionList.length - 1)) {
                        listModel.item_mode = "sign";
                    } else {
                        listModel.item_mode = "visa";
                    }
                }
                newDiffList.items.push({
                    "id": listModel.id,
                    "item_id": listModel.item_id,
                    "item_type": "user_id",
                    "item_mode": listModel.item_mode,
                    "sequence": listModel.sequence
                });
            });
            newDiffList.object_id = newDiffList.object_type + '_' + (Math.random()+ +new Date).toString(36).replace('.','').toUpperCase();
            this.http.post("../../rest/listTemplates", newDiffList)
                .subscribe((data: any) => {
                    this.idCircuit = data.id;
                    this.router.navigate(["/administration/diffusionModels"]);
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else if (this.diffusionModel.diffusionList.length > 0) {
            this.diffusionModel.diffusionList.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                
                if (this.diffusionModel.object_type == 'AVIS_CIRCUIT') {
                    listModel.item_mode = "avis";
                } else {
                    if (i == (this.diffusionModel.diffusionList.length - 1)) {
                        listModel.item_mode = "sign";
                    } else {
                        listModel.item_mode = "visa";
                    }
                }
                newDiffList.items.push({
                    "id": listModel.id,
                    "item_id": listModel.item_id,
                    "item_type": "user_id",
                    "item_mode": listModel.item_mode,
                    "sequence": listModel.sequence
                });
            });
            this.http.put("../../rest/listTemplates/" + this.idCircuit, newDiffList)
                .subscribe((data: any) => {
                    this.idCircuit = data.id;
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
