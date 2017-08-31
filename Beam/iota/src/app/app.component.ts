import { Component } from '@angular/core';
import { HudComponent } from './hud/hud.component';

@Component({
  selector: 'app-on-site',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
  providers: [ HudComponent ]
})

export class AppComponent {
  title = 'Exus On-site';
  selectedValue: string;
  enableHud: false;
  // art: NodeListOf<Element>;

  foods = [
    {value: 'steak-0', viewValue: 'Steak'},
    {value: 'pizza-1', viewValue: 'Pizza'},
    {value: 'tacos-2', viewValue: 'Tacos'}
  ];

  toggleOnSite(): void {
    console.log('aaa');
    let art = document.querySelectorAll('article>h3>a');
    console.log(art);
    for (var i = 0, len = art.length; i < len; i++) {
      console.log(art[i]['left']);
    }
    // console.log(this.art);
  }
}
