import { Pipe, PipeTransform } from '@angular/core';
import { MEX } from './mex';

@Pipe({
  name: 'successivePassedValue'
})
export class SuccessivePassedValuePipe implements PipeTransform {
  /**
   * Transforms given passed value into the obvious follow up passed value.
   * @param value current passed value
   */
  transform(value: number): number {
    if (value == MEX || value >= 600) {
      return MEX;
    } else {
      let str_val = value.toString();
      if (value >= 65) {
        if (+str_val.charAt(0) >= 6) {
          return 100;
        } else {
          return Math.floor((value + 100)/100) * 100;
        }
      } else if(value >= 31) {
        if (+str_val.charAt(1) < +str_val.charAt(0) - 1) {
          return value + 1;
        } else {
          return (+str_val.charAt(0)+1) * 10 + 1;
        }
      } else {
        return 31;
      }
    }
    return null;
  }

}
