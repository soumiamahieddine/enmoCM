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
            label : this.lang.resId,
            isFunction : false
        },
        {
            value : 'type_label',
            label : this.lang.doctype,
            isFunction : false
        },
        {
            value : 'doctypes_first_level_label',
            label : this.lang.firstLevelDoctype,
            isFunction : false
        },
        {
            value : 'doctypes_second_level_label',
            label : this.lang.secondLevelDoctype,
            isFunction : false
        },
        {
            value : 'format',
            label : this.lang.format,
            isFunction : false
        },
        {
            value : 'doc_date',
            label : this.lang.docDate,
            isFunction : false
        },
        {
            value : 'reference_number',
            label : this.lang.reference,
            isFunction : false
        },
        {
            value : 'departure_date',
            label : this.lang.departureDate,
            isFunction : false
        },
        {
            value : 'department_number_id',
            label : this.lang.department,
            isFunction : false
        },
        {
            value : 'barcode',
            label : this.lang.barcode,
            isFunction : false
        },
        {
            value : 'fold_status',
            label : this.lang.folderStatus,
            isFunction : false
        },
        {
            value : 'folder_name',
            label : this.lang.folderName,
            isFunction : false
        },
        {
            value : 'confidentiality',
            label : this.lang.confidentiality,
            isFunction : false
        },
        {
            value : 'nature_id',
            label : this.lang.nature,
            isFunction : false
        },
        {
            value : 'alt_identifier',
            label : this.lang.chronoNumber,
            isFunction : false
        },
        {
            value : 'admission_date',
            label : this.lang.admissionDate,
            isFunction : false
        },
        {
            value : 'process_limit_date',
            label : this.lang.processLimitDate,
            isFunction : false
        },
        {
            value : 'recommendation_limit_date',
            label : this.lang.recommendationLimitDate,
            isFunction : false
        },
        {
            value : 'closing_date',
            label : this.lang.closingDate,
            isFunction : false
        },
        {
            value : 'sve_start_date',
            label : this.lang.sveStartDate,
            isFunction : false
        },
        {
            value : 'subject',
            label : this.lang.subject,
            isFunction : false
        },
        {
            value : 'case_label',
            label : this.lang.caseLabel,
            isFunction : false
        },
        {
            value : 'getStatus',
            label : this.lang.status,
            isFunction : true
        },
        {
            value : 'getPriority',
            label : this.lang.priority,
            isFunction : true
        },
        {
            value : 'getCopyEntities',
            label : this.lang.copyEntities,
            isFunction : true
        },
        {
            value : 'getDetailLink',
            label : this.lang.detailLink,
            isFunction : true
        },
        {
            value : 'getParentFolder',
            label : this.lang.parentFolder,
            isFunction : true
        },
        {
            value : 'getCategory',
            label : this.lang.category_id,
            isFunction : true
        },
        {
            value : 'getInitiatorEntity',
            label : this.lang.initiatorEntity,
            isFunction : true
        },
        {
            value : 'getDestinationEntity',
            label : this.lang.destinationEntity,
            isFunction : true
        },
        {
            value : 'getDestinationEntityType',
            label : this.lang.destinationEntityType,
            isFunction : true
        },
        {
            value : 'getSender',
            label : this.lang.sender,
            isFunction : true
        },
        {
            value : 'getRecipient',
            label : this.lang.recipient,
            isFunction : true
        },
        {
            value : 'getTypist',
            label : this.lang.typist,
            isFunction : true
        },
        {
            value : 'getAssignee',
            label : this.lang.dest_user,
            isFunction : true
        },
        {
            value : 'getTags',
            label : this.lang.tags,
            isFunction : true
        },
        {
            value : 'getSignatories',
            label : this.lang.signUser,
            isFunction : true
        },
        {
            value : 'getSignatureDates',
            label : this.lang.signatureDate,
            isFunction : true
        },
        {
            value : '',
            label : this.lang.comment,
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

    addData(item: any) {
        var realIndex = 0;

        this.dataAvailable.forEach((value : any, index : number) => {
            if (value.value == item.value) {
                realIndex = index;
            }
        });

        transferArrayItem(this.dataAvailable, this.exportModel.data, realIndex, this.exportModel.data.length);
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
