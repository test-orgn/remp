export class ArticlesElement {
  top: number;
  left: number;
  height: number;
  width: number;
  element: Element;
  article_id: number;
  h1: 0;
  h4: 0;
  h24: 0;


  constructor(element: Element, article_id) {
    this.elementAttributes(element);
    this.element = element;
    this.article_id = article_id;
  }

  private elementAttributes(element: Element) {
    const rect = element.getBoundingClientRect();
    const pos = this.cumulativeOffset(element);
    this.top = pos.top - 4;
    this.left = pos.left - 4;
    this.height = Math.round(rect.bottom - rect.top) + 8;
    this.width = Math.round(rect.right - rect.left) + 8;
  }

  private cumulativeOffset(element: Element) {
    let top = 0, left = 0;
    do {
      top += (<HTMLElement>element).offsetTop  || 0;
      left += (<HTMLElement>element).offsetLeft || 0;
      element = (<HTMLElement>element).offsetParent;
    } while (element);

    return {
      top: top,
      left: left
    };
  }
}
