import { Injectable, Inject, forwardRef } from "@angular/core";
import { Contact, Item } from './getItem';
import { MailDataService } from "./maildata.service";
import { ContactService } from "./contact.service";


@Injectable({
  providedIn: 'root'
})
export class RequestDataService {
  async getMailData(): Promise<Item> {
    let mailData = await this.mailDataService.getMailData();
//    let attach = await this.mailDataService.getAttach();
//    console.log(attach);
    return mailData;
  }
  /**
   * Unicode to ASCII (encode data to Base64)
   */
  utoa(data : string): string {
    return btoa(unescape(encodeURIComponent(data)));
  }
  // Build request and fetch MaarchCourrier API
  fill_request_body(context) {
    var encoded_body = this.utoa(context.body);

    var raw = {
      "modelId":1, // *
      "status":"NEW",
      "encodedFile":encoded_body,
      "format":"txt",
      "confidentiality":false,
      "documentDate":"2020-01-01 17:18:47",
      "arrivalDate":"2020-01-01 17:18:47",//context.created.toString(),
      "processLimitDate":"2021-10-01", // --> need a date filler component
      "doctype":101, // *, 
      "diffusionList":[{"id":18,"mode":"dest","type":"user"}], // *
      "destination":11, // *
      "initiator":13, // *
      "externalId":{"companyId":"123456789"}, // *
      "subject":context.subject,
      "typist":19, // *
      "priority":"poiuytre1357nbvc", // *
      "senders":[{"type":"contact","id":context.contactId}], // *
      "recipients":[{"id":2,"type":"user"},{"id":17,"type":"entity"}] // *
    };
    return raw;
  }
  async getRequestData(): Promise<any> {
    let mailData = await this.getMailData();
    let contactId = await this.contactService.getContact(mailData);
    let agregatedData = {
      body: mailData.body,
      subject: mailData.subject,
      contactId: contactId
    }
    let requestData = this.fill_request_body(agregatedData);
    return requestData;
  }

  constructor(
    @Inject(forwardRef(() => ContactService)) private contactService: ContactService,
    @Inject(forwardRef(() => MailDataService)) private mailDataService: MailDataService) {
  }
}
