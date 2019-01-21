import { Component, OnInit, ViewChild, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { CdkDragDrop, moveItemInArray, transferArrayItem } from '@angular/cdk/drag-drop';
import {MAT_DIALOG_DATA} from '@angular/material';

declare function $j(selector: any): any;

@Component({
    templateUrl : "list-administration.component.html",
    styleUrls   : ['list-administration.component.scss'],
    providers   : [NotificationService],
})
export class ListAdministrationComponent implements OnInit {

    lang            : any       = LANG;
    loading         : boolean   = false;
    loadingExport   : boolean   = false;

    delimiters          = [';', ',', 'TAB'];
    exportModel : any   = {
        delimiter   : ';',
        data        : []
    };

    dataAvailable : any[] = [
        {
            value : 'res_id',
            label : 'Identifiant du document',
            isFunction : false
        },
        {
            value : 'type_label',
            label : 'Typologie du document',
            isFunction : false
        },
        {
            value : 'doctypes_first_level_label',
            label : 'Premier niveau typologique du document',
            isFunction : false
        },
        {
            value : 'doctypes_second_level_label',
            label : 'Second niveau typologique du document',
            isFunction : false
        },
        {
            value : 'format',
            label : 'Format',
            isFunction : false
        },
        {
            value : 'doc_date',
            label : 'Date du document',
            isFunction : false
        },
        {
            value : 'reference_number',
            label : 'Référence',
            isFunction : false
        },
        {
            value : 'departure_date',
            label : 'Date de départ',
            isFunction : false
        },
        {
            value : 'department_number_id',
            label : 'Départements',
            isFunction : false
        },
        {
            value : 'barcode',
            label : 'Code barre',
            isFunction : false
        },
        {
            value : 'fold_status',
            label : 'Status du dossier',
            isFunction : false
        },
        {
            value : 'folder_name',
            label : 'Libellé du dossier',
            isFunction : false
        },
        {
            value : 'confidentiality',
            label : 'Confidentialité',
            isFunction : false
        },
        {
            value : 'nature_id',
            label : 'Nature du courrier',
            isFunction : false
        },
        {
            value : 'alt_identifier',
            label : 'Numéro chrono',
            isFunction : false
        },
        {
            value : 'admission_date',
            label : 'Date d\'admission',
            isFunction : false
        },
        {
            value : 'process_limit_date',
            label : 'Date limite de traitement',
            isFunction : false
        },
        {
            value : 'recommendation_limit_date',
            label : 'Date limite de demande d\'avis',
            isFunction : false
        },
        {
            value : 'closing_date',
            label : 'Date de clôture',
            isFunction : false
        },
        {
            value : 'sve_start_date',
            label : 'Date de début SVE',
            isFunction : false
        },
        {
            value : 'subject',
            label : 'Sujet',
            isFunction : false
        },
        {
            value : 'case_label',
            label : 'Libellé de l\'affaire du courrier',
            isFunction : false
        },
        {
            value : 'getStatus',
            label : 'Status',
            isFunction : true
        },
        {
            value : 'getPriority',
            label : 'Priorité',
            isFunction : true
        },
        {
            value : 'getCopyEntities',
            label : 'Entités en copie',
            isFunction : true
        },
        {
            value : 'getDetailLink',
            label : 'Lien vers la fiche détaillé',
            isFunction : true
        },
        {
            value : 'getParentFolder',
            label : 'Dossier parent',
            isFunction : true
        },
        {
            value : 'getCategory',
            label : 'Catégorie',
            isFunction : true
        },
        {
            value : 'getInitiatorEntity',
            label : 'Libellé de l\'entité initiatrice',
            isFunction : true
        },
        {
            value : 'getDestinationEntity',
            label : 'Libellé de l\'entité traitante',
            isFunction : true
        },
        {
            value : 'getDestinationEntityType',
            label : 'Type de l\'entité traitante',
            isFunction : true
        },
        {
            value : 'getSender',
            label : 'Expéditeur',
            isFunction : true
        },
        {
            value : 'getRecipient',
            label : 'Destinataire',
            isFunction : true
        },
        {
            value : 'getTypist',
            label : 'Rédacteur',
            isFunction : true
        },
        {
            value : 'getAssignee',
            label : 'Attributaire',
            isFunction : true
        },
        {
            value : 'getTags',
            label : 'Mots-clés',
            isFunction : true
        },
        {
            value : 'getSignatories',
            label : 'Signataires',
            isFunction : true
        },
        {
            value : 'getSignatureDates',
            label : 'Dates de signature',
            isFunction : true
        },
        {
            value : '',
            label : 'Commentaires',
            isFunction : true
        }
    ];

    @ViewChild('listFilter') private listFilter: any;


    constructor(public http: HttpClient, private notify: NotificationService, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        this.http.get('../../rest/resourcesList/exportTemplate')
            .subscribe((data: any) => {
                if (data["delimiter"] != '') {
                    this.exportModel.data = data["template"];
                    this.exportModel.delimiter = data["delimiter"];
                    this.exportModel.data.forEach((value : any) => {
                        this.dataAvailable.forEach((availableValue : any, index : number) => {
                            if (value.value == availableValue.value) {
                                this.dataAvailable.splice(index, 1);
                            }
                        });
                    });
                }
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });
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
        this.http.put('../../rest/resourcesList/users/' + this.data.ownerId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/exports?init' + this.data.filters, this.exportModel, {responseType: "blob"})
            .subscribe((data) => {
                let downloadLink = document.createElement('a');
                downloadLink.href = window.URL.createObjectURL(data);
                downloadLink.setAttribute('download', "export_maarch.csv");
                document.body.appendChild(downloadLink);
                downloadLink.click();

                this.loadingExport = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
            });
    }

    addData(i: number) {
        transferArrayItem(this.dataAvailable, this.exportModel.data, i, this.exportModel.data.length);
        this.listFilter.nativeElement.value = '';
    }

    removeData(i: number) {
        transferArrayItem(this.exportModel.data, this.dataAvailable, i, this.dataAvailable.length);
    }

    removeAllData() {
        this.dataAvailable = this.dataAvailable.concat(this.exportModel.data);
        this.exportModel.data = [];
    }

    addAllData() {
        this.exportModel.data = this.exportModel.data.concat(this.dataAvailable);
        this.dataAvailable = [];
        this.listFilter.nativeElement.value = '';
    }
}
