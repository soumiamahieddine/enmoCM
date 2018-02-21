import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

import { AutoCompletePlugin } from '../../plugins/autocomplete.plugin';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: angularGlobals["entities-administrationView"],
    providers: [NotificationService]
})
export class EntitiesAdministrationComponent extends AutoCompletePlugin implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;

    entities: any[] = [];
    entityTypeList: any[] = ['Direction', 'Service', 'Bureau'];
    currentEntity: any = {};

    loading: boolean = false;
    creationMode: boolean = false;
    listTeamOne: Array<string> = ['Muhammad Ali', 'George Foreman', 'Joe Frazier', 'Jake LaMotta', 'Joe Louis', 'Jack Dempsey', 'Rocky Marciano', 'Mike Tyson', 'Oscar De La Hoya'];
    listTeamTwo: Array<string> = ['Sugar Ray Robinson'];


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService) {
        super(http, 'usersAndEntities');
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Entités";
        }
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + "rest/entities")
            .subscribe((data: any) => {
                this.entities = data['entities'];
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
                            'data': this.entities,
                            "check_callback": true
                        },
                        "plugins": ["search", "dnd", "contextmenu"],
                    });
                    $j('#jstree')
                        // listen for event
                        .on('select_node.jstree', (e: any, data: any) => {
                            if (this.creationMode == true) {
                                this.currentEntity.parent_entity_id = data.node.id;
                            } else {
                                this.loadEntity(data.node.id);
                            }

                        }).on('move_node.jstree', (e: any, data: any) => {
                            this.loadEntity(data.node.id);
                            this.currentEntity.parent_entity_id = data.parent;
                            this.moveEntity();
                        })
                        // create the instance
                        .jstree();
                }, 0);
                $j('#jstree').jstree('select_node', this.entities[0]);
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

    loadEntity(entity_id: any) {
        this.http.get(this.coreUrl + "rest/entities/" + entity_id + '/details')
            .subscribe((data: any) => {
                this.currentEntity = data['entity'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    addElemListModel(element: any) {
        var inListModel = false;
        var newElemListModel = {
            "type": element.type,
            "id": element.id,
            "labelToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo,
        };
        this.currentEntity.roles.forEach((role: any) => {
            if (role.available == true) {
                if (this.currentEntity.listTemplate[role.id]) {

                    this.currentEntity.listTemplate[role.id].forEach((listModel: any) => {
                        console.log(listModel);
                        if (listModel.id == element.id) {
                            inListModel = true;
                        }
                    });
                }
            }
        });
        if (!inListModel) {
            this.currentEntity.listTemplate.cc.unshift(newElemListModel);
        }

    }

    saveEntity() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/entities", this.currentEntity)
                .subscribe((data: any) => {
                    this.creationMode = false;
                    this.entities.push(this.currentEntity);
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.entityAdded);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
                .subscribe((data: any) => {
                    console.log(data);
                    this.entities = data['entities'];
                    $j('#jstree').jstree(true).settings.core.data = this.entities;
                    $j('#jstree').jstree("refresh");
                    this.notify.success(this.lang.entityUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    moveEntity() {
        this.http.put(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
                .subscribe((data: any) => {
                    this.notify.success(this.lang.entityUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });

    }

    readMode() {
        this.creationMode = false;
        $j('#jstree').jstree('deselect_all');
        $j('#jstree').jstree('select_node', this.entities[0]);
    }

    removeEntity() {
        this.http.delete(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id)
            .subscribe((data: any) => {
                this.entities = data['entities'];
                $j('#jstree').jstree("refresh");
                this.notify.success(this.lang.entityDeleted);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    prepareEntityAdd() {
        $j('#jstree').jstree('deselect_all');
        this.creationMode = true;
        this.currentEntity = {};
    }


    updateStatus(entity: any, method: string) {
        this.http.put(this.coreUrl + "rest/entities/" + entity['entity_id'] + "/status", { "method": method })
            .subscribe((data: any) => {
                this.notify.success("");
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    delete(entity: any) {
        this.http.delete(this.coreUrl + "rest/entities/" + entity['entity_id'])
            .subscribe((data: any) => {
                this.notify.success(this.lang.entityDeleted);
                this.entities = data['entities'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
