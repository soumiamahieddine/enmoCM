import { Injectable } from "@angular/core";

@Injectable({
  providedIn: 'root'
})
export class DoctypesService {
  async getDoctypes(): Promise<any> {
    var authHeader = new Headers();
    //authHeader.append("Authorization", "Basic YmJsaWVyOm1hYXJjaA==");

    let requestOptions: RequestInit = {
      method: 'GET',
      headers: authHeader
    };
    let response = await fetch("/courrier/doctypes", requestOptions);
    let result = await response.json();
    //let res = JSON.parse(result);
    //console.log( result );
    return result;
  }

  constructor() {
  }
}
