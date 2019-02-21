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
import { BasketListComponent }                  from './list/basket-list.component';
import { PasswordModificationComponent, InfoChangePasswordModalComponent, }        from './password-modification.component';
import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';
import { SaveNumericPackageComponent }          from './save-numeric-package.component';
import { ActivateUserComponent }                from './activate-user.component';

import { ActionsListComponent }                 from './actions/actions-list.component';
/*ACTIONS PAGES */
import { ConfirmActionComponent }               from './actions/confirm-action/confirm-action.component';
import { ClosingActionComponent }               from './actions/closing-action/closing-action.component';

import { FiltersListComponent }                 from './list/filters/filters-list.component';
import { FiltersToolComponent }                 from './list/filters/filters-tool.component';
import { SummarySheetComponent }                from './list/summarySheet/summary-sheet.component';
import { ExportComponent }                      from './list/export/export.component';

import { NoteEditorComponent }                  from './notes/note-editor.component';
import { NotesListComponent }                   from './notes/notes.component';
import { AttachmentsListComponent }             from './attachments/attachments-list.component';
import { DiffusionsListComponent }             from './diffusions/diffusions-list.component';



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
        NotesListComponent,
        NoteEditorComponent,
        AttachmentsListComponent,
        DiffusionsListComponent,
        FiltersListComponent,
        FiltersToolComponent,
        SummarySheetComponent,
        ExportComponent,
        ConfirmActionComponent,
        ClosingActionComponent,
        ActionsListComponent,
    ],
    entryComponents: [
        CustomSnackbarComponent,
        ConfirmModalComponent,
        InfoChangePasswordModalComponent,
        NotesListComponent,
        AttachmentsListComponent,
        DiffusionsListComponent,
        SummarySheetComponent,
        ExportComponent,
        ConfirmActionComponent,
        ClosingActionComponent,
    ],
    providers: [ ShortcutMenuService, HeaderService, FiltersListService ],
    bootstrap: [ AppComponent ]
})
export class AppModule { }
