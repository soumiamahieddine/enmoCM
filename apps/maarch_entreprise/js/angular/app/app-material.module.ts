import { NgModule } from '@angular/core';
import {
    MdSelectModule,
    MdCheckboxModule,
    MdSlideToggleModule,
    MdInputModule,
    MdTooltipModule,
    MdTabsModule,
    MdSidenavModule,
    MdButtonModule,
    MdCardModule,
    MdButtonToggleModule
} from '@angular/material';


@NgModule({
    imports: [
        MdCheckboxModule,
        MdSelectModule,
        MdSlideToggleModule,
        MdInputModule,
        MdTooltipModule,
        MdTabsModule,
        MdSidenavModule,
        MdButtonModule,
        MdCardModule,
        MdButtonToggleModule
    ],
    exports: [
        MdCheckboxModule,
        MdSelectModule,
        MdSlideToggleModule,
        MdInputModule,
        MdTooltipModule,
        MdTabsModule,
        MdSidenavModule,
        MdButtonModule,
        MdCardModule,
        MdButtonToggleModule
    ]
})
export class AppMaterialModule { }
