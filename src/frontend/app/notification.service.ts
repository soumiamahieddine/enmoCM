import {MatSnackBar} from '@angular/material';
import {Injectable,Component,Inject} from '@angular/core';
import {MAT_SNACK_BAR_DATA} from '@angular/material';
import { Router } from '@angular/router';

@Component({
    selector: 'custom-snackbar',
    template: '<mat-grid-list cols="4" rowHeight="1:1"><mat-grid-tile colspan="1"><mat-icon class="fa fa-{{data.icon}} fa-2x"></mat-icon></mat-grid-tile><mat-grid-tile colspan="3">{{data.message}}</mat-grid-tile></mat-grid-list>' // You may also use a HTML file
})
export class CustomSnackbarComponent {
    constructor(@Inject(MAT_SNACK_BAR_DATA) public data: any) { }
}

@Injectable()
export class NotificationService {
    constructor(private router: Router, public snackBar: MatSnackBar) {
    }
    success(message:string) {
        this.snackBar.openFromComponent(CustomSnackbarComponent,{
            duration: 2000,
            data: {message: message,icon : 'info-circle'}
          });
    }
         
    error(message:string) {
        this.snackBar.openFromComponent(CustomSnackbarComponent,{
            duration: 2000,
            data: {message: message,icon : 'exclamation-triangle'}
          });
    }

    handleErrors(err: any) {
        console.log(err);
        if (err.status === 401 && this.router.url !== '/login') {
            this.router.navigate(['/login']);
            this.error('Veuillez vous reconnecter');
        } else if (err.status === 0 && err.statusText === 'Unknown Error') {
            this.error('La connexion au serveur a échoué. Veuillez réessayer ultérieurement.');
        } else {
            if (err.error.errors !== undefined) {
                this.error(err.error.errors);
                if (err.status === 403 || err.status === 404) {
                    this.router.navigate(['/documents']);
                }
            } else if (err.error.exception !== undefined) {
                this.error(err.error.exception[0].message);
            } else {
                this.error(err.error.error.message);
            }
        }
    }
}