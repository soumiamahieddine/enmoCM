import { Injectable } from "@angular/core";
import { Contact, Item } from './getItem';

declare const Office: any;

@Injectable({
  providedIn: 'root'
})

export class MailDataService {

  readBodyAsync(): Promise<any> {
    return new Promise((resolve, reject) => {
      Office.context.mailbox.item.body.getAsync("text", { asyncContext: "This is passed to the callback" }, (asyncResult) => {
        if (asyncResult.status === Office.AsyncResultStatus.Failed) {
            reject(asyncResult.error);
        } else {
              resolve(asyncResult.value);
        }
      });
    });
  }

  async getMailData(): Promise<Item> {
    const item = Office.context.mailbox.item;
    let mailData: Item = {
      subject: item.subject,
      from: {
        name: item.from.displayName,
        mail_address: item.from.emailAddress,
        id: -1
      },
      cc: [],
      created: item.dateTimeCreated,
      modified: item.dateTimeModified,
      body: ""
    };
    // If there are cc(s) we push them in mailData cc array
    for (let i = 0; i < item.cc.length; i++) {
      const contact: Contact = {
        name: item.cc[i].displayName,
        mail_address: item.cc[i].emailAddress,
        id: -1
      };
      mailData.cc.push(contact);
    }
    let body = await this.readBodyAsync();
    mailData.body = body;
/*
    console.log("supported 1.8 ?");
    console.log( Office.context.requirements.isSetSupported('Mailbox', '1.8'));
    console.log("supported 1.3 ?");
    console.log( Office.context.requirements.isSetSupported('Mailbox', '1.3'));
    console.log("supported 1.1 ?");
    console.log( Office.context.requirements.isSetSupported('Mailbox', '1.1'));
*/
    let get_attach_res = await this.get_req_attach();
    console.log( "get_attach_res :" );
    console.log( get_attach_res );
    return mailData;
  }

  async get_req_attach(): Promise<any> {

    let message_id = this.getRestId( Office.context.mailbox.item.itemId );
    
    let requestOptions: RequestInit = {
      method: 'GET'
    };

    let url = "/ews/get_attachments?message_id=" + message_id;
    let response = await fetch( url, requestOptions );
    let result = await response.json();
    //let res = JSON.parse(result);
    return result;
  }
  getRestId( ewsId ) {
    return Office.context.mailbox.convertToRestId( ewsId, Office.MailboxEnums.RestVersion.v2_0 );
  }


  constructor() {
  }
}
