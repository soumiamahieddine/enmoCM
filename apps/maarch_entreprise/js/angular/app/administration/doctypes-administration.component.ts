import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

import { AutoCompletePlugin } from '../../plugins/autocomplete.plugin';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: angularGlobals["doctypes-administrationView"],
    providers: [NotificationService]
})

export class DoctypesAdministrationComponent extends AutoCompletePlugin implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;

    doctypes: any[] = [];
    currentType: any = false;
    currentFirstLevel: any = false;
    currentSecondLevel: any = false;

    loading: boolean = false;
    creationMode: boolean = false;


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService) {
        super(http, 'usersAndEntities');
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Typologie documentaire";
        }
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/doctypes")
            .subscribe((data: any) => {
                this.doctypes = data['structure'];
                setTimeout(() => {
                    $j('#jstree').jstree({
                        "checkbox": {
                            "three_state": false //no cascade selection
                        },
                        'core': {
                            'themes': {
                                'name': 'proton',
                                'responsive': true
                            },
                            'data': this.doctypes,
                            "check_callback": true
                        },
                        "plugins": ["search", "dnd", "contextmenu"],
                    });
                    $j('#jstree')
                        // listen for event
                        .on('select_node.jstree', (e: any, data: any) => {
                            if (this.creationMode == true) {
                                // this.currentDoctype.doctypes_second_level_id = data.node.doctypes_second_level_id;
                            } else {
                                this.loadDoctype(data.node);
                            }

                        }).on('move_node.jstree', (e: any, data: any) => {
                            this.loadDoctype(data.node.id);
                            // this.currentDoctype.parent_entity_id = data.parent;
                            // this.moveEntity();
                        })
                        // create the instance
                        .jstree();
                }, 0);
                $j('#jstree').jstree('select_node', this.doctypes[0]);
                var to: any = false;
                $j('#jstree_search').keyup(function () {
                    if (to) { clearTimeout(to); }
                    to = setTimeout(function () {
                        var v = $j('#jstree_search').val();
                        $j('#jstree').jstree(true).search(v);
                    }, 250);
                });
                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    loadDoctype(data: any) {
        // Doctype
        if(data.original.type_id){
            this.http.get(this.coreUrl + "rest/doctypes/types/" + data.original.type_id )
                .subscribe((data: any) => {
                    this.currentFirstLevel  = false;
                    this.currentSecondLevel = false;
                    this.currentType        = data['doctype'];
                }, (err) => {
                    this.notify.error(err.error.errors);
                });

        // Second level
        } else if(data.original.doctypes_second_level_id) {
            this.http.get(this.coreUrl + "rest/doctypes/secondLevel/" + data.original.doctypes_second_level_id )
                .subscribe((data: any) => {
                    this.currentFirstLevel  = false;
                    this.currentSecondLevel = data['secondLevel'];
                    this.currentType        = false;
                }, (err) => {
                    this.notify.error(err.error.errors);
                });

        // First level
        } else {
            this.http.get(this.coreUrl + "rest/doctypes/firstLevel/" + data.original.doctypes_first_level_id )
                .subscribe((data: any) => {
                    this.currentFirstLevel  = data['firstLevel'];
                    this.currentSecondLevel = false;
                    this.currentType        = false;
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
        console.log(this.currentFirstLevel);
        console.log(this.currentSecondLevel);
        console.log(this.currentType);
    }

    // addElemListModel(element: any) {
    //     var inListModel = false;
    //     var newElemListModel = {
    //         "type": element.type,
    //         "id": element.id,
    //         "labelToDisplay": element.idToDisplay,
    //         "descriptionToDisplay": element.otherInfo,
    //     };
    //     this.currentDoctype.roles.forEach((role: any) => {
    //         if (role.available == true) {
    //             if (this.currentDoctype.listTemplate[role.id]) {

    //                 this.currentDoctype.listTemplate[role.id].forEach((listModel: any) => {
    //                     console.log(listModel);
    //                     if (listModel.id == element.id) {
    //                         inListModel = true;
    //                     }
    //                 });
    //             }
    //         }
    //     });
    //     if (!inListModel) {
    //         this.currentDoctype.listTemplate.cc.unshift(newElemListModel);
    //     }

    // }

    // saveEntity() {
    //     if (this.creationMode) {
    //         this.http.post(this.coreUrl + "rest/entities", this.currentDoctype)
    //             .subscribe((data: any) => {
    //                 this.creationMode = false;
    //                 this.doctypes.push(this.currentDoctype);
    //                 $j('#jstree').jstree("refresh");
    //                 this.notify.success(this.lang.entityAdded);
    //             }, (err) => {
    //                 this.notify.error(err.error.errors);
    //             });
    //     } else {
    //         this.http.put(this.coreUrl + "rest/entities/" + this.currentDoctype.entity_id, this.currentDoctype)
    //             .subscribe((data: any) => {
    //                 console.log(data);
    //                 this.doctypes = data['entities'];
    //                 $j('#jstree').jstree(true).settings.core.data = this.doctypes;
    //                 $j('#jstree').jstree("refresh");
    //                 this.notify.success(this.lang.entityUpdated);
    //             }, (err) => {
    //                 this.notify.error(err.error.errors);
    //             });
    //     }
    // }

    // moveEntity() {
    //     this.http.put(this.coreUrl + "rest/entities/" + this.currentDoctype.entity_id, this.currentDoctype)
    //             .subscribe((data: any) => {
    //                 this.notify.success(this.lang.entityUpdated);
    //             }, (err) => {
    //                 this.notify.error(err.error.errors);
    //             });

    // }

    readMode() {
        this.creationMode = false;
        $j('#jstree').jstree('deselect_all');
        $j('#jstree').jstree('select_node', this.doctypes[0]);
    }

    // removeEntity() {
    //     this.http.delete(this.coreUrl + "rest/entities/" + this.currentDoctype.entity_id)
    //         .subscribe((data: any) => {
    //             this.doctypes = data['entities'];
    //             $j('#jstree').jstree("refresh");
    //             this.notify.success(this.lang.entityDeleted);
    //         }, (err) => {
    //             this.notify.error(err.error.errors);
    //         });
    // }

    // prepareEntityAdd() {
    //     $j('#jstree').jstree('deselect_all');
    //     this.creationMode = true;
    //     this.currentDoctype = {};
    // }


    // updateStatus(entity: any, method: string) {
    //     this.http.put(this.coreUrl + "rest/entities/" + entity['entity_id'] + "/status", { "method": method })
    //         .subscribe((data: any) => {
    //             this.notify.success("");
    //         }, (err) => {
    //             this.notify.error(err.error.errors);
    //         });
    // }

    // delete(entity: any) {
    //     this.http.delete(this.coreUrl + "rest/entities/" + entity['entity_id'])
    //         .subscribe((data: any) => {
    //             this.notify.success(this.lang.entityDeleted);
    //             this.doctypes = data['entities'];
    //         }, (err) => {
    //             this.notify.error(err.error.errors);
    //         });
    // }
}
