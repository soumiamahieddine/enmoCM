import { Component, OnInit, Inject, forwardRef } from '@angular/core';
import { RequestDataService } from "../requestdata.service";

@Component({
  selector: 'app-versement',
  templateUrl: './versement.component.html',
  styleUrls: ['./versement.component.scss']
})

export class VersementComponent implements OnInit {
  requestBody: string;
  doctype: number = -1;
  public loaded: boolean = false; // Not updated anymore

  receiveDoctype($event) {
    console.log("receiveDoctype");
    console.log($event);
    this.doctype = $event;
  }
  // Build request and fetch MaarchCourrier API
  async register_courrier() {
/*
    var myHeaders = new Headers();
    myHeaders.append("Authorization", "Basic YmJsaWVyOm1hYXJjaA==");
    myHeaders.append("Content-Type", "application/json");
*/
    
    this.requestBody['doctype'] = this.doctype;
    let raw = JSON.stringify( this.requestBody );
    let requestOptions: RequestInit = {
      method: 'POST',
      //headers: myHeaders,
      //credentials: 'include',
      body: raw 
      //redirect: 'follow'
    };

    let url = "/courrier/register_courrier";
    let response = await fetch( url, requestOptions );
    let result = await response.json();
  }

  constructor(
    @Inject(forwardRef(() => RequestDataService)) private requestDataService: RequestDataService) {
  }

  async getRequestData(): Promise<void> {
    let requestData = await this.requestDataService.getRequestData();
    this.requestBody = requestData;
  }

  ngOnInit(): void {
    this.getRequestData();
  }
}

