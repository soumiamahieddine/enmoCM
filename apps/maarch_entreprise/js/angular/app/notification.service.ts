import {Md2Toast} from 'md2';
import {Injectable} from '@angular/core';

@Injectable()
export class NotificationService {
    constructor(private toast: Md2Toast) {
    }
    success(message:string) {
        this.toast.show('<i class="fa fa-info-circle fa-2x"></i><span>'+message+'</span>', 2000);
    }
         
    error(message:string) {
        this.toast.show('<i class="fa fa-exclamation-triangle fa-2x"></i><span>'+message+'</span>', 2000);
    }
}