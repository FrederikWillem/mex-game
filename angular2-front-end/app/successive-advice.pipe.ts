import { Pipe, PipeTransform } from '@angular/core';
import { MEX } from './mex';

@Pipe({
  name: 'successiveAdvice'
})
export class SuccessiveAdvicePipe implements PipeTransform {
  /**
   * Transforms given amount of advice into the obvious follow up amount of advice.
   * @param value current given advice
   * @param passedValue current passed value
   */
  transform(value: number, passedValue: number): number {
    if (value == 0 || passedValue == MEX) {
      return 0;
    } else if (value > 0) {
      return value - 1;
    } else {
      return 0;
    }
  }

}
