import { NgModule }              from '@angular/core';
import { RouterModule, Routes }  from '@angular/router';

import { ProfileComponent }                     from './profile.component';
import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';


@NgModule({
    imports: [
        RouterModule.forRoot([
            { path: 'profile', component: ProfileComponent },
            { path: ':basketId/signatureBook/:resId', component: SignatureBookComponent },
            { path: '**',   redirectTo: '', pathMatch: 'full' },
        ], { useHash: true }),
    ],
    exports: [
        RouterModule
    ]
})
export class AppRoutingModule {}
