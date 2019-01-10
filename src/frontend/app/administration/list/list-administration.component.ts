import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { CdkDragDrop, moveItemInArray, transferArrayItem } from '@angular/cdk/drag-drop';

declare function $j(selector: any): any;

@Component({
    templateUrl: "list-administration.component.html",
    styleUrls: ['list-administration.component.scss'],
    providers: [NotificationService],
})
export class ListAdministrationComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;
    loadingExport: boolean = false;

    @ViewChild('listFilter') private listFilter: any;

    delimiters = [';', ',',':','_TAB_'];
    encodings = ['UTF-8', 'ANSI'];

    exportModel: any = {
        delimiter : ';',
        encoding : 'UTF-8',
        datas : []
    };

    dataAvailable = [
        {
            label: 'Numéro GED',
            id: 'res_id'
        }, {
            label: 'Numéro Chrono',
            id: 'alt_identifier'
        }, {
            label: 'Categorie',
            id: 'category'
        }, {
            label: 'Date d\'arrivée',
            id: 'doc_date'
        }, {
            label: 'Date limite de traitement',
            id: 'process_limit_date'
        }, {
            label: 'Prénom de l\'expéditeur',
            id: 'contact_firstname'
        }, {
            label: 'Nom de l\'expéditeur',
            id: 'contact_lastname'
        }
    ];

    dataExport = [
        {
            label: 'Société de l\'expéditeur',
            id: 'contact_society'
        },
        {
            label: 'Service destinataire',
            id: 'destination'
        },
        {
            label: 'Destinataire',
            id: 'dest_user'
        },
        {
            label: 'Objet',
            id: 'subject'
        },
        {
            label: 'Type de courrier',
            id: 'type_id'
        }
    ];

    constructor(public http: HttpClient, private notify: NotificationService) { }

    ngOnInit(): void {
        /*this.http.get('../../rest/???')
            .subscribe((data: any) => {
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });*/
    }

    drop(event: CdkDragDrop<string[]>) {
        if (event.previousContainer === event.container) {
            moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        } else {
            const fakeIndex = $j('.available-data .columns')[event.previousIndex].id;
            const realIndex = this.dataAvailable.map((dataAv: any) => (dataAv.id)).indexOf(fakeIndex);
            transferArrayItem(event.previousContainer.data,
                event.container.data,
                realIndex,
                event.currentIndex);
            this.listFilter.nativeElement.value = '';
        }
    }

    exportData() {
        this.loadingExport = true;
        /*this.http.post('../../rest/???')
            .subscribe((data: any) => {
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });*/
    }

    addData(item: any, i: number) {
        const fakeIndex = $j('.available-data .columns')[i].id;
        const realIndex = this.dataAvailable.map((dataAv: any) => (dataAv.id)).indexOf(fakeIndex);

        transferArrayItem(this.dataAvailable,
            this.dataExport,
            realIndex,
            this.dataExport.length);

        this.listFilter.nativeElement.value = '';
    }

    removeData(i: number) {
        transferArrayItem(this.dataExport,
            this.dataAvailable,
            i,
            this.dataAvailable.length);
    }

    removeAllData() {
        this.dataAvailable = this.dataAvailable.concat(this.dataExport);
        this.dataExport = [];
    }

    addAllData() {
        this.dataExport = this.dataExport.concat(this.dataAvailable);
        this.dataAvailable = [];
        this.listFilter.nativeElement.value = '';
    }
}