import { NgModule }                         from '@angular/core';
import { RouterModule }                     from '@angular/router';

import { ActivateUserComponent }            from './activate-user.component';
import { PasswordModificationComponent }    from './password-modification.component';
import { ProfileComponent }                 from './profile.component';
import { AboutUsComponent }                 from './about-us.component';
import { HomeComponent }                    from './home.component';
import { BasketListComponent }              from './list/basket-list.component';
import { SignatureBookComponent }           from './signature-book.component';
import { SaveNumericPackageComponent }      from './save-numeric-package.component';

@NgModule({
    imports: [
        RouterModule.forRoot([
            { path: 'activate-user', component: ActivateUserComponent},
            { path: 'password-modification', component: PasswordModificationComponent },
            { path: 'profile', component: ProfileComponent },
            { path: 'about-us', component: AboutUsComponent },
            { path: 'home', component: HomeComponent },
            { path: 'basketList/users/:userSerialId/groups/:groupSerialId/baskets/:basketId', component: BasketListComponent },
            { path: 'saveNumericPackage', component: SaveNumericPackageComponent },
            { path: 'groups/:groupId/baskets/:basketId/signatureBook/:resId', component: SignatureBookComponent },
            { path: '**',   redirectTo: 'home', pathMatch: 'full' },
        ], { useHash: true }),
    ],
    exports: [
        RouterModule
    ]
})
export class AppRoutingModule {}
