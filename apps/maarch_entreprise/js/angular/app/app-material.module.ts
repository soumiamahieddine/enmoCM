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
    MdMenuModule,
    MdGridListModule
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
        MdMenuModule,
        MdGridListModule
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
        MdMenuModule,
        MdGridListModule
    ]
})
export class AppMaterialModule { }
