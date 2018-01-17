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
    MatGridListModule,
    MatPaginator,
    MatTableDataSource
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
        MatGridListModule,
        MatPaginator,
        MatTableDataSource
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
        MatGridListModule,
        MatPaginator,
        MatTableDataSource
    ]
})
export class AppMaterialModule { }
