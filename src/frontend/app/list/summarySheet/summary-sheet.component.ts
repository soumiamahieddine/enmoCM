import { Component, OnInit, Inject, ViewEncapsulation } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { MAT_DIALOG_DATA } from '@angular/material';

declare function $j(selector: any): any;

@Component({
    templateUrl: "summary-sheet.component.html",
    styleUrls: ['summary-sheet.component.scss'],
    providers: [NotificationService],
    encapsulation: ViewEncapsulation.None
})
export class SummarySheetComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    withQrcode: boolean = true;

    dataAvailable: any[] = [
        {
            unit: 'primaryInformations',
            label: this.lang.primaryInformations,
            css: 'col-md-6 text-left',
            desc: [
                this.lang.mailDate, 
                this.lang.arrivalDate, 
                this.lang.nature, 
                this.lang.creation_date, 
                this.lang.mailType, 
                this.lang.initiator
            ],
            enabled: true
        },
        {
            unit: 'senderRecipientInformations',
            label: this.lang.senderRecipientInformations,
            css: 'col-md-6 text-left',
            desc: [
                this.lang.senders, 
                this.lang.recipients
            ],
            enabled: true
        },
        {
            unit: 'secondaryInformations',
            label: this.lang.secondaryInformations,
            css: 'col-md-6 text-left',
            desc: [
                this.lang.category_id,
                this.lang.status,
                this.lang.priority, 
                this.lang.processLimitDate
            ],
            enabled: true
        },
        {
            unit: 'diffusionList',
            label: this.lang.diffusionList,
            css: 'col-md-6 text-left',
            desc: [
                this.lang.dest_user, 
                this.lang.copyUsersEntities
            ],
            enabled: true
        },
        {
            unit: 'avisWorkflow',
            label: this.lang.avis,
            css: 'col-md-4 text-center',
            desc: [
                this.lang.firstname + ' ' + this.lang.lastname + ' ('+ this.lang.destinationEntity +')', 
                this.lang.role, 
                this.lang.processDate
            ],
            enabled: true
        },
        {
            unit: 'visaWorkflow',
            label: this.lang.visaWorkflow,
            css: 'col-md-4 text-center',
            desc: [
                this.lang.firstname + ' ' + this.lang.lastname + ' ('+ this.lang.destinationEntity +')', 
                this.lang.role, 
                this.lang.processDate
            ],
            enabled: true
        },
        {
            unit: 'notes',
            label: this.lang.notes,
            css: 'col-md-4 text-center',
            desc: [
                this.lang.firstname + ' ' + this.lang.lastname, 
                this.lang.creation_date, 
                this.lang.content
            ],
            enabled: true
        },
        {
            unit: 'freeField',
            label: this.lang.comments,
            css: 'col-md-12 text-left',
            desc: [
                this.lang.freeNote
            ],
            enabled: true
        }
    ];

    constructor(public http: HttpClient, private notify: NotificationService, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void { }

    drop(event: CdkDragDrop<string[]>) {
        if (event.previousContainer === event.container) {
            moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        }
    }

    genSummarySheets() {
        this.loading = true;
        let currElemData: any[] = [];

        if (this.withQrcode) {
            currElemData.push({
                unit: 'qrcode',
                label: '',
            });
        }
        this.dataAvailable.forEach((element: any) => {
            if (element.enabled) {
                currElemData.push({
                    unit: element.unit,
                    label: element.label,
                });
            }
        });
        this.http.get('../../rest/resourcesList/users/' + this.data.ownerId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/summarySheets?units=' + btoa(JSON.stringify(currElemData)) + '&init' + this.data.filters, { responseType: "blob" })
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

    toggleQrcode() {
        this.withQrcode = !this.withQrcode;
    }
}
