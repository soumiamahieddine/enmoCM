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
    MdButtonToggleModule,
    MdProgressSpinnerModule,
    MdToolbarModule,
    MdMenuModule
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
        MdButtonToggleModule,
        MdProgressSpinnerModule,
        MdToolbarModule,
        MdMenuModule
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
        MdButtonToggleModule,
        MdProgressSpinnerModule,
        MdToolbarModule,
        MdMenuModule
    ]
})
export class AppMaterialModule { }
