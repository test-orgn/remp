  import {Injectable} from '@angular/core';
  import {ArticlesElement} from './articleselement';
  import { HttpClient } from '@angular/common/http';
  import { ConfigService} from "../config.service";

  // import 'rxjs/add/operator/map';


  @Injectable()
  export class ArticlesService {
    elements: ArticlesElement[] = [];
    articles_ids: number[] = [];
    results: string[];

    constructor(private http: HttpClient, private config: ConfigService) { }

    getElements(): ArticlesElement[] {
      return this.elements;
    }

    getArticleIds() {
      return this.articles_ids;
    }

    pushElement(element: Element) {
      const reg = /https:\/\/dennikn.sk\/(\d+)/gi
      const res = reg.exec(element['href']);
      if (res !== null) {
        this.elements.push(new ArticlesElement(element, res[1]));
        // console.log(res[1]);
        this.articles_ids.push(Number(res[1]));
      }
    }

    getArticlesStats() {
      let ids = '';
      for (let entry of this.articles_ids) {
        ids = ids + '&ids=' + entry;
      }
      var d = new Date();
      d.setHours(d.getHours() - 1);
      this.http.get(this.config.beam + '/journal/commerce/purchase/sum?time_after=' + d.toISOString().split('.')[0]+"Z" + '&filter_by=articles' + ids + '&group=1').subscribe(data => this.setArticlesStatsh1(data));
      d.setHours(d.getHours() - 4);
      this.http.get(this.config.beam + '/journal/commerce/purchase/sum?time_after=' + d.toISOString().split('.')[0]+"Z" + '&filter_by=articles' + ids + '&group=1').subscribe(data => this.setArticlesStatsh4(data));
      d.setDate(d.getDate() - 1);
      this.http.get(this.config.beam + '/journal/commerce/purchase/sum?time_after=' + d.toISOString().split('.')[0]+"Z" + '&filter_by=articles' + ids + '&group=1').subscribe(data => this.setArticlesStatsh24(data));
    }

    setArticlesStatsh1(articles) {
      var element;
      for (element in this.elements) {
        if (typeof articles['sums'][this.elements[element].article_id] !== 'undefined') {
          this.elements[element].h1 = articles['sums'][this.elements[element].article_id];
        }
      }
    }

    setArticlesStatsh4(articles) {
      var element;
      for (element in this.elements) {
        if (typeof articles['sums'][this.elements[element].article_id] !== 'undefined') {
          this.elements[element].h4 = articles['sums'][this.elements[element].article_id];
        }
      }
    }

    setArticlesStatsh24(articles) {
      var element;
      for (element in this.elements) {
        if (typeof articles['sums'][this.elements[element].article_id] !== 'undefined') {
          this.elements[element].h24 = articles['sums'][this.elements[element].article_id];
        }
      }
    }
  }
