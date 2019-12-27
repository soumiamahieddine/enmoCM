import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { LANG } from '../../../translate.component';
import { HttpClient } from '@angular/common/http';
import { PrivilegeService } from '../../../../service/privileges.service';

@Component({
    templateUrl: 'contact-modal.component.html',
    styleUrls: ['contact-modal.component.scss'],
})
export class ContactModalComponent {
    lang: any = LANG;
    creationMode: boolean = true;
    canUpdate: boolean = false;
    contact: any = null;
    mode: 'update' | 'read' = 'read';

    constructor(
        public http: HttpClient,
        private privilegeService: PrivilegeService,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<ContactModalComponent>) {
    }

    ngOnInit(): void {
        if (this.data.contactId !== null) {
            this.contact = {
                id: this.data.contactId,
                type: this.data.contactType
            }
            this.creationMode = false;
        } else {
            this.creationMode = true;
        }
        this.canUpdate = this.privilegeService.hasCurrentUserPrivilege('update_contacts');
    }
}
