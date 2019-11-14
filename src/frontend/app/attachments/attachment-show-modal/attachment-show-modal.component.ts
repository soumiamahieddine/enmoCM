import { Component, Inject, ViewChild } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { LANG } from '../../translate.component';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../../notification.service';
import { DocumentViewerComponent } from '../../viewer/document-viewer.component';

@Component({
    templateUrl: 'attachment-show-modal.component.html',
    styleUrls: ['attachment-show-modal.component.scss'],
})
export class AttachmentShowModalComponent {
    lang: any = LANG;

    pdfSrc: any = null;

    @ViewChild('appDocumentViewer', { static: true }) appDocumentViewer: DocumentViewerComponent;

    constructor(
        public http: HttpClient,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<AttachmentShowModalComponent>,
        private notify: NotificationService) {
    }

    ngOnInit(): void { 
        console.log(this.data);
    }
}
