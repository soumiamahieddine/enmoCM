import { NgModule } from '@angular/core';
import {
    MdSelectModule,
    MdCheckboxModule
} from '@angular/material';


@NgModule({
    imports: [
        MdCheckboxModule,
        MdSelectModule
    ],
    exports: [
        MdCheckboxModule,
        MdSelectModule
    ]
})
export class AppMaterialModule { }
