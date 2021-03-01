import { Component, OnInit, Inject, forwardRef } from '@angular/core';
import { Output, EventEmitter } from '@angular/core';
import { Doctype } from '../doctype';
import { DoctypesService } from "../doctype.service";

interface Event {
  name: string;
  value: any;
}

@Component({
  selector: 'app-dropdown',
  templateUrl: './dropdown.component.html',
  styleUrls: ['./dropdown.component.scss']
})

export class DropdownComponent implements OnInit {
  doctypes: Doctype[] = [];
  selectedDoctypeId = -1;
  events: Event[] = [];

  @Output() doctypeEvent = new EventEmitter<string>();

  // -> onChange
  sendDoctype($event) {
    this.events.push( { name: '(change)', value: $event } );
    // -> parent component will receive the selected value
    this.doctypeEvent.emit( $event );
  }

  async getDoctypes(): Promise<void> {
    let doctypesJson = await this.doctypesService.getDoctypes();
    let doctypesArray = doctypesJson.doctypes;
    let sectionId = 0;
    for (let doc of doctypesArray) {
      /*
      ** Push a display only element with id_type = -1 when type id first
      ** number is incremented ( 102 -> 201 means it is a new section ).
      */
      let calcSectionId = Math.floor(doc.type_id / 100);
      if ( calcSectionId != sectionId ) {
        sectionId = calcSectionId;
        this.doctypes = [...this.doctypes, {
          type_id: -1,
          description: "------------------------"
        }];
      }
      /* End of separator push in doctypes list. */
      this.doctypes = [...this.doctypes, {
        type_id: doc.type_id,
        description: doc.description
      }];
      /*
      ** Can't do it with "this.doctypes.push({...})" because,
      ** as seen here : https://github.com/ng-select/ng-select/issues/935
      ** and here : https://github.com/ng-select/ng-select#change-detection
      ** ng-select won't detect mutation.
      */
    }
    console.log( this.doctypes );
  }

  constructor(
    @Inject(forwardRef(() => DoctypesService)) private doctypesService: DoctypesService) {
  }

  ngOnInit(): void {
    this.getDoctypes();
  }
}

