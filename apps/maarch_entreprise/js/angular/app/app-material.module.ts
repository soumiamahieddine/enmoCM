import { NgModule } from '@angular/core';
import {
    MdSelectModule,
    MdCheckboxModule,
    MdRadioModule
} from '@angular/material';


@NgModule({
    imports: [
        MdCheckboxModule,
        MdSelectModule,
        MdRadioModule
    ],
    exports: [
        MdCheckboxModule,
        MdSelectModule,
        MdRadioModule
    ]
})
export class AppMaterialModule { }
