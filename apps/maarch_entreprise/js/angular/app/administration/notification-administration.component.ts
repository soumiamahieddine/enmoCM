import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { ActivatedRoute, Router } from '@angular/router';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare var angularGlobals : any;

@Component({
    templateUrl : angularGlobals["notification-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class NotificationAdministrationComponent implements OnInit {

    coreUrl             : string;
    notificationId              : string;
    creationMode                : boolean;
    notification                : any         = {
                                                    diffusionType_label  : null
                                                };
    loading                     : boolean   = false;
    lang                        : any       = LANG;


    constructor(public http: HttpClient, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void {
        this.prepareNotifications();
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.route.params.subscribe(params => {
            console.log(params['identifier']);
            if(typeof params['identifier']== "undefined"){
            //if(params['identifier']== "new"){
                this.creationMode = true;
                this.http.get(this.coreUrl + 'rest/administration/notifications/new')
                    .subscribe((data : any) => {
                        this.notification = data.notification;
                                    
                        this.loading = false;
                        // setTimeout(() => {
                        //             $j("select").chosen({width:"100%",disable_search_threshold: 10, search_contains: true});     
                        //         }, 0);
                    }, (err) => {
                        errorNotification(JSON.parse(err._body).errors);
                    });
            }else {
                this.creationMode = false;
                this.http.get(this.coreUrl + 'rest/administration/notifications/' + params['identifier'])
                .subscribe((data : any) => {
            
                    this.notification = data.notification;
                    console.log(data.notification);
                    this.loading = false;
                    setTimeout(() => {
                        $j("select").chosen({width:"100%",disable_search_threshold: 10, search_contains: true});       
                    }, 0);
                });

            } 
        });
    }

    selectAll(event:any){
        var target = event.target.getAttribute("data-target");
        $j('#' + target + ' option').prop('selected', true);
        $j('#' + target).trigger('chosen:updated');
       
    }

    unselectAll(event:any){
        var target = event.target.getAttribute("data-target");
        $j('#' + target + ' option').prop('selected', false);
        $j('#' + target).trigger('chosen:updated');
    }

    onSubmit() {
        //affect value of select
        if($j("#groupslist").chosen().val()){
            this.notification.diffusion_properties = $j("#groupslist").chosen().val();
        }else if($j("#entitieslist").chosen().val()){
            this.notification.diffusion_properties = $j("#entitieslist").chosen().val();
        }else if($j("#statuseslist").chosen().val()){
            this.notification.diffusion_properties = $j("#statuseslist").chosen().val();
        }else if($j("#userslist").chosen().val()){
            this.notification.diffusion_properties = $j("#userslist").chosen().val();
        }

        if($j("#groupslistJd").chosen().val()){
            this.notification.attachfor_properties = $j("#groupslistJd").chosen().val();
        }else if($j("#entitieslistJd").chosen().val()){
            this.notification.attachfor_properties = $j("#entitieslistJd").chosen().val();
        }else if($j("#statuseslistJd").chosen().val()){
            this.notification.attachfor_properties = $j("#statuseslistJd").chosen().val();
        }else if($j("#userslistJd").chosen().val()){
            this.notification.attachfor_properties = $j("#userslistJd").chosen().val();
        }
        this.http.post(this.coreUrl + 'rest/notifications', this.notification)
        .subscribe((data : any) => {
            this.router.navigate(['/administration/notifications']);
            //successNotification(data.success);
            successNotification(this.lang.newNotificationAdded + ' : ' + this.notification.notification_id);

        },(err) => {
            errorNotification(JSON.parse(err._body).errors);
        });
    }
    

    prepareNotifications() {
        $j('#inner_content').remove();
    }

    updateBreadcrumb(applicationName: string){
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/notifications\"' style='cursor: pointer'>notifications</a>";
        }
    }


}
