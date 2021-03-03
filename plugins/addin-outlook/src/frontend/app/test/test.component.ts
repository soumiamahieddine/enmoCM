import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { of } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';

@Component({
    selector: 'app-test',
    templateUrl: './test.component.html',
    styleUrls: ['./test.component.scss']
})
export class TestComponent implements OnInit {

    constructor(
        public http: HttpClient,
    ) { }

    ngOnInit(): void {
        const headers = new HttpHeaders({
            'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE2MTQ3ODkxNDEsInVzZXIiOnsiaWQiOjIxLCJmaXJzdG5hbWUiOiJCZXJuYXJkIiwibGFzdG5hbWUiOiJCTElFUiIsInN0YXR1cyI6Ik9LIiwibG9naW4iOiJiYmxpZXIifX0.0OSUOkkgp948Ehs0qKeaaukJf8VVuAc_TChHQ1oS_tM'
        });

        this.http.get('../rest/currentUser/profile', { headers: headers }).pipe(
            tap((data: any) => {
                console.log(data);
            }),
            catchError((err: any) => {
                console.log(err);

                return of(false);
            })
        ).subscribe();
    }
}
