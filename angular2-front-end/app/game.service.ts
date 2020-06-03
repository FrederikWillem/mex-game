import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class GameService {
  /**
   * Array of strings containing the playernames participating in the game.
   */
  public players: string[];

  /**
   * String contain the name of the player's turn
   */
  public playersTurn: string;

  /**
   * Integer with the value of the passed value by the previous player.
   */
  public passedValue: number;

  /**
   * Integer with the amounts of advice given by the previous player.
   */
  public advice: number;

  /**
   * Integer with the actual thrown score.
   */
  public rolledValue: number;

  /**
   * Boolean to monitor if the dices/score have to be revealed or not in the GameViewComponent.
   */
  public drawn: boolean;

  /**
   * Boolean to monitor if the player has already rolled, so the dices/score can be revealed to the player's turn.
   */
  public hasRolled: boolean;

  /**
   * Boolean to monitor if the player may roll again, when already rooled, so the roll agian button can be showed when needed.
   */
  public mayRollAgain: boolean;

  /**
   * Boolean to monitor if the player has to roll, so the passed value and advice inputs and button are shown or not.
   */
  public hasToRoll: boolean;

  constructor() {
    this.players = [];
    this.playersTurn = "";
    this.rolledValue = 21;
    this.drawn = true;
    this.hasRolled = false;
    this.mayRollAgain = true;
    this.hasToRoll = true;
  }

  /**
   * Updates the players in the game. 
   * Called when a player joins or exits the game, or disconnects from the server.
   * @param newPlayers Array containing the names of the players in the game
   * @param plyTurn string with the name of the player's turn
   */
  public updatePlayers(newPlayers: string[], plyTurn: number): void {
    this.players = newPlayers;
    this.playersTurn = this.players[plyTurn];
  }

  /**
   * Updates the state of the game. 
   * Called when the cup/dices are passed to the next player, or when the cup/dices are drawn.
   * @param plyTurn string with the name of the player's turn
   * @param pssdVal integer with the passed value by the previous player
   * @param advc integer with the amounts of advice given by the previous player
   * @param hastorll boolean if the player first has to roll, before passing the cup/dices to the next player
   */
  public updateState(plyTurn: number, pssdVal: number, advc: number, hastorll: boolean): void {
    this.playersTurn = this.players[plyTurn];
    this.passedValue = pssdVal;
    this.advice = advc;
    this.hasToRoll = hastorll;
  }

  /**
   * Sets the values when the players has rolled.
   * Called when the player rolls the dices.
   * @param value integer with the score that is rolled
   * @param again boolean if the player may roll again
   */
  public setThrow(value: number, again: boolean): void {
    this.rolledValue = value;
    this.mayRollAgain = again;
    this.hasRolled = true;
    this.hasToRoll = false;
  }

  /**
   * Sets the value when a player draws the cup.
   * Called when a player draws.
   * @param value integer with the score that was rolled
   * @param loser number of the index of the player that has lost the round.
   */
  public setDraw(value: number, loser: number): void {
    this.rolledValue = value;
    this.playersTurn = this.players[loser];
    this.drawn = true;
  }

  /**
   * Reset that the player has rolled.
   * Called when the player passes the cup.
   */
  public resetTurn(): void {
    this.hasRolled = false;
  }

  /**
   * Reverses back the resetTurn().
   * Called when a 'pass-fail' is returned by the back-end, after the player passed the cup.
   */
  public reverseResetTurn(): void {
    this.hasRolled = true;
  }

  /**
   * Covers the dices in the GameViewComponent.
   * Called when a player has (re)started the game after a 'draw' event.
   */
  public coverDices(): void {
    this.drawn = false;
  }
}