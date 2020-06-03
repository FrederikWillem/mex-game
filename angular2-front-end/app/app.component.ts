import { Component } from '@angular/core';
import { ApiService } from './api.service';
import { ControllerService } from './controller.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  /**
   * The name of the player, given by the SetupComponent.
   */
  myName: string;

  /**
   * Links the input of the api websocket to the executeAction method of the controller(service).
   */
  constructor(private apiService: ApiService, public controller: ControllerService){
    apiService.api.subscribe(reply => {
      controller.executeAction(reply.action, reply.parameters);
    });
  }
  /**
   * Sets the name of the player, which is inputted into the GameView- and LobbyViewComponent
   * @param event given by the EventEmitter of the SetupComponent
   */
  setMyName(event){
    this.myName = event;
  }
}
