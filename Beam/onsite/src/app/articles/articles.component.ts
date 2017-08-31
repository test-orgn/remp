import {Component } from '@angular/core';
// import { StatsComponent } from '../stats/stats.component';
import { ConfigService } from '../config.service';
import { ArticlesService } from './articles.service';
import { ArticlesElement } from './articleselement';

@Component({
  'selector': 'app-articles',
  'templateUrl': './articles.component.html',
  'styleUrls': ['./articles.component.css'],
  providers: [ ArticlesService, ConfigService ]
})

export class ArticlesComponent {
  pickedelements: ArticlesElement[];

  constructor(private config: ConfigService, public articlesService: ArticlesService) {
    const links = document.querySelectorAll(config.article_selector);
    for (let i = 0; i < links.length; ++i) {
      this.articlesService.pushElement(links[i]);
    }

    this.articlesService.getArticlesStats();
  }
}
