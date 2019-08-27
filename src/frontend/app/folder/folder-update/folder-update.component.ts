import { Component, OnInit, Input, Output, EventEmitter, Inject } from '@angular/core';
import { LANG } from '../../translate.component';
import { HttpClient } from '@angular/common/http';
import { map, tap, catchError, exhaustMap } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../../notification.service';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';

declare function $j(selector: any): any;

@Component({
    templateUrl: "folder-update.component.html",
    styleUrls: ['folder-update.component.scss'],
    providers: [NotificationService],
})
export class FolderUpdateComponent implements OnInit {

    lang: any = LANG;

    folder: any = {
        id: 0,
        label: '',
        public: true,
        user_id: 0,
        parent_id: 0,
        level: 0,
        sharing: {
            entities: []
        }
    }

    entities: any[] = [];

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public dialogRef: MatDialogRef<FolderUpdateComponent>,
        @Inject(MAT_DIALOG_DATA) public data: any
    ) { }

    ngOnInit(): void {
        this.getFolder();
    }

    getFolder() {
        this.http.get('../../rest/folders/' + this.data.folderId).pipe(
            tap((data: any) => this.folder = data.folder),
            exhaustMap(() => this.http.get('../../rest/entities')),
            map((data: any) => {
                this.entities = data.entities;
                data.entities.forEach((element: any) => {
                    if (this.folder.sharing.entities.map((data: any) => data.entity_id).indexOf(element.serialId) > -1) {
                        element.state.selected = true;
                    }
                    element.state.allowed = true;
                    element.state.disabled = false;
                });
                return data;
            }),
            tap((data: any) => {
                this.initEntitiesTree(data.entities);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    initEntitiesTree(entities: any) {
        $j('#jstree').jstree({
            "checkbox": {
                "three_state": false //no cascade selection
            },
            'core': {
                'themes': {
                    'name': 'proton',
                    'responsive': true
                },
                'data': entities
            },
            "plugins": ["checkbox", "search"]
        });
        $j('#jstree')
            // listen for event
            .on('select_node.jstree', (e: any, data: any) => {
                this.selectEntity(data.node.original);

            }).on('deselect_node.jstree', (e: any, data: any) => {
                this.deselectEntity(data.node.original);
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

    selectEntity(newEntity: any) {
        this.folder.sharing.entities.push(
            {
                entity_id : newEntity.serialId,
                edition : false
            }
        );
    }

    deselectEntity(entity: any) {
        let index = this.folder.sharing.entities.map((data: any) => data.entity_id).indexOf(entity.id);
        this.folder.sharing.entities.splice(index, 1);
    }

    onSubmit(): void {
        this.http.put('../../rest/folders/' + this.folder.id, this.folder).pipe(
            exhaustMap(() => this.http.put('../../rest/folders/' + this.folder.id + '/sharing', { public: this.folder.sharing.entities.length > 0, sharing: this.folder.sharing })),
            tap((data: any) => {
                this.notify.success('Dossier modifié');
                this.dialogRef.close();
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    checkSelectedFolder(entity: any) {
        if (this.folder.sharing.entities.map((data: any) => data.entity_id).indexOf(entity.serialId) > -1) {
            return true;
        } else {
            return false;
        }
    }
}
