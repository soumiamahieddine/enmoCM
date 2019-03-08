import { Component, OnInit, Inject } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';

@Component({
    templateUrl: "view-doc-action.component.html",
    styleUrls: ['view-doc-action.component.scss'],
    providers: [NotificationService],
})
export class ViewDocActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;
    docUrl: string = '';
    innerHtml: SafeHtml;

    constructor(private notify: NotificationService, public dialogRef: MatDialogRef<ViewDocActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any, public sanitizer: DomSanitizer) { }

    ngOnInit(): void {
        this.docUrl = '../../rest/res/' + this.data.selectedRes[0] + '/content';
            this.innerHtml = this.sanitizer.bypassSecurityTrustHtml(
                "<iframe style='position: absolute;width: 100%;height: 100%;border: none;' src='" + this.docUrl + "' class='embed-responsive-item'>" +
                "</iframe>");
    }
}
