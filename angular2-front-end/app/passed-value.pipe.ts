import { Pipe, PipeTransform } from '@angular/core';
import {MEX } from './mex';

@Pipe({
  name: 'passedValue'
})
export class PassedValuePipe implements PipeTransform {
  /**
   * Transforms the passed value and advice into a string for display.
   * @param value the passed value as an integer
   * @param advice the amount of advice given as an integer
   */
  transform(value: number, advice: number): string {
    let return_str = "";
    if (value == 0) {
      return_str += "-";
    } else if (value == MEX || value > 600) {
      return_str += "Mex!!!";
    } else {
      return_str += value;
    }

    if (advice > 0) {
      return_str += " with "+advice+" x advice";
    }

    return return_str;
  }

}
