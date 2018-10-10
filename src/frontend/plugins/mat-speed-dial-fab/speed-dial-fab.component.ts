import { Component, EventEmitter, Input, Output, ElementRef } from '@angular/core';

import { speedDialFabAnimations } from './speed-dial-fab.animations';
declare function $j(selector: any): any;
export interface FabButton {
  icon: string,
  tooltip: string
}

export enum SpeedDialFabPosition {
  Top = 'top',
  Bottom = 'bottom',
  Left = 'left',
  Right = 'right'
}

@Component({
  selector: 'speed-dial-fab',
  templateUrl: './speed-dial-fab.component.html',
  styleUrls: ['./speed-dial-fab.component.scss'],
  animations: speedDialFabAnimations
})
export class SpeedDialFabComponent {

  @Input("reverse-column-direction") reverseColumnDirection: boolean = false;
  @Input("buttons") fabButtons: FabButton[];
  @Input("mainIcon") mainIcon: String;
  @Output('fabClick') fabClick = new EventEmitter();

  buttons: any = [];
  fabTogglerState = 'inactive';

  constructor(private elementRef: ElementRef) { }

  public showItems() {
      if($j('.speedDial').length == 0) {
        this.fabTogglerState = 'active';
        this.buttons = this.fabButtons;
      }
  }

  private hideItems() {
    this.fabTogglerState = 'inactive';
    this.buttons = [];
    this.elementRef.nativeElement.querySelector('#fab-container-buttons').removeEventListener('mouseleave', this.mouseLeave.bind(this));
  }

  public onToggleFab() {
    this.buttons.length ? this.hideItems() : this.showItems();
  }

  public bindLeaveEvent() {
    this.elementRef.nativeElement.querySelector('#fab-container-buttons').addEventListener('mouseleave', this.mouseLeave.bind(this));
  }


  mouseLeave(event: any) {
    this.hideItems();
  }

  public onClickFab(btn: { icon: string }) {
    this.hideItems();
    this.fabClick.emit(btn);
  }
}