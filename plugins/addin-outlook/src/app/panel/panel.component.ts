import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';
import { NotificationService } from '../service/notification/notification.service';

declare const Office: any;
@Component({
    selector: 'app-test',
    templateUrl: './test.component.html',
    styleUrls: ['./test.component.scss']
})
export class PanelComponent implements OnInit {
    loading: boolean = true;

    // must be REST USER (with create_contact privilege)
    headers = new HttpHeaders({
        Authorization: 'Basic ' + btoa('cchaplin:maarch')
    });

    inApp: boolean = false;
    applicationName: string = 'Maarch Courrier';

    displayMailInfo: any = {};
    docFromMail: any = {};
    contactInfos: any = {};
    userInfos: any;
    mailBody: any;
    contactId: number;

    constructor(
        public http: HttpClient,
        private notificationService: NotificationService
    ) { }

    async ngOnInit(): Promise<void> {
        this.http.get('../rest/authenticationInformations')
        .pipe(
            tap((data: any) => {
                this.applicationName = data.applicationName;
            }),
            catchError((err: any) => {
                console.log(err);
                return of(false);
            })
        ).subscribe();

        console.log(Office.context);

        this.inApp = await this.checkMailInApp();

        if (!this.inApp) {
            // console.log(Office.context.mailbox.item);
            // this.getToken();
            this.initMailInfo();
        }
        this.loading = false;
    }

    async sendToMaarch() {
        this.loading = true;
        await this.getMailBody();
        await this.createContact();
        this.createDocFromMail();
        // this.getAttachments();
    }

    checkMailInApp(): Promise<boolean> {
        return new Promise((resolve) => {
            resolve(false);
            // TO DO route check resource
        });
    }

    initMailInfo() {
        this.displayMailInfo = {
            modelId: 1,
            doctype: 'Courriel',
            subject: Office.context.mailbox.item.subject,
            typist: Office.context.mailbox.item.to.displayName,
            status: 'NEW',
            documentDate: Office.context.mailbox.item.dateTimeCreated,
            arrivalDate: Office.context.mailbox.item.dateTimeCreated,
            emailId: Office.context.mailbox.item.itemId,
            sender: Office.context.mailbox.item.from.displayName
        };
    }

    createDocFromMail() {
        // TO DO get id user
        this.docFromMail = {
            modelId: 1,
            doctype: 102,
            subject: Office.context.mailbox.item.subject,
            chrono: true,
            // typist : 10,
            status: 'NEW',
            documentDate: Office.context.mailbox.item.dateTimeCreated,
            arrivalDate: Office.context.mailbox.item.dateTimeCreated,
            format: 'TXT',
            encodedFile: btoa(unescape(encodeURIComponent(this.mailBody))),
            externalId: { emailId: Office.context.mailbox.item.itemId },
            senders: [{ id: this.contactId, type: 'contact' }]
        };
        return new Promise((resolve) => {
            return new Promise((resolve) => {
                this.http.post('../rest/resources', this.docFromMail, { headers: this.headers }).pipe(
                    tap((data: any) => {
                        // console.log(data);
                        this.notificationService.success('Courriel envoyé');
                        this.inApp = true;
                        resolve(true);
                    }),
                    finalize(() => this.loading = false),
                    catchError((err: any) => {
                        console.log(err);
                        return of(false);
                    })
                ).subscribe();
            });
        });
    }

    getMailBody() {
        return new Promise((resolve) => {
            Office.context.mailbox.item.body.getAsync(Office.CoercionType.Text, ((res: { value: any; }) => {
                this.mailBody = res.value;
                resolve(true);
            }));
        });
        
    }

    createContact() {
        const userName: string = Office.context.mailbox.item.from.displayName;
        const index = userName.lastIndexOf(' ');
        this.contactInfos = {
            firstname: userName.substring(0, index),
            lastname: userName.substring(index + 1),
            email: Office.context.mailbox.item.from.emailAddress,
        };
        return new Promise((resolve) => {
            this.http.post('../rest/contacts', this.contactInfos, { headers: this.headers }).pipe(
                tap((data: any) => {
                    // console.log(data.id);
                    this.contactId = data.id;
                    resolve(true);
                }),
                catchError((err: any) => {
                    console.log(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    /*getToken() {
        Office.context.mailbox.getCallbackTokenAsync(this.attachmentTokenCallback);
    }

    attachmentTokenCallback(asyncResult: any) {
        let serviceRequest : any = {
            attachmentToken: '',
            ewsUrl : Office.context.mailbox.ewsUrl,
            restUrl: Office.context.mailbox.restUrl,
            attachments: []
        };
        if (asyncResult.status == "succeeded") {
            serviceRequest.attachmentToken = asyncResult.value;
            for (var i = 0; i < Office.context.mailbox.item.attachments.length; i++) {
                serviceRequest.attachments.push(Office.context.mailbox.item.attachments[i])
            }
            console.log(serviceRequest);
        }
        else {
            console.log(asyncResult.error.message);
        }
    }*/

    getAttachments() {
        if (Office.context.requirements.isSetSupported('Mailbox', '1.8')) {
            if (Office.context.mailbox.item.attachments.length > 0) {
                for (let i = 0 ; i < Office.context.mailbox.item.attachments.length ; i++) {
                    Office.context.mailbox.item.getAttachmentContentAsync(Office.context.mailbox.item.attachments[i].id, this.handleAttachmentsCallback);
                }
            }
          } else {
            console.log('Impossible de récupérer les pj : version minimum Office server 1.8');
          }
    }
    
    handleAttachmentsCallback(result) {
        console.log(result);
        
        // Parse string to be a url, an .eml file, a base64-encoded string, or an .icalendar file.
        /*switch (result.value.format) {
            case Office.MailboxEnums.AttachmentContentFormat.Base64:
                console.log(result);
                // Handle file attachment.
                break;
            case Office.MailboxEnums.AttachmentContentFormat.Eml:
                // Handle email item attachment.
                break;
            case Office.MailboxEnums.AttachmentContentFormat.ICalendar:
                // Handle .icalender attachment.
                break;
            case Office.MailboxEnums.AttachmentContentFormat.Url:
                // Handle cloud attachment.
                break;
            default:
                // Handle attachment formats that are not supported.
        }*/
    }
}
