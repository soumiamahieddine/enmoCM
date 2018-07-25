import { NgModule }                         from '@angular/core';
import { RouterModule }                     from '@angular/router';

import { ProfileComponent }                 from './profile.component';
import { AboutUsComponent }                 from './about-us.component';
import { PasswordModificationComponent }    from './password-modification.component';
import { SignatureBookComponent }           from './signature-book.component';
import { SaveNumericPackageComponent }      from './save-numeric-package.component';
import { ActivateUserComponent }            from './activate-user.component';

@NgModule({
    imports: [
        RouterModule.forRoot([
            { path: 'profile', component: ProfileComponent },
            { path: 'about-us', component: AboutUsComponent },
            { path: 'password-modification', component: PasswordModificationComponent },
            { path: 'saveNumericPackage', component: SaveNumericPackageComponent },
            { path: 'activateUser', component: ActivateUserComponent},
            { path: 'groups/:groupId/baskets/:basketId/signatureBook/:resId', component: SignatureBookComponent },
            { path: '**',   redirectTo: '', pathMatch: 'full' },
        ], { useHash: true }),
    ],
    exports: [
        RouterModule
    ]
})
export class AppRoutingModule {}
