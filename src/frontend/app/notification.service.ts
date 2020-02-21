import { MatSnackBar } from '@angular/material/snack-bar';
import { Injectable, Component, Inject } from '@angular/core';
import { MAT_SNACK_BAR_DATA } from '@angular/material/snack-bar';
import { Router } from '@angular/router';
import { LANG } from './translate.component';
@Component({
    selector: 'custom-snackbar',
    templateUrl: "notification.service.html",
    styleUrls: ['notification.service.scss'],
})
export class CustomSnackbarComponent {
    constructor(@Inject(MAT_SNACK_BAR_DATA) public data: any) { }
}

@Injectable()
export class NotificationService {
    lang: any = LANG;

    constructor(private router: Router, public snackBar: MatSnackBar) {
    }
    success(message: string) {
        this.snackBar.openFromComponent(CustomSnackbarComponent, {
            duration: 2000,
            panelClass: 'success-snackbar',
            verticalPosition : 'top',
            data: { message: message, icon: 'info-circle' }
        });
    }

    error(message: string, url: string = null) {
        this.snackBar.openFromComponent(CustomSnackbarComponent, {
            duration: 4000,
            panelClass: 'error-snackbar',
            verticalPosition : 'top',
            data: { url: url, message: message, icon: 'exclamation-triangle' }
        });
    }

    handleErrors(err: any) {
        console.log(err);
        if (err.status === 401 && this.router.url !== '/home') {
            this.router.navigate(['/home']);
            window.location.reload(true);
            this.error(this.lang.mustReconnect);
        } else if (err.status === 0 && err.statusText === 'Unknown Error') {
            this.error(this.lang.connectionFailed);
        } else {
            if (err.error !== undefined) {
                if (err.error.errors !== undefined) {
                    if (err.error.lang !== undefined) {
                        this.error('lang.' + err.error.lang, err.url);
                    } else {
                        this.error(err.error.errors, err.url);
                    }
                    if (err.status === 403 || err.status === 404) {
                        this.router.navigate(['/home']);
                    }
                } else if (err.error.exception !== undefined) {
                    this.error(err.error.exception[0].message, err.url);
                } else if (err.error.error !== undefined) {
                    this.error(err.error.error.message, err.url);
                } else {
                    this.error(`${err.status} : ${err.statusText}`, err.url);
                }
            } else {
                this.error(err);
            }
        }
    }

    handleSoftErrors(err: any) {
        console.log(err);
        if (err.error !== undefined) {
            if (err.error.errors !== undefined) {
                this.error(err.error.errors, err.url);
            } else if (err.error.exception !== undefined) {
                this.error(err.error.exception[0].message, err.url);
            } else if (err.error.error !== undefined) {
                this.error(err.error.error.message, err.url);
            } else {
                this.error(`${err.status} : ${err.statusText}`, err.url);
            }
        } else {
            this.error(err);
        }
    }
}
