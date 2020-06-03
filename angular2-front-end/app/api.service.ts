import { Injectable } from '@angular/core';
import { Subject } from "rxjs";
import { WebsocketService } from "./websocket.service";
import { map, tap } from 'rxjs/operators';
import { Reply } from './reply'
import { Call } from './call';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  /**
   * Property to store the websocket subject into.
   * Responses are in Reply interfaces.
   */
  public api: Subject<Reply>;

  /**
   * Property to turn on/off debug mode.
   */
  private debugMode: boolean = true;

  /**
   * Connectiong with websocket is made
   * and api property loaded with websocket-subject.
   * Responses are mapped to a Reply interface
   * and, when in debug mode, response is consolelogged.
   * @param wsService WebSocketService which creates connetion with websocket.
   */
  constructor(wsService: WebsocketService) {
    this.api = <Subject<Reply>>wsService
      .connect("ws://127.0.0.1:3000")
      .pipe(
        map((response: MessageEvent): Reply => {
          let data = JSON.parse(response.data);
          return {
            action: data.action,
            parameters: data.parameters
          };
        }),
        tap( reply => this.debug("Recieved reply: " + JSON.stringify(reply)))
      );
  }

  /**
   * Contructs the request into proper call
   * and send it over websocket to back-end.
   * @param contr back-end controller to address as string
   * @param act back-end action of controller to execute as string
   * @param param array of parameters to send allong
   */
  public send(contr: string, act: string, param: any = null) {
    let call: Call = {
      controller: contr,
      action: act,
      parameters: param
    }
    this.api.next(call);
    this.debug("Call to websocket: " + JSON.stringify(call));
  }

  /**
   * Console logs debug messages when in debug mode.
   * @param message string with debug message
   */
  public debug(message: string) {
    if(this.debugMode) {
      console.log(message);
    }
  }
}
