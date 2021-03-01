import { Injectable } from "@angular/core";
import { Contact, Item } from './getItem';


@Injectable({
  providedIn: 'root'
})
export class ContactService {
  async getContact(mailData: Item): Promise<number> {
    let name = mailData.from.name;
    let name_split = name.split(' ');
    let firstname;
    let lastname;
    // Check there is a firstname and a lastname
    if ( name_split[0] && name_split[1] ) {
      let firstname = name_split[0];
      let lastname = name_split[1].toUpperCase();
    }

    let requestOptions: RequestInit = {
      method: 'GET',
    };
    let response = await fetch(
                   "/courrier/contacts?firstname=" + firstname + "&lastname=" + lastname,
                   requestOptions );
    let result = await response.text();

    let res = JSON.parse(result);
    let contactId = -1;
    if ( res['contact_id'] ) {
      contactId = res['contact_id'];
    }
    if (contactId === -1)
      console.log( "Contact not found and / or couldn't be created." );
    return contactId;
  }

  constructor() {
  }
}
