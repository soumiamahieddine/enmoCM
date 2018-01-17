import { NgModule } from '@angular/core';
import {
    MatSelectModule,
    MatCheckboxModule,
    MatSlideToggleModule,
    MatInputModule,
    MatTooltipModule,
    MatTabsModule,
    MatSidenavModule,
    MatButtonModule,
    MatCardModule,
    MatButtonToggleModule,
    MatProgressSpinnerModule,
    MatToolbarModule,
    MatMenuModule,
    MatGridListModule
} from '@angular/material';


@NgModule({
    imports: [
        MatCheckboxModule,
        MatSelectModule,
        MatSlideToggleModule,
        MatInputModule,
        MatTooltipModule,
        MatTabsModule,
        MatSidenavModule,
        MatButtonModule,
        MatCardModule,
        MatButtonToggleModule,
        MatProgressSpinnerModule,
        MatToolbarModule,
        MatMenuModule,
        MatGridListModule
    ],
    exports: [
        MatCheckboxModule,
        MatSelectModule,
        MatSlideToggleModule,
        MatInputModule,
        MatTooltipModule,
        MatTabsModule,
        MatSidenavModule,
        MatButtonModule,
        MatCardModule,
        MatButtonToggleModule,
        MatProgressSpinnerModule,
        MatToolbarModule,
        MatMenuModule,
        MatGridListModule
    ]
})
export class AppMaterialModule { }
