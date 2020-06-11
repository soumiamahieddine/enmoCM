import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
    selector: 'app-database',
    templateUrl: './database.component.html',
    styleUrls: ['./database.component.scss']
})
export class DatabaseComponent implements OnInit {

    stepFormGroup: FormGroup;

    constructor(
        private _formBuilder: FormBuilder
    ) { }

    ngOnInit(): void {
        this.stepFormGroup = this._formBuilder.group({
            firstCtrl: ['', Validators.required]
        });

        // this.stepFormGroup.controls['firstCtrl'].setValue(this.checkStep());
        // this.stepFormGroup.controls['firstCtrl'].markAsUntouched();
    }

    checkStep() {
        let state = 'success';
        return state;
    }

    isValidStep() {
        return this.stepFormGroup === undefined ? false : this.stepFormGroup.controls['firstCtrl'].value === 'success';
    }

    getFormGroup() {
        return this.stepFormGroup;
    }

}
