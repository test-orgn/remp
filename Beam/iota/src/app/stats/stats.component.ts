import { Component, Input } from '@angular/core';
import { ArticlesElement } from '../articles/articleselement';


@Component({
  'selector': 'stats',
  'templateUrl': './stats.component.html',
  'styleUrls': ['./stats.component.css']
})

export class StatsComponent {
  @Input()
  picked: ArticlesElement;

  setStylesBox() {
    const styles = {
      // CSS property names
      'top':  this.picked.top + 'px',
      'left': this.picked.left + 'px',
      'height': this.picked.height + 'px',
      'width': this.picked.width + 'px'
    };
    return styles;
  }

  setStylesStats() {
    const styles = {
      // CSS property names
      'top':  this.picked.top + 'px',
      'left': this.picked.left - 40 + 'px',
      'height': '65px',
      // 'height': this.picked.height + 'px',
      'width': 40 + 'px'
    };
    return styles;
  }

}
