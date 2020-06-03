/**
 * This WebsocketService is taken from the tutorial given on tutorialedge.net
 * Source: https://tutorialedge.net/typescript/angular/angular-websockets-tutorial/
 */
import { Injectable } from '@angular/core';
import { Subject, Observable, Observer } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class WebsocketService {
  /**
   * Subject property to hold the Observer(sender) and Observable(reciever) of the websocket.
   */
  private subject: Subject<MessageEvent>;

  constructor() { }

  /**
   * Returns the binded subject to the websocket. If to created yet, it first creates a connection.
   * @param url URL of the websocket it has to listen and send to.
   */
  public connect(url): Subject<MessageEvent> {
    if (!this.subject) {
      this.subject = this.create(url);
      console.log("Successfully connected: " + url);
    }
    return this.subject;
  }

  /**
   * Creates a new WebSocket,
   * binds the responses of the websocket to an observable,
   * binds the requests of the websocket to an observer
   * and creates and returns a subject constructed out of the two.
   * @param url URL of the websocket
   */
  private create(url): Subject<MessageEvent> {
    let ws = new WebSocket(url);

    let observable = Observable.create((obs: Observer<MessageEvent>) => {
      ws.onmessage = obs.next.bind(obs);
      ws.onerror = obs.error.bind(obs);
      ws.onclose = obs.complete.bind(obs);
      return ws.close.bind(ws);
    });

    let observer = {
      next: (data: Object) => {
        if (ws.readyState === WebSocket.OPEN) {
          ws.send(JSON.stringify(data));
        }
      }
    };

    return Subject.create(observer, observable);
  }
}