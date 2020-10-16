import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-connections-administration',
  templateUrl: './connections-administration.component.html',
  styleUrls: ['./connections-administration.component.scss']
})
export class ConnectionsAdministrationComponent implements OnInit {

  loading: boolean = true;

  constructor() { }

  ngOnInit(): void {
  }

}
