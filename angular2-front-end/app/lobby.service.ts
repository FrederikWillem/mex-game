import { Injectable } from '@angular/core';
import { Message } from './message';
import { Observable, of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class LobbyService {
  /**
   * Property to store all the users in the lobby into.
   * (no interface given, so this can be determined by back-end. Only has to contain "name".)
   */
  public users;

  /**
   * Property to store all the messages into.
   */
  public messages: Message[];

  constructor() {
    this.users = [];
    this.messages = [];
   }

   /**
    * Sets users with given user list from back-end (when setting up).
    * @param userList user list send by back-end
    */
  setUsers(userList): void {
    this.users = userList;
  }

  /**
   * Adds (new connecting) user to users.
   * @param user user send by back-end
   */
  addUser(user): void {
    this.users.push(user);
  }

  /**
   * Removes (disconnecting) user from users.
   * @param user user send by back-end
   */
  removeUser(user): void {
    let index: number = this.messages.indexOf(user);
    this.messages.splice(index, 1);
  }

  /**
   * Adds (newly send) message to messages.
   * @param msg message send by back-end
   */
  addMessage(msg: Message): void {
    if(this.messages.length >= 25){
      this.messages.shift();
    }
    this.messages.push(msg);
  }

  /**
   * Adds a message with server response info to messages.
   * @param txt update text
   */
  addServerMessage(txt: string): void {
    let msg: Message = {text: txt, author:"server", datetime:""};
    this.addMessage(msg);
  }
}
