import { NgModule }                             from '@angular/core';

import { SharedModule }                         from './app-common.module';

import { CustomSnackbarComponent }              from './notification.service';
import { ConfirmModalComponent }                from './confirmModal.component';
import { ShortcutMenuService }                  from '../service/shortcut-menu.service';
import { HeaderService }                        from '../service/header.service';
import { FiltersListService }                   from '../service/filtersList.service';

import { AppComponent }                         from './app.component';
import { AppRoutingModule }                     from './app-routing.module';
import { AdministrationModule }                 from './administration/administration.module';

import { ProfileComponent }                     from './profile.component';
import { AboutUsComponent }                     from './about-us.component';
import { HomeComponent }                        from './home.component';
import { BasketListComponent, BottomSheetNoteList, BottomSheetAttachmentList, BottomSheetDiffusionList }  from './list/basket-list.component';
import { PasswordModificationComponent, InfoChangePasswordModalComponent, }        from './password-modification.component';
import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';
import { SaveNumericPackageComponent }          from './save-numeric-package.component';
import { ActivateUserComponent }                from './activate-user.component';


import { FiltersListComponent }                from './list/filters/filters-list.component';

@NgModule({
    imports: [
        SharedModule,
        AdministrationModule,
        AppRoutingModule,
    ],
    declarations: [
        AppComponent,
        ProfileComponent,
        AboutUsComponent,
        HomeComponent,
        BasketListComponent,
        PasswordModificationComponent,
        SignatureBookComponent,
        SafeUrlPipe,
        SaveNumericPackageComponent,
        CustomSnackbarComponent,
        ConfirmModalComponent,
        InfoChangePasswordModalComponent,
        ActivateUserComponent,
        BottomSheetNoteList,
        BottomSheetAttachmentList,
        BottomSheetDiffusionList,
        FiltersListComponent
    ],
    entryComponents: [
        CustomSnackbarComponent,
        ConfirmModalComponent,
        InfoChangePasswordModalComponent,
        BottomSheetNoteList,
        BottomSheetAttachmentList,
        BottomSheetDiffusionList
    ],
    providers: [ ShortcutMenuService, HeaderService, FiltersListService ],
    bootstrap: [ AppComponent ]
})
export class AppModule { }
