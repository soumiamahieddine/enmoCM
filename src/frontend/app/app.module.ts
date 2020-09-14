import { NgModule, Injectable } from '@angular/core';

import { SharedModule } from './app-common.module';
import { AppRoutingModule } from './app-routing.module';

import { BrowserModule, HammerGestureConfig, HAMMER_GESTURE_CONFIG } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { InternationalizationModule } from '../service/translate/internationalization.module';

import { JoyrideModule } from 'ngx-joyride';

import { PanelListComponent } from './list/panel/panel-list.component';
import { ActionsModule } from './actions/actions.module';
import { DocumentViewerModule } from './viewer/document-viewer.module';
import { AppListModule } from './app-list.module';

import { AuthInterceptor } from '../service/auth-interceptor.service';
import { FiltersListService } from '../service/filtersList.service';
import { FoldersService } from './folder/folders.service';
import { PrivilegeService } from '../service/privileges.service';
import { ActionsService } from './actions/actions.service';

import { AppComponent } from './app.component';

// PROCESS
import { ProcessComponent } from './process/process.component';
import { IndexationComponent } from './indexation/indexation.component';
import { LinkResourceModalComponent } from './linkedResource/linkResourceModal/link-resource-modal.component';
import { HistoryWorkflowResumeComponent } from './history/history-workflow-resume/history-workflow-resume.component';
import { NoteResumeComponent } from './notes/note-resume/note-resume.component';
import { AttachmentsResumeComponent } from './attachments/attachments-resume/attachments-resume.component';
import { MailResumeComponent } from './mail/mail-resume/mail-resume.component';
import { SentResourceListComponent } from './sentResource/sent-resource-list.component';
import { SentResourcePageComponent } from './sentResource/sent-resource-page/sent-resource-page.component';
import { SentNumericPackagePageComponent } from './sentResource/sent-numeric-package-page/sent-numeric-package-page.component';
import { ThesaurusModalComponent } from './tag/indexing/thesaurus/thesaurus-modal.component';
import { SelectIndexingModelComponent } from './indexation/select-indexing-model/select-indexing-model.component';
import { DocumentFormModule } from './document-form.module';
import { FilterToolComponent } from './adv-search/filter-tool/filter-tool.component';

import { AdvSearchComponent } from './adv-search/adv-search.component';
import { AboutUsComponent } from './about-us.component';
import { ActivateUserComponent } from './activate-user.component';
import { AddAvisModelModalComponent } from './avis/addAvisModel/add-avis-model-modal.component';
import { AddPrivateIndexingModelModalComponent } from './indexation/private-indexing-model/add-private-indexing-model-modal.component';
import { AddVisaModelModalComponent } from './visa/addVisaModel/add-visa-model-modal.component';
import { AttachmentCreateComponent } from './attachments/attachment-create/attachment-create.component';
import { AttachmentPageComponent } from './attachments/attachments-page/attachment-page.component';
import { BasketListComponent } from './list/basket-list.component';
import { ContactModalComponent } from './administration/contact/modal/contact-modal.component';
import { ContactResourceModalComponent } from './contact/contact-resource/modal/contact-resource-modal.component';
import { DocumentViewerPageComponent } from './viewer/page/document-viewer-page.component';
import { FolderCreateModalComponent } from './folder/folder-create-modal/folder-create-modal.component';
import { FolderDocumentListComponent } from './folder/document-list/folder-document-list.component';
import { FolderPinnedComponent } from './folder/folder-pinned/folder-pinned.component';
import { FolderTreeComponent } from './folder/folder-tree.component';
import { FolderUpdateComponent } from './folder/folder-update/folder-update.component';
import { FollowedDocumentListComponent } from './home/followed-list/followed-document-list.component';
import { ForgotPasswordComponent } from './login/forgotPassword/forgotPassword.component';
import { HomeComponent } from './home/home.component';
import { LoginComponent } from './login/login.component';
import { MaarchParapheurListComponent } from './home/maarch-parapheur/maarch-parapheur-list.component';
import { PanelFolderComponent } from './folder/panel/panel-folder.component';
import { PasswordModificationComponent, InfoChangePasswordModalComponent, } from './login/passwordModification/password-modification.component';
import { PrintSeparatorComponent } from './separator/print-separator/print-separator.component';
import { ProfileComponent } from './profile.component';
import { RedirectIndexingModelComponent } from './administration/indexingModel/redirectIndexingModel/redirect-indexing-model.component';
import { ResetPasswordComponent } from './login/resetPassword/reset-password.component';
import { SaveNumericPackageComponent } from './save-numeric-package.component';
import { SignatureBookComponent } from './signature-book.component';
import { VisaWorkflowModalComponent } from './visa/modal/visa-workflow-modal.component';

