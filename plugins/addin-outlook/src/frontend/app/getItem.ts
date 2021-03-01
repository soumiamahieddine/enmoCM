export interface Contact {
  name: string;
  mail_address: string;
  id: number;
}

export interface Item {
  subject: string;
  from: Contact;
  cc?: Contact[];
  created: Date;
  modified: Date;
  body: string;
}


