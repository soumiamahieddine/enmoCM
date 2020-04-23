import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { AppService } from '../../../service/app.service';
import { HeaderService } from '../../../service/header.service';
import { NotificationService } from '../../notification.service';
import { MatSidenav } from '@angular/material/sidenav';
import { FunctionsService } from '../../../service/functions.service';
import { ActivatedRoute, Router } from '@angular/router';
import { tap } from 'rxjs/internal/operators/tap';
import { catchError } from 'rxjs/internal/operators/catchError';
import { of } from 'rxjs/internal/observable/of';

@Component({
    selector: 'app-alfresco',
    templateUrl: './alfresco-administration.component.html',
    styleUrls: ['./alfresco-administration.component.scss']
})
export class AlfrescoAdministrationComponent implements OnInit {

    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;

    lang: any = LANG;
    loading: boolean = false;
    creationMode: boolean = true;

    entities: any[] = [];

    alfresco: any = {
        id: 0,
        label: '',
        account: {
            id: '',
            password: '',
        },
        rootFolder: null,
        linkedEntities: []
    };

    alfrescoTreeLoaded: boolean = false;

    constructor(
        public http: HttpClient,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService,
        private headerService: HeaderService,
        public appService: AppService,
        public functionsService: FunctionsService
    ) { }

    ngOnInit() {
        this.loading = false;
        this.route.params.subscribe(async params => {
            if (typeof params['id'] === 'undefined') {
                this.headerService.setHeader(this.lang.alfrescoCreation);
                this.creationMode = true;
            } else {
                this.headerService.setHeader(this.lang.alfrescoModification);

                this.alfresco.id = params['id'];
                this.creationMode = false;
            }
            await this.getEntities();
            await this.initAccount();
            this.loading = false;
        });
    }

    onSubmit() {
        if (this.creationMode) {
            this.createAccount();
        } else {
            this.updateAccount();
        }
    }

