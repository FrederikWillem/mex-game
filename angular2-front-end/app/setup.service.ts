import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class SetupService {
  /**
   * Boolean on which the SetupComponent is show or not.
   */
  public nameSet: boolean = false;

  /**
   * Boolean on which the waiting text is shown to the user, while corresponding with back-end to setup the user.
   */
  public checkingName: boolean = false;

  constructor() { }

  /**
   * Flip checkingName property, to flip from input and button to waiting text.
   */
  public flipCheckingName(): void {
    this.checkingName = !this.checkingName;
  }

  /**
   * Flip nameSet property, to (un)show the SetupComponent.
   */
  public flipNameSet(): void {
    this.nameSet = !this.nameSet;
  }
}
