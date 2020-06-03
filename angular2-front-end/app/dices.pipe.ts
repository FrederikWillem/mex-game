import { Pipe, PipeTransform } from '@angular/core';
import { MEX } from './mex';

@Pipe({
  name: 'dices'
})
export class DicesPipe implements PipeTransform {
  /**
   * Transforms the score into a HTML string of the corresponding 'dice'-images.
   * @param value the score as an integer
   */
  transform(value: number): string {
    const base: string = 'assets/dices/';
    const begin_tag: string = '<img style="width: 100px; height: 100px; border-radius: 5px; margin: 45px 10px 0px 10px;" src="'+base;
    const end_tag: string = '.JPG" />';

    if(value > 600 || value == MEX) {
      return begin_tag+'2'+end_tag + begin_tag+'1'+end_tag;
    } else if (value <= 600 && value >= 100) {
      let hundredfold = value.toString().charAt(0);
      return begin_tag+hundredfold+end_tag + begin_tag+hundredfold+end_tag;
    } else {
      let val_str: string = value.toString();
      let onefold = val_str.charAt(1);
      let tenfold = val_str.charAt(0);
      return begin_tag+tenfold+end_tag + begin_tag+onefold+end_tag;
    }
  }
}
