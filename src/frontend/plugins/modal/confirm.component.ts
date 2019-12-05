import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { LANG } from '../../app/translate.component';

@Component({
    templateUrl: 'confirm.component.html',
    styleUrls: ['confirm.component.scss']
})
export class ConfirmComponent {

    lang: any = LANG;

    constructor(@Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<ConfirmComponent>) {
        if (this.data.msg === null) {
            this.data.msg = '';
        }

        if (this.data.buttonCancel === undefined) {
            this.data.buttonCancel = this.lang.cancel;
        }

        if (this.data.buttonValidate === undefined) {
            this.data.buttonValidate = this.lang.ok;
        }
    }
}
