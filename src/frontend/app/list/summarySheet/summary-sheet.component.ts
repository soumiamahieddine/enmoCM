import { Component, OnInit, ViewChild, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import {MAT_DIALOG_DATA} from '@angular/material';

declare function $j(selector: any): any;

@Component({
    templateUrl : "summary-sheet.component.html",
    styleUrls   : ['summary-sheet.component.scss'],
    providers   : [NotificationService],
})
export class SummarySheetComponent implements OnInit {

    lang            : any       = LANG;
    loading         : boolean   = false;

    dataAvailable : any[] = [
        {
            unit : 'primaryInformations',
            label : 'Informations pricipales',
            css: 'col-md-6 text-center',
            desc : ['Date du courrier','Date d\'arrivée','Nature', 'Crée le', 'Type de document', 'Opérateur'],
            enabled : true
        },
        {
            unit : 'senderRecipientInformations',
            label : 'Informations de destination',
            css: 'col-md-6 text-center',
            desc : ['Expéditeur','Destinataire'],
            enabled : true
        },
        {
            unit : 'secondaryInformations',
            label : 'Informations secondaires',
            css: 'col-md-6 text-center',
            desc : ['Catégorie','Statut','Priorité', 'Date limite de traitement'],
            enabled : true
        },
        {
            unit : 'diffusionList',
            label : 'Liste de diffusion',
            css: 'col-md-12 text-center',
            desc : ['Attributaire','En copie(s)'],
            enabled : true
        },
        {
            unit : 'avisWorkflow',
            label : 'Circuit d\'avis',
            css: 'col-md-4 text-center',
            desc : ['Prénom Nom (entité traitante)','Rôle', 'Date de traitement'],
            enabled : true
        },
        {
            unit : 'visaWorkflow',
            label : 'Circuit de visa',
            css: 'col-md-4 text-center',
            desc : ['Prénom Nom (entité traitante)','Rôle', 'Date de traitement'],
            enabled : true
        },
        {
            unit : 'notes',
            label : 'Annotations',
            css: 'col-md-4 text-center',
            desc : ['Prénom Nom','Date de création','Contenu'],
            enabled : true
        },
        {
            unit : 'freeField',
            label : 'Commentaire(s)',
            css: 'col-md-12 text-center',
            desc : ['Note libre'],
            enabled : true
        }
    ];

    constructor(public http: HttpClient, private notify: NotificationService, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
       //TO DO GET PARAM SUMMARY SHEET
    }

    drop(event: CdkDragDrop<string[]>) {
        if (event.previousContainer === event.container) {
            moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        }
    }

    genSummarySheets() {
        this.loading = true;
        let currElemData: any[] = [{
            unit : 'qrcode',
            label : '',
        }];
        this.dataAvailable.forEach((element: any) => {
            if (element.enabled) {
                currElemData.push({
                    unit : element.unit,
                    label : element.label,
                });
            }
        });
        this.http.get('../../rest/resourcesList/users/' + this.data.ownerId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/summarySheets?units=' + btoa(JSON.stringify(currElemData)) + '&init' + this.data.filters, {responseType: "blob"})
            .subscribe((data) => {
                let downloadLink = document.createElement('a');
                downloadLink.href = window.URL.createObjectURL(data);
                downloadLink.setAttribute('download', "summary_sheet.pdf");
                document.body.appendChild(downloadLink);
                downloadLink.click();

                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });
    }
}
