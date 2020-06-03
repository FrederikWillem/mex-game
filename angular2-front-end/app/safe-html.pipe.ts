import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';

@Pipe({
  name: 'safeHtml'
})
export class SafeHtmlPipe implements PipeTransform {

  constructor(private sanitized: DomSanitizer) {}
  
  /**
   * Transforms a HTML string into HTML, and checks if it contains real HTML.
   * @param value a HTML string
   */
  transform(value) {
    return this.sanitized.bypassSecurityTrustHtml(value);
  }

}