import { DevToolComponent } from '../service/debug/dev-tool.component';
import { DevLangComponent } from '../service/debug/dev-lang.component';
import { AcknowledgementReceptionComponent } from './registeredMail/acknowledgement-reception/acknowledgement-reception.component';

@Injectable()
export class MyHammerConfig extends HammerGestureConfig {
    overrides = <any>{
        'pinch': { enable: false },
        'rotate': { enable: false }
    };
}

@NgModule({
    imports: [
        BrowserModule,
        BrowserAnimationsModule,
        HttpClientModule,
        InternationalizationModule,
        ActionsModule,
        DocumentFormModule,
        JoyrideModule.forRoot(),
        SharedModule,
        AppRoutingModule,
        DocumentViewerModule,
        AppListModule,
    ],
    declarations: [
        AppComponent,
        ProcessComponent,
        IndexationComponent,
        LinkResourceModalComponent,
        HistoryWorkflowResumeComponent,
        NoteResumeComponent,
        AttachmentsResumeComponent,
        MailResumeComponent,
        SentResourceListComponent,
        SentResourcePageComponent,
        SentNumericPackagePageComponent,
        ThesaurusModalComponent,
        SelectIndexingModelComponent,
        FilterToolComponent,
        PanelListComponent,
        AdvSearchComponent,
        AboutUsComponent,
        ActivateUserComponent,
        AddAvisModelModalComponent,
        AddPrivateIndexingModelModalComponent,
        AddVisaModelModalComponent,
        AttachmentCreateComponent,
        AttachmentPageComponent,
        BasketListComponent,
        ContactModalComponent,
        ContactResourceModalComponent,
        DocumentViewerPageComponent,
        FolderCreateModalComponent,
        FolderDocumentListComponent,
        FolderPinnedComponent,
        FolderTreeComponent,
        FolderUpdateComponent,
        FollowedDocumentListComponent,
        ForgotPasswordComponent,
        HomeComponent,
        InfoChangePasswordModalComponent,
        LoginComponent,
        MaarchParapheurListComponent,
        PanelFolderComponent,
        PasswordModificationComponent,
        PrintSeparatorComponent,
        ProfileComponent,
        RedirectIndexingModelComponent,
        ResetPasswordComponent,
        SaveNumericPackageComponent,
        SignatureBookComponent,
        VisaWorkflowModalComponent,
        DevLangComponent,
        DevToolComponent,
        AcknowledgementReceptionComponent
    ],
    exports : [
        SharedModule
    ],
    entryComponents: [
        LinkResourceModalComponent,
        SentResourcePageComponent,
        SentNumericPackagePageComponent,
        ThesaurusModalComponent,
        AddAvisModelModalComponent,
        AddPrivateIndexingModelModalComponent,
        AddVisaModelModalComponent,
        AttachmentCreateComponent,
        AttachmentPageComponent,
        ContactModalComponent,
        ContactResourceModalComponent,
        FolderCreateModalComponent,
        FolderUpdateComponent,
        InfoChangePasswordModalComponent,
        RedirectIndexingModelComponent,
        VisaWorkflowModalComponent,
        DevLangComponent
    ],
    providers: [{ provide: HTTP_INTERCEPTORS, useClass: AuthInterceptor, multi: true }, FiltersListService, FoldersService, ActionsService, PrivilegeService, {
        provide: HAMMER_GESTURE_CONFIG,
        useClass: MyHammerConfig
    }],
    bootstrap: [AppComponent]
})
export class AppModule { }
