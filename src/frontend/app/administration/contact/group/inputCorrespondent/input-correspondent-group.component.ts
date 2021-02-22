import { Component, ElementRef, ErrorHandler, Input, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { FunctionsService } from '@service/functions.service';
import { HeaderService } from '@service/header.service';
import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { FormControl } from '@angular/forms';
import { MatAutocompleteSelectedEvent, MatAutocomplete } from '@angular/material/autocomplete';
import { Observable, of } from 'rxjs';
import { catchError, map, tap } from 'rxjs/operators';
import { NotificationService } from '@service/notification/notification.service';

@Component({
    selector: 'app-input-correspondent-group',
    templateUrl: './input-correspondent-group.component.html',
    styleUrls: ['./input-correspondent-group.component.scss'],
})

export class InputCorrespondentGroup implements OnInit{ 

  @Input() id: number;
  @Input() type: string;

  visible = true;
  separatorKeysCodes: number[] = [ENTER, COMMA];
  correspondentGroupsForm = new FormControl();
  filteredcorrespondentGroups: Observable<string[]>;
  correspondentGroups: any[] = [];
  allCorrespondentGroups: any[] = ['hamza'];

  @ViewChild('correspondentGroupsInput') correspondentGroupsInput: ElementRef<HTMLInputElement>;
  @ViewChild('auto') matAutocomplete: MatAutocomplete;

  constructor(
    public translate: TranslateService,
    public http: HttpClient,
    private notify: NotificationService,
    public functionsService: FunctionsService,
    public headerService: HeaderService,
  ) {
    this.filteredcorrespondentGroups = this.correspondentGroupsForm.valueChanges.pipe(
      map((item: any | null) => item ? this._filter(item) : this.allCorrespondentGroups.map((el: any) => { return el.label })));
  }
  async ngOnInit(): Promise<void> {
    await this.getCorrespondentsGroup();
  }

  getCorrespondentsGroup() {
    return new Promise((resolve, reject) => {
      this.http.get('../rest/contactsGroups').pipe(
          tap((data: any) => {
            this.allCorrespondentGroups = data.contactsGroups;   
            resolve(true);
          }),
          catchError((err: any) => {
              this.notify.handleSoftErrors(err);
              return of(false);
          })
      ).subscribe();
    });
  }

  remove(item: any): void {
    const index = this.correspondentGroups.indexOf(item);
    this.allCorrespondentGroups.push(item);    
    if (index >= 0) {
      this.correspondentGroups.splice(index, 1);
    }
  }

  selected(event: MatAutocompleteSelectedEvent): void {   
    const element = this.allCorrespondentGroups.filter((item: any) => item.label === event.option.viewValue)[0];
    this.correspondentGroups.push(element);
    this.allCorrespondentGroups.splice(event.option.value, 1);
    this.correspondentGroupsInput.nativeElement.value = '';
    this.correspondentGroupsForm.setValue(null);        
  }

  private _filter(value: any): string[] {    
    return this.allCorrespondentGroups.filter((item: any) => item.label.toLowerCase().indexOf(value) === 0);
  }    
}