    createAccount() {
        this.http.post('../rest/alfresco/accounts', this.formatData()).pipe(
            tap(() => {
                this.notify.success(this.lang.accountAdded);
                this.router.navigate(['/administration/alfresco']);
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    updateAccount() {
        this.http.put(`../rest/alfresco/accounts/${this.alfresco.id}`, this.formatData()).pipe(
            tap(() => {
                this.notify.success(this.lang.accountUpdated);
                this.router.navigate(['/administration/alfresco']);
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    formatData() {
        const alfresco: any = {
            label: this.alfresco.label,
            login: this.alfresco.account.id,
            nodeId: this.alfresco.rootFolder,
            entities: $('#jstree').jstree('get_checked', null, true)
        };

        if (!this.functionsService.empty(this.alfresco.account.password)) {
            alfresco.password = this.alfresco.account.password;
        }

        return alfresco;
    }

    getEntities() {
        return new Promise((resolve, reject) => {
            this.http.get(`../rest/administration/shippings/new`).pipe(
                tap((data: any) => {
                    this.entities = data['entities'];
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    initAccount() {
        return new Promise((resolve, reject) => {
            if (this.creationMode) {
                this.http.get('../rest/administration/shippings/new').pipe(
                    tap((data: any) => {
                        this.entities = data['entities'];

                        // TO DO : WAIT BACK
                        const allowedEntities = ['12', '19', '16'];

                        this.entities.forEach(element => {
                            if (allowedEntities.indexOf(element.id) === -1) {
                                element.state.disabled = true;
                            }
                        });

                        setTimeout(() => {
                            this.initEntitiesTree(this.entities);
                        }, 0);
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notify.handleErrors(err);
                        return of(false);
                    })
                ).subscribe();

            } else {
                this.http.get(`../rest/alfresco/accounts/${this.alfresco.id}`).pipe(
                    tap((data: any) => {
                        this.alfresco = {
                            id: data.id,
                            label: data.label,
                            account: {
                                id: data.login
                            },
                            rootFolder: data.nodeId,
                            linkedEntities: data.entities
                        };

                        // TO DO : WAIT BACK
                        const allowedEntities = ['12', '19', '16'];

                        this.entities.forEach(element => {
                            element.state.disabled = false;
                            /*if (allowedEntities.indexOf(element.id) === -1) {
                                element.state.disabled = true;
                            }*/
                            console.log(this.alfresco.linkedEntities[0]);
                            console.log(element.id);

                            if (this.alfresco.linkedEntities.indexOf(+element.id) > -1) {
                                element.state.selected = true;
                            }
                        });
                        setTimeout(() => {
                            this.initEntitiesTree(this.entities);
                        }, 0);
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notify.handleErrors(err);
                        return of(false);
                    })
                ).subscribe();
            }

        });
    }

    initEntitiesTree(entities: any) {
        $('#jstree')
            .jstree({
                'checkbox': {
                    'three_state': false // no cascade selection
                },
                'core': {
                    force_text: true,
                    'themes': {
                        'name': 'proton',
                        'responsive': true
                    },
                    'data': entities
                },
                'plugins': ['checkbox', 'search', 'sort']
            });
        let to: any = false;
        $('#jstree_search').keyup(function () {
            if (to) { clearTimeout(to); }
            to = setTimeout(function () {
                const v: any = $('#jstree_search').val();
                $('#jstree').jstree(true).search(v);
            }, 250);
        });
    }

    getAlfrescoFolders() {

        // TODO :  ROUTE GET FOLDERS ARBO
        const folders: any = [{ 'id': '1', 'entity_id': 'VILLE', 'entity_label': 'Ville de Maarch-les-bains', 'parent_id': null, 'parent': '#', 'icon': 'fa fa-building', 'allowed': true, 'state': { 'opened': true }, 'text': 'Ville de Maarch-les-bains' }, { 'id': '2', 'entity_id': 'CAB', 'entity_label': 'Cabinet du Maire', 'parent_id': 1, 'parent': '1', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Cabinet du Maire' }, { 'id': '3', 'entity_id': 'DGS', 'entity_label': 'Direction Générale des Services', 'parent_id': 1, 'parent': '1', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Direction Générale des Services' }, { 'id': '4', 'entity_id': 'DGA', 'entity_label': 'Direction Générale Adjointe', 'parent_id': 3, 'parent': '3', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Direction Générale Adjointe' }, { 'id': '5', 'entity_id': 'PCU', 'entity_label': 'Pôle Culturel', 'parent_id': 4, 'parent': '4', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Pôle Culturel' }, { 'id': '6', 'entity_id': 'PJS', 'entity_label': 'Pôle Jeunesse et Sport', 'parent_id': 4, 'parent': '4', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Pôle Jeunesse et Sport' }, { 'id': '7', 'entity_id': 'PE', 'entity_label': 'Petite enfance', 'parent_id': 6, 'parent': '6', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Petite enfance' }, { 'id': '8', 'entity_id': 'SP', 'entity_label': 'Sport', 'parent_id': 6, 'parent': '6', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Sport' }, { 'id': '9', 'entity_id': 'PSO', 'entity_label': 'Pôle Social', 'parent_id': 4, 'parent': '4', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Pôle Social' }, { 'id': '10', 'entity_id': 'PTE', 'entity_label': 'Pôle Technique', 'parent_id': 4, 'parent': '4', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Pôle Technique' }, { 'id': '11', 'entity_id': 'DRH', 'entity_label': 'Direction des Ressources Humaines', 'parent_id': 3, 'parent': '3', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Direction des Ressources Humaines' }, { 'id': '12', 'entity_id': 'DSG', 'entity_label': 'Secrétariat Général', 'parent_id': 3, 'parent': '3', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Secrétariat Général' }, { 'id': '14', 'entity_id': 'COR', 'entity_label': 'Correspondants Archive', 'parent_id': 13, 'parent': '13', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Correspondants Archive' }, { 'id': '13', 'entity_id': 'COU', 'entity_label': 'Service Courrier', 'parent_id': 12, 'parent': '12', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Service Courrier' }, { 'id': '15', 'entity_id': 'PSF', 'entity_label': 'Pôle des Services Fonctionnels', 'parent_id': 12, 'parent': '12', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Pôle des Services Fonctionnels' }, { 'id': '16', 'entity_id': 'DSI', 'entity_label': 'Direction des Systèmes d\'Information', 'parent_id': 3, 'parent': '3', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Direction des Systèmes d\'Information' }, { 'id': '17', 'entity_id': 'FIN', 'entity_label': 'Direction des Finances', 'parent_id': 3, 'parent': '3', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Direction des Finances' }, { 'id': '18', 'entity_id': 'PJU', 'entity_label': 'Pôle Juridique', 'parent_id': 17, 'parent': '17', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Pôle Juridique' }, { 'id': '19', 'entity_id': 'ELUS', 'entity_label': 'Ensemble des élus', 'parent_id': 1, 'parent': '1', 'icon': 'fa fa-sitemap', 'allowed': true, 'state': { 'opened': true }, 'text': 'Ensemble des élus' }, { 'id': '20', 'entity_id': 'CCAS', 'entity_label': 'Centre Communal d\'Action Sociale', 'parent_id': null, 'parent': '#', 'icon': 'fa fa-building', 'allowed': true, 'state': { 'opened': true }, 'text': 'Centre Communal d\'Action Sociale' }];

        this.alfrescoTreeLoaded = true;
        setTimeout(() => {
            this.initAlfrescoTree(folders);
        }, 0);

    }

    initAlfrescoTree(folders: any) {
        $('#jstreeAlfresco')
            .on('select_node.jstree', (e: any, data: any) => {
                this.alfresco.rootFolder = data.node.id;
                this.alfrescoTreeLoaded = false;
            }).on('deselect_node.jstree', (e: any, data: any) => {
                // this.shipping.entities = data.selected;
            })
            .jstree({
                'checkbox': {
                    'deselect_all': true,
                    'three_state': false // no cascade selection
                },
                'core': {
                    force_text: true,
                    'themes': {
                        'name': 'proton',
                        'responsive': true
                    },
                    'multiple': false,
                    'data': folders
                },
                'plugins': ['checkbox', 'search', 'sort']
            });
    }

}
