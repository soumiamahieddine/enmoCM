import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { HeaderService } from '../../../service/header.service';
import { NotificationService } from '../../notification.service';
import { MatSidenav } from '@angular/material';

declare function $j(selector: any): any;


@Component({
    templateUrl: "print-separator.component.html",
    styleUrls: ['print-separator.component.scss'],
})
export class PrintSeparatorComponent implements OnInit {

    lang: any = LANG;
    entities: any[] = [];
    entitiesChosen: any[] = [];
    loading: boolean = false;
    docUrl: string = '';
    separatorTypes: string [] = ['barcode', 'qrcode'];
    separatorTargets: string [] = ['entities', 'generic'];

    separator: any = {
        type : 'qrcode',
        target: 'entities',
        entities: []
    }

    @ViewChild('snav') sidenavLeft: MatSidenav;
    @ViewChild('snav2') sidenavRight: MatSidenav;

    constructor(public http: HttpClient, private headerService: HeaderService) {
        (<any>window).pdfWorkerSrc = '../../node_modules/pdfjs-dist/build/pdf.worker.min.js';
    }

    ngOnInit(): void {

        this.headerService.setHeader('Impression des séparateurs');

        this.http.get("../../rest/entities")
            .subscribe((data: any) => {
                this.entities = data['entities'];
                this.entities.forEach(entity => {
                    entity.state.disabled = false;
                });
                this.loadEntities();

            }, () => {
                location.href = "index.php";
            });
    }

    loadEntities() {

        setTimeout(() => {
            $j('#jstree').jstree({
                "checkbox": {
                    'deselect_all': true,
                    "three_state": false //no cascade selection
                },
                'core': {
                    'themes': {
                        'name': 'proton',
                        'responsive': true
                    },
                    'multiple': true,
                    'data': this.entities,
                },
                "plugins": ["checkbox", "search", "sort"]
            });
            var to: any = false;
            $j('#jstree_search').keyup(function () {
                if (to) { clearTimeout(to); }
                to = setTimeout(function () {
                    var v = $j('#jstree_search').val();
                    $j('#jstree').jstree(true).search(v);
                }, 250);
            });
            $j('#jstree')
                // listen for event
                .on('select_node.jstree', (e: any, data: any) => {
                    this.generateSeparators();

                }).on('deselect_node.jstree', (e: any, data: any) => {
                    this.generateSeparators();
                })
                // create the instance
                .jstree();
        }, 0);
    }

    generateSeparators() {
        this.loading = true;
        this.entitiesChosen = $j('#jstree').jstree(true).get_checked();

        if (this.entitiesChosen.length > 0) {
            this.docUrl = '../../rest/res/100/content';
        } else {
            this.docUrl = '';
        }
    }

    changeType(type: any) {
        if (type.value == 'entities') {
            this.docUrl = '';
            this.entities.forEach(entity => {
                entity.state.disabled = false;
            });
            $j('#jstree').jstree(true).settings.core.data = this.entities;
            $j('#jstree').jstree('deselect_all');
            $j('#jstree').jstree("refresh");
        } else {
            this.entities.forEach(entity => {
                entity.state.disabled = true;
            });
            $j('#jstree').jstree(true).settings.core.data = this.entities;
            $j('#jstree').jstree('deselect_all');
            $j('#jstree').jstree("refresh");
            this.sidenavRight.open();
            this.generateSeparators();
        }
    }
}
