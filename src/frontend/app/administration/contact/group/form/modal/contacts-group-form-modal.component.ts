import { Component, Inject, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { FunctionsService } from '@service/functions.service';
import { ContactsGroupFormComponent } from '../contacts-group-form.component';

@Component({
    templateUrl: 'contacts-group-form-modal.component.html',
    styleUrls: ['contacts-group-form-modal.component.scss'],
})
export class ContactsGroupFormModalComponent implements OnInit{
    
    contactGroupId: number = null;

    constructor(
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<ContactsGroupFormModalComponent>,
        private functionsService: FunctionsService) {
    }

    ngOnInit(): void {
        this.contactGroupId = !this.functionsService.empty(this.data.contactGroupId) ? this.data.contactGroupId : null;
    }
}
