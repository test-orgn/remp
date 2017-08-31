import { Injectable } from '@angular/core';

@Injectable()
export class ConfigService {
  enabled = true;
  article_selector = 'article>h3>a';
  beam = 'http://localhost:8092';
}
