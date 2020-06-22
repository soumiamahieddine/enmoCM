import { NgModule } from '@angular/core';

import { SharedModule } from './app-common.module';

import { SignatureBookRoutingModule } from './signature-book-routing.module';
import { SignatureBookComponent } from './signature-book.component';
import { NoteModule } from './notes/note.module';


@NgModule({
    imports: [
        SharedModule,
        SignatureBookRoutingModule,
        NoteModule
    ],
    declarations: [SignatureBookComponent],
    entryComponents: [

    ]
})
export class SignatureBookModule { }
