import { ChangeDetectorRef, Component, OnInit, Inject, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material';

import { AutoCompletePlugin } from '../../plugins/autocomplete.plugin';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: angularGlobals["basket-administrationView"],
    providers: [NotificationService]
})
export class BasketAdministrationComponent implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;
    dialogRef: MatDialogRef<any>;
    config: any = {};

    id: string;
    creationMode: boolean;

    basket: any = {};
    basketGroups: any[] = [];
    allGroups: any[] = [];
    basketIdAvailable: boolean;
    actionsList: any[] = [];
    resultPages: any[] = [];

    loading: boolean = false;

    displayedColumns = ['label_action', 'actions'];
    dataSource: any;
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService, public dialog: MatDialog) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    updateBreadcrumb(applicationName: string) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>" + this.lang.administration + "</a> > <a onclick='location.hash = \"/administration/baskets\"' style='cursor: pointer'>" + this.lang.baskets + "</a> > ";
        if (this.creationMode == true) {
            breadCrumb += this.lang.basketCreation;
        } else {
            breadCrumb += this.lang.basketModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;
                this.updateBreadcrumb(angularGlobals.applicationName);
                this.basketIdAvailable = false;
                this.loading = false;
            } else {
                this.creationMode = false;
                this.updateBreadcrumb(angularGlobals.applicationName);
                this.basketIdAvailable = true;
                this.id = params['id'];
                this.http.get(this.coreUrl + "rest/baskets/" + this.id)
                    .subscribe((data: any) => {
                        this.basket = data.basket;
                        this.basket.id = data.basket.basket_id;
                        this.basket.name = data.basket.basket_name;
                        this.basket.description = data.basket.basket_desc;
                        this.basket.clause = data.basket.basket_clause;
                        this.basket.isSearchBasket = data.basket.is_visible != "Y";
                        this.basket.isFolderBasket = data.basket.is_folder_basket == "Y";
                        this.basket.flagNotif = data.basket.flag_notif == "Y";

                        this.http.get(this.coreUrl + "rest/baskets/" + this.id + "/groups")
                            .subscribe((data: any) => {
                                this.allGroups = data.allGroups;

                                this.allGroups.forEach((tmpAllGroup: any) => {
                                    tmpAllGroup.isUsed = false;
                                    data.groups.forEach((tmpGroup: any) => {
                                        if (tmpAllGroup.group_id == tmpGroup.group_id) {
                                            tmpAllGroup.isUsed = true
                                        }
                                    });
                                });

                                data.groups.forEach((tmpGroup: any) => {
                                    tmpGroup.groupActions.forEach((tmpAction: any) => {
                                        tmpAction.used_in_basketlist = tmpAction.used_in_basketlist == "Y";
                                        tmpAction.used_in_action_page = tmpAction.used_in_action_page == "Y";
                                        tmpAction.default_action_list = tmpAction.default_action_list == "Y";
                                    });
                                });
                                this.basketGroups = data.groups;
                                this.resultPages = data.pages;

                                this.loading = false;
                            }, () => {
                                location.href = "index.php";
                            });
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    openSettings(group: any, action: any) {
        this.config = { data: { group: group, action: action, pages: this.resultPages } };
        this.dialogRef = this.dialog.open(BasketAdministrationSettingsModalComponent, this.config);
        this.dialogRef.afterClosed().subscribe((result: any) => {
            if (result) {
                this.http.put(this.coreUrl + "rest/baskets/" + this.id + "/groups/" + result.group.group_id, { 'result_page': result.group.result_page, 'groupActions': result.group.groupActions })
                    .subscribe((data: any) => {
                        //this.basketGroups.push(data);
                        this.notify.success(this.lang.basketUpdated);
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            }
            this.dialogRef = null;
        });
    }

    isAvailable() {
        if (this.basket.id) {
            this.http.get(this.coreUrl + "rest/baskets/" + this.basket.id)
                .subscribe(() => {
                    this.basketIdAvailable = false;
                }, (err) => {
                    this.basketIdAvailable = false;
                    if (err.error.errors == "Basket not found") {
                        this.basketIdAvailable = true;
                    }
                });
        } else {
            this.basketIdAvailable = false;
        }
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/baskets", this.basket)
                .subscribe((data: any) => {
                    this.notify.success(this.lang.basketAdded);
                    this.router.navigate(["/administration/baskets"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/baskets/" + this.id, this.basket)
                .subscribe((data: any) => {
                    this.notify.success(this.lang.basketUpdated);
                    this.router.navigate(["/administration/baskets"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    initAction(groupIndex: number) {
        this.dataSource = new MatTableDataSource(this.basketGroups[groupIndex].groupActions);
        this.dataSource.sort = this.sort;
    }

    setDefaultAction(group: any, action: any) {
        group.groupActions.forEach((tmpAction: any) => {
            if (action.id == tmpAction.id) {
                tmpAction.default_action_list = true;
            } else {
                tmpAction.default_action_list = false;
            }
        });
    }

    unlinkGroup(groupIndex: any) {
        let r = confirm(this.lang.unlinkGroup + ' ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/baskets/" + this.id + "/groups/" + this.basketGroups[groupIndex].group_id)
                .subscribe((data: any) => {
                    this.allGroups.forEach((tmpGroup: any) => {
                        if (tmpGroup.group_id == this.basketGroups[groupIndex].group_id) {
                            tmpGroup.isUsed = false;
                        }
                    });
                    this.basketGroups.splice(groupIndex, 1);
                    this.notify.success(this.lang.basketUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    linkGroup() {
        this.config = { data: { basketId: this.basket.id, groups: this.allGroups, linkedGroups: this.basketGroups } };
        this.dialogRef = this.dialog.open(BasketAdministrationGroupListModalComponent, this.config);
        this.dialogRef.afterClosed().subscribe((result: any) => {
            if (result) {
                this.http.post(this.coreUrl + "rest/baskets/" + this.id + "/groups", result)
                    .subscribe((data: any) => {
                        this.basketGroups.push(result);
                        this.allGroups.forEach((tmpGroup: any) => {
                            if (tmpGroup.group_id == result.group_id) {
                                tmpGroup.isUsed = true;
                            }
                        });
                        this.notify.success(this.lang.basketUpdated);
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            }
            this.dialogRef = null;
        });
    }

    addAction(group: any) {
        this.http.put(this.coreUrl + "rest/baskets/" + this.id + "/groups/" + group.group_id, { 'result_page': group.result_page, 'groupActions': group.groupActions })
            .subscribe((data: any) => {
                //this.basketGroups.push(data);
                this.notify.success(this.lang.basketUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

}
@Component({
    templateUrl: angularGlobals["basket-administration-settings-modalView"],
    styles: [".mat-dialog-content{height: 65vh;}"]
})
export class BasketAdministrationSettingsModalComponent extends AutoCompletePlugin {
    lang: any = LANG;
    allEntities: any[] = [];
    statuses: any;
    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<BasketAdministrationSettingsModalComponent>) {
        super(http, ['users']);
    }
    ngOnInit(): void {
        this.http.get(this.coreUrl + "rest/entities")
            .subscribe((entities: any) => {
                var keywordEntities = [{
                    id: 'ALL_ENTITIES',
                    keyword: 'ALL_ENTITIES',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: 'Toute les entités'
                }, {
                    id: 'ENTITIES_JUST_BELOW',
                    keyword: 'ENTITIES_JUST_BELOW',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Immédiatement inférieur à mon entité primaire"
                }, {
                    id: 'ENTITIES_BELOW',
                    keyword: 'ENTITIES_BELOW',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Inférieur à toutes mes entités"
                }, {
                    id: 'ALL_ENTITIES_BELOW',
                    keyword: 'ALL_ENTITIES_BELOW',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Inférieur à mon entité primaire"
                }, {
                    id: 'MY_ENTITIES',
                    keyword: 'MY_ENTITIES',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Mes entités"
                }, {
                    id: 'MY_PRIMARY_ENTITY',
                    keyword: 'MY_PRIMARY_ENTITY',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Mon entité primaire"
                }, {
                    id: 'SAME_LEVEL_ENTITIES',
                    keyword: 'SAME_LEVEL_ENTITIES',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Même niveau de mon entité primaire"
                }];

                keywordEntities.forEach((keyword: any) => {
                    this.allEntities.push(keyword);
                });
                entities.entities.forEach((entity: any) => {
                    this.allEntities.push(entity);
                });
            }, () => {
                location.href = "index.php";
            })
        this.http.get(this.coreUrl + 'rest/statuses')
            .subscribe((data: any) => {
                this.statuses = data.statuses;
            });
    }
    initService() {
        this.allEntities.forEach((entity: any) => {
            entity.state = { "opened": false, "selected": false };
            this.data.action.redirects.forEach((keyword: any) => {
                if (entity.id == keyword.keyword && keyword.redirect_mode == 'ENTITY') {
                    entity.state = { "opened": true, "selected": true };
                }
            });
        });


        $j('#jstree').jstree({
            "checkbox": {
                "three_state": false //no cascade selection
            },
            'core': {
                'themes': {
                    'name': 'proton',
                    'responsive': true
                },
                'data': this.allEntities
            },
            "plugins": ["checkbox", "search"]
        });
        $j('#jstree')
            // listen for event
            .on('select_node.jstree', (e: any, data: any) => {
                if (data.node.original.keyword) {
                    this.data.action.redirects.push({ action_id: this.data.action.id, entity_id: '', keyword: data.node.id, redirect_mode: 'ENTITY' })
                } else {
                    this.data.action.redirects.push({ action_id: this.data.action.id, entity_id: data.node.id, keyword: '', redirect_mode: 'ENTITY' })
                }

            }).on('deselect_node.jstree', (e: any, data: any) => {
                this.data.action.redirects.forEach((redirect: any) => {
                    if (data.node.original.keyword) {
                        if (redirect.keyword == data.node.original.keyword) {
                            var index = this.data.action.redirects.indexOf(redirect);
                            this.data.action.redirects.splice(index, 1);
                        }
                    } else {
                        if (redirect.entity_id == data.node.id) {
                            var index = this.data.action.redirects.indexOf(redirect);
                            this.data.action.redirects.splice(index, 1);
                        }
                    }

                });
            })
            // create the instance
            .jstree();

        var to: any = false;
        $j('#jstree_search').keyup(function () {
            if (to) { clearTimeout(to); }
            to = setTimeout(function () {
                var v = $j('#jstree_search').val();
                $j('#jstree').jstree(true).search(v);
            }, 250);
        });

    }

    initService2() {
        this.allEntities.forEach((entity: any) => {
            entity.state = { "opened": false, "selected": false };
            this.data.action.redirects.forEach((keyword: any) => {
                if (entity.id == keyword.keyword && keyword.redirect_mode == 'USERS') {
                    entity.state = { "opened": true, "selected": true };
                }
            });
        });
        $j('#jstree2').jstree({
            "checkbox": {
                "three_state": false //no cascade selection
            },
            'core': {
                'themes': {
                    'name': 'proton',
                    'responsive': true
                },
                'data': this.allEntities
            },
            "plugins": ["checkbox", "search"]
        });
        $j('#jstree2')
            // listen for event
            .on('select_node.jstree', (e: any, data: any) => {
                if (data.node.original.keyword) {
                    this.data.action.redirects.push({ action_id: this.data.action.id, entity_id: '', keyword: data.node.id, redirect_mode: 'USERS' })
                } else {
                    this.data.action.redirects.push({ action_id: this.data.action.id, entity_id: data.node.id, keyword: '', redirect_mode: 'USERS' })
                }

            }).on('deselect_node.jstree', (e: any, data: any) => {
                this.data.action.redirects.forEach((redirect: any) => {
                    if (data.node.original.keyword) {
                        if (redirect.keyword == data.node.original.keyword) {
                            var index = this.data.action.redirects.indexOf(redirect);
                            this.data.action.redirects.splice(index, 1);
                        }
                    } else {
                        if (redirect.entity_id == data.node.id) {
                            var index = this.data.action.redirects.indexOf(redirect);
                            this.data.action.redirects.splice(index, 1);
                        }
                    }

                });
            })
            // create the instance
            .jstree();

        var to: any = false;
        $j('#jstree_search2').keyup(function () {
            if (to) { clearTimeout(to); }
            to = setTimeout(function () {
                var v = $j('#jstree_search2').val();
                $j('#jstree2').jstree(true).search(v);
            }, 250);
        });
    }
}

import { FormBuilder, FormGroup, Validators } from '@angular/forms';
@Component({
    templateUrl: angularGlobals["basket-administration-groupList-modalView"],
    styles: [".mat-dialog-content{height: 65vh;}"]
})
export class BasketAdministrationGroupListModalComponent {
    lang: any = LANG;
    coreUrl: string;
    groupId: any;
    firstFormGroup: FormGroup;
    secondFormGroup: FormGroup;
    displayedColumns = ['label_action'];
    dataSource: any;
    dataSource2: any;
    actionAll: any = [];
    newBasketGroup: any = {};


    @ViewChild(MatSort) sort: MatSort;
    @ViewChild('paginator') paginator: MatPaginator;
    @ViewChild('paginator2') paginator2: MatPaginator;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }
    applyFilter2(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource2.filter = filterValue;
    }


    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<BasketAdministrationGroupListModalComponent>, private _formBuilder: FormBuilder) { }
    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + "rest/actions")
            .subscribe((data: any) => {
                data.actions.forEach((tmpAction: any) => {
                    tmpAction.where_clause = "";
                    tmpAction.used_in_basketlist = false;
                    tmpAction.default_action_list = false;
                    tmpAction.used_in_action_page = true;
                    tmpAction.statuses = [];
                    tmpAction.redirects = [];
                    tmpAction.checked = false;
                    this.actionAll.push(tmpAction);
                });
                this.dataSource = new MatTableDataSource(this.actionAll);
                this.dataSource.sort = this.sort;
                this.dataSource.paginator = this.paginator;

                this.dataSource2 = new MatTableDataSource(this.actionAll);
                this.dataSource2.sort = this.sort;
                this.dataSource2.paginator = this.paginator2;

            }, (err) => {
                location.href = "index.php";
            });

        this.firstFormGroup = this._formBuilder.group({
            firstCtrl: ['', Validators.required]
        });
        this.secondFormGroup = this._formBuilder.group({
            secondCtrl: ['', Validators.required]
        });
        this.data.groups.forEach((tmpGroup: any) => {
            this.data.linkedGroups.forEach((tmpLinkedGroup: any) => {
                if (tmpGroup.group_id == tmpLinkedGroup.group_id) {
                    var index = this.data.groups.indexOf(tmpGroup);
                    this.data.groups.splice(index, 1);
                }
            });
        });
    }

    initAction(actionType: any) {
        this.dataSource.filter = actionType.value;
    }

    selectDefaultAction(action: any) {
        this.actionAll.forEach((tmpAction: any) => {
            if (action.id == tmpAction.id) {
                tmpAction.checked = true;
                tmpAction.default_action_list = true
            } else {
                tmpAction.checked = false;
                tmpAction.default_action_list = false
            }
        });
    }
    selectAction(e: any, action: any) {
        action.checked = e.checked;
    }

    validateForm() {
        this.newBasketGroup.group_id = this.groupId;
        this.newBasketGroup.basket_id = this.data.basketId;
        this.newBasketGroup.result_page = 'list_with_attachments';
        this.newBasketGroup.groupActions = this.actionAll;
        this.dialogRef.close(this.newBasketGroup);
    }
}