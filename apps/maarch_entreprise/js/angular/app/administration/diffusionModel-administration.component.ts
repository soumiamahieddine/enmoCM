import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort } from '@angular/material';

import { AutoCompletePlugin } from '../../plugins/autocomplete.plugin';

declare function $j(selector: any): any;

declare const angularGlobals: any;


@Component({
    templateUrl: "../../../../Views/diffusionModel-administration.component.html",
    providers: [NotificationService]
})
export class DiffusionModelAdministrationComponent extends AutoCompletePlugin implements OnInit {

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;

    coreUrl                         : string;
    lang                            : any       = LANG;
    loading                         : boolean   = false;

    diffusionModel                  : any       = {};
    idCircuit                       : number;
    itemTypeList                    : any       = [];
    creationMode                    : boolean;


    displayedColumns    = ['firstname', 'lastname'];
    dataSource          : any;


    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService) {
        super(http, ['users']);
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;
                this.loading = false;
                this.itemTypeList =[{"id":"VISA_CIRCUIT", "label": this.lang.visa},{"id":"AVIS_CIRCUIT", "label": this.lang.avis}]
            } else {
                this.creationMode = false;
                this.http.get(this.coreUrl + "rest/listTemplates/" + params['id'])
                    .subscribe((data: any) => {
                        this.diffusionModel = data['listTemplate'];
                        if (this.diffusionModel.diffusionList[0]) {
                            this.idCircuit = this.diffusionModel.diffusionList[0].id;
                        }
                        this.loading = false;
                        setTimeout(() => {
                            this.dataSource = new MatTableDataSource(this.diffusionModel);
                            this.dataSource.paginator = this.paginator;
                            this.dataSource.sort = this.sort;
                        }, 0);

                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    addElemListModel(element: any) {
        var newDiffList = {
            "object_id": this.diffusionModel.entity_id,
            "object_type": this.diffusionModel.object_type,
            "title": this.diffusionModel.title,
            "description": this.diffusionModel.description,
            "items": Array()
        };
        
        if (this.diffusionModel.object_type == 'VISA_CIRCUIT') {
            var itemMode = 'sign';
        } else {
            var itemMode = 'avis';
        }

        var newElemListModel = {
            "id": '',
            "item_type": 'user_id',
            "item_mode": itemMode,
            "item_id": element.id,
            "sequence": this.diffusionModel.diffusionList.length,
            "idToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo
        };

        this.diffusionModel.diffusionList.forEach((listModel: any, i: number) => {
            listModel.sequence = i;
            if (this.diffusionModel.object_type == 'VISA_CIRCUIT') {
                listModel.item_mode = "visa";
            } else {
                listModel.item_mode = "avis";
            }  
            newDiffList.items.push({
                "id": listModel.id,
                "item_id": listModel.item_id,
                "item_type": "user_id",
                "item_mode": listModel.item_mode,
                "sequence": listModel.sequence
            });
        });

        newDiffList.items.push(newElemListModel);

        if (this.diffusionModel.diffusionList.length > 0) {
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuit, newDiffList)
                .subscribe((data: any) => {
                    this.idCircuit = data.id;
                    this.diffusionModel.diffusionList.push(newElemListModel);
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.post(this.coreUrl + "rest/listTemplates", newDiffList)
                .subscribe((data: any) => {
                    this.idCircuit = data.id;
                    this.diffusionModel.diffusionList.push(newElemListModel);
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
        this.userCtrl.setValue('');
    }

    updateDiffList(): any {
        var newDiffList = {
            "object_id": this.diffusionModel.entity_id,
            "object_type": this.diffusionModel.object_type,
            "title": this.diffusionModel.title,
            "description": this.diffusionModel.description,
            "items": Array()
        };
        this.diffusionModel.diffusionList.forEach((listModel: any, i: number) => {
            listModel.sequence = i;

            if (this.diffusionModel.object_type == 'VISA_CIRCUIT') {
                if (i == (this.diffusionModel.diffusionList.length - 1)) {
                    listModel.item_mode = "sign";
                } else {
                    listModel.item_mode = "visa";
                }
            } else {
                listModel.item_mode = "avis";
            } 

            newDiffList.items.push({
                "id": listModel.id,
                "item_id": listModel.item_id,
                "item_type": "user_id",
                "item_mode": listModel.item_mode,
                "sequence": listModel.sequence
            });
        });
        this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuit, newDiffList)
            .subscribe((data: any) => {
                this.idCircuit = data.id;
                this.notify.success(this.lang.diffusionModelUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    removeDiffList(template: any, i: number): any {
        this.diffusionModel.diffusionList.splice(i, 1);

        if (this.diffusionModel.diffusionList.length > 0) {
            var newDiffList = {
                "object_id": this.diffusionModel.entity_id,
                "object_type": this.diffusionModel.object_type,
                "title": this.diffusionModel.title,
                "description": this.diffusionModel.description,
                "items": Array()
            };

            this.diffusionModel.diffusionList.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (this.diffusionModel.object_type == 'VISA_CIRCUIT') {
                    if (i == (this.diffusionModel.diffusionList.length - 1)) {
                        listModel.item_mode = "sign";
                    } else {
                        listModel.item_mode = "visa";
                    }
                } else {
                    listModel.item_mode = "avis";
                } 

                newDiffList.items.push({
                    "item_id": listModel.item_id,
                    "item_type": "user_id",
                    "item_mode": listModel.item_mode,
                    "sequence": listModel.sequence
                });
            });
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuit, newDiffList)
                .subscribe((data: any) => {
                    this.idCircuit = data.id;
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.delete(this.coreUrl + "rest/listTemplates/" + this.idCircuit)
                .subscribe(() => {
                    this.idCircuit = null;
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
