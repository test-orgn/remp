import { Component } from '@angular/core';

import { ArticlesComponent} from "../articles/articles.component";

@Component({
  selector: 'app-hud',
  templateUrl: './hud.component.html',
  styleUrls: ['./hud.component.css'],
})

export class HudComponent {
  hudEnabled: false;
}
