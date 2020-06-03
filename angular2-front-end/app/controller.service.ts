import { Injectable } from '@angular/core';
import { ApiService } from './api.service';
import { GameService } from './game.service';
import { LobbyService } from './lobby.service';
import { SetupService } from './setup.service';
import { MEX } from './mex';

@Injectable({
  providedIn: 'root'
})
export class ControllerService {

  constructor(private apiService:     ApiService, 
              private gameService:    GameService, 
              private lobbyService:   LobbyService, 
              private setupService:   SetupService) { }

  /**
   * Executes the wanted action, called by the back-end.
   * Called when a message is received from the websocket server.
   * @param action string containing the wanted action to execute
   * @param parameters Associated array containing the potentially needed parameters for the action
   */
  public executeAction(action: string, parameters: any): void {
    switch (action) {
      
      case "setup-user":
        if(parameters.userset) {
          this.lobbyService.setUsers(parameters.userlist);
          this.setupService.flipNameSet();
        }  else {
          window.alert("Username already taken! (or no name given...)");
        }
        this.setupService.flipCheckingName();
        break;

      case "players-update":
        this.gameService.updatePlayers(parameters.players, parameters.playersturn);
        break;

      case "state-update":
        this.gameService.updateState(parameters.playersturn, parameters.passedvalue, parameters.advice, parameters.hastoroll);
        break;

      case "user-connected":
        if(false) {
          console.log("New user!");
        }
        break;

      case "user-disconnected":
        this.lobbyService.removeUser(parameters.user);
        this.lobbyService.addServerMessage(parameters.user.name + " left!");
        break;

      case "add-user":
        this.lobbyService.addUser(parameters.user);
        this.lobbyService.addServerMessage(parameters.user.name + " joined the lobby!");
        break;

      case "new-message":
        this.lobbyService.addMessage(parameters.message);
        break;

      case "throw-response":
        this.gameService.setThrow(parameters.score, parameters.mayrollagain);
        break;

      case "draw-response":
        this.gameService.setDraw(parameters.score, parameters.loser);
        this.lobbyService.addServerMessage(this.gameService.players[parameters.loser] + " has lost!!!");
        break;

      case "cover-dices":
        this.gameService.coverDices();
        this.gameService.passedValue = 0;
        break;

      case "pass-fail":
        this.gameService.reverseResetTurn();
        window.alert("Passed value is not valid!");
        break;
    
      default:
        console.log("Unknown action recieved: " + action);
        break;
    }
  }

  /**
   * Sets up the username of the user/player.
   * Called when the 'Enter' button is clicked in the SetupComponent
   * @param myName string with the name inputted in the SetupComponent
   */
  public setup(myName: string): void {
    this.apiService.send("lobby","setup",{name: myName});
  }

  /**
   * Join the user to the game.
   * Called when the 'Join game' button is pressed in the GameViewComponent
   */
  public joinGame(): void {
    this.apiService.send("game", "join-game");
  }

  /**
   * Exits the user from the game.
   * Called when the 'Exit game' button is pressed in the GameViewComponent
   */
  public exitGame(): void {
    this.apiService.send("game", "exit-game");
  }

  /**
   * Rolls the dices for the first time after a 'draw' event.
   * Called when the 'roll' button is pressed in the GameViewComponent, first time after a 'draw' event.
   */
  public firstRollDices(): void {
    this.apiService.send("game","first-roll-dices");
  }

  /**
   * Rolls the dices.
   * Called when the 'roll' button is pressed in the GameViewComponent.
   */
  public rollDices(): void {
    this.apiService.send("game","roll-dices");
  }

  /**
   * Passes the cup to the next player, along with the given values.
   * Called when the 'pass' button is pressed in the GameViewComponent.
   * @param val integer with the inputted value from the #passbox element in the GameViewComponent.
   * @param ad integer with the inputted value from the #advice element in the GameViewComponent.
   */
  public passCup(val: number, ad: number): void {
    if( (this.gameService.passedValue < +val && 31 <= +val && this.gameService.passedValue != MEX) || +val == MEX ) {
      this.apiService.send("game","pass-cup", {value: +val, advice: +ad});
      this.gameService.resetTurn();
    } else {
      window.alert("Passed value is too low!!!");
    }
  }

  /**
   * Draws the cup.
   * Called when the 'draw' button is pressed in the GameViewComponent.
   */
  public drawCup(): void {
    this.apiService.send("game","draw");
  }

  /**
   * Send a message.
   * Called when the 'send' button is pressed in the LobbyViewComponent.
   * @param msg string containing the inputted text from the #message element in the LobbyViewComponent
   */
  public sendMessage(msg: string): void {
    this.apiService.send("lobby","submit-message", {message: msg});
  }
}
