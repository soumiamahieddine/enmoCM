import { Injectable } from '@angular/core';

@Injectable()
export class HeaderService {
    headerMessage   : string = ""; 
    subHeaderMessage   : string = "";


    setHeader(maintTitle: string, subTitle: any = '') {
        this.headerMessage = maintTitle;
        this.subHeaderMessage = subTitle;
    }
}
