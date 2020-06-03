import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { ControllerService } from '../controller.service';
import { SetupService } from '../setup.service';

@Component({
  selector: 'app-setup',
  templateUrl: './setup.component.html',
  styleUrls: ['./setup.component.css']
})
export class SetupComponent implements OnInit {
  /**
   * EventEmitter to set the of the player amoung all the other components.
   */
  @Output() setupMyName = new EventEmitter();

  constructor(public controller: ControllerService, public setupService: SetupService) { }

  ngOnInit(): void {
  }

  /**
   * Emits the name to the other components,
   * send the name to back-end via controller
   * and flips the checkingName property to show waiting text untill response of back-end.
   * @param name name given by input
   */
  setupName(name: string): void {
    this.setupMyName.emit(name);
    this.controller.setup(name);
    this.setupService.flipCheckingName();
  }
}
