import { NgModule } from '@angular/core';

import { SharedModule } from '../app-common.module';

import { DocumentViewerComponent } from './document-viewer.component';
import { DocumentViewerModalComponent } from './modal/document-viewer-modal.component';

import { EcplOnlyofficeViewerComponent } from '../../plugins/onlyoffice-api-js/onlyoffice-viewer.component';

import { PdfViewerModule } from 'ng2-pdf-viewer';

@NgModule({
    imports: [
        SharedModule,
        PdfViewerModule
    ],
    declarations: [
        DocumentViewerComponent,
        EcplOnlyofficeViewerComponent,
        DocumentViewerModalComponent
    ],
    exports: [
        PdfViewerModule,
        EcplOnlyofficeViewerComponent,
        DocumentViewerComponent,
    ],
    entryComponents: [
        DocumentViewerModalComponent
    ]
})
export class DocumentViewerModule { }
