import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { TestComponent } from './test/test.component';
import { VersementComponent } from './versement/versement.component';

const routes: Routes = [
  {
    path: 'taskpane',
    component: TestComponent
  },
  {
    path: '**',
    redirectTo: 'taskpane',
    pathMatch: 'full'
  },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule {
}
