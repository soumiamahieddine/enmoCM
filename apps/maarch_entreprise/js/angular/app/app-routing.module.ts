import { NgModule }                         from '@angular/core';
import { RouterModule }                     from '@angular/router';

import { ProfileComponent }                 from './profile.component';
import { PasswordModificationComponent }    from './password-modification.component';
import { SignatureBookComponent }           from './signature-book.component';
import { SaveNumericPackageComponent }      from './save-numeric-package.component';


@NgModule({
    imports: [
        RouterModule.forRoot([
            { path: 'profile', component: ProfileComponent },
            { path: 'password-modification', component: PasswordModificationComponent },
            { path: 'saveNumericPackage', component: SaveNumericPackageComponent },
            { path: 'groups/:groupId/baskets/:basketId/signatureBook/:resId', component: SignatureBookComponent },
            { path: '**',   redirectTo: '', pathMatch: 'full' },
        ], { useHash: true }),
    ],
    exports: [
        RouterModule
    ]
})
export class AppRoutingModule {}
