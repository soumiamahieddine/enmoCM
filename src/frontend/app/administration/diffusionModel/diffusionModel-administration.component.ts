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
                this.itemTypeList =[{"id":"visaCircuit", "label": this.lang.visaWorkflow},{"id":"opinionCircuit", "label": this.lang.avis}];
                this.diffusionModel.type = 'opinionCircuit';
                this.diffusionModel.items = [];
            } else {
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(this.sidenavRight);

                this.creationMode = false;
                this.http.get("../../rest/listTemplates/" + params['id'])
                .subscribe((data: any) => {
                        this.diffusionModel = data['listTemplate'];
                        this.headerService.setHeader(this.lang.diffusionModelModification, this.diffusionModel.title);
                        this.idCircuit = params['id'];
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

        if (this.diffusionModel.type == 'opinionCircuit') {
            item_mode = 'avis';
            item_mode2 = 'avis';
        } else {
            item_mode = 'sign';
            item_mode2 = 'visa';
        }
        var newElemListModel = {
            "type": 'user',
            "mode": item_mode,
            "id": element.id,
            "sequence": this.diffusionModel.items.length,
            "idToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.descriptionToDisplay
        };

        this.diffusionModel.items.push(newElemListModel);
        if (this.diffusionModel.items.length > 1) {
            this.diffusionModel.items[this.diffusionModel.items.length-2].item_mode = item_mode2;
        }
    }

    updateDiffListVisa(template: any): any {
        this.listDiffModified = true;
        this.diffusionModel.items.forEach((listModel: any, i: number) => {
            listModel.sequence = i;
            if (this.diffusionModel.type == 'opinionCircuit') {
                listModel.mode = "avis";
            } else {
                if (i == (this.diffusionModel.items.length - 1)) {
                    listModel.mode = "sign";
                } else {
                    listModel.mode = "visa";
                }
            }
        });
    }

    removeDiffListVisa(template: any, i: number): any {
        this.listDiffModified = true;
        this.diffusionModel.items.splice(i, 1);

        if (this.diffusionModel.items.length > 0) {
            this.diffusionModel.items.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (this.diffusionModel.type == 'opinionCircuit') {
                    listModel.mode = "avis";
                } else {
                    if (i == (this.diffusionModel.items.length - 1)) {
                        listModel.mode = "sign";
                    } else {
                        listModel.mode = "visa";
                    }
                }
            });
        }
    }

    loadDiffList() {
        this.http.get("../../rest/listTemplates/" + this.idCircuit)
            .subscribe((data: any) => {
                this.diffusionModel = data['listTemplate'];
                this.loading = false;
                this.listDiffModified = false;

            }, () => {
                location.href = "index.php";
            });
    }

    saveDiffListVisa() {
        this.listDiffModified = false;
        var newDiffList = {
            "type": this.diffusionModel.type,
            "title": this.diffusionModel.title,
            "description": this.diffusionModel.description,
            "items": Array(),
            "admin": true
        };
        if (this.idCircuit == null) {
            this.diffusionModel.items.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (this.diffusionModel.type == 'opinionCircuit') {
                    listModel.mode = "avis";
                } else {
                    if (i == (this.diffusionModel.items.length - 1)) {
                        listModel.mode = "sign";
                    } else {
                        listModel.mode = "visa";
                    }
                }
                newDiffList.items.push({
                    "id": listModel.id,
                    "type": "user",
                    "mode": listModel.mode,
                    "sequence": listModel.sequence
                });
            });
            this.http.post("../../rest/listTemplates", newDiffList)
                .subscribe((data: any) => {
                    this.idCircuit = data.id;
                    this.router.navigate(["/administration/diffusionModels"]);
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else if (this.diffusionModel.items.length > 0) {
            this.diffusionModel.items.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                
                if (this.diffusionModel.type == 'opinionCircuit') {
                    listModel.mode = "avis";
                } else {
                    if (i == (this.diffusionModel.items.length - 1)) {
                        listModel.mode = "sign";
                    } else {
                        listModel.mode = "visa";
                    }
                }
                newDiffList.items.push({
                    "id": listModel.id,
                    "type": "user",
                    "mode": listModel.mode,
                    "sequence": listModel.sequence
                });
            });
            this.http.put("../../rest/listTemplates/" + this.idCircuit, newDiffList)
                .subscribe(() => {
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
