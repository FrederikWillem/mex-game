import { Component, OnInit, Input } from '@angular/core';
import { GameService } from '../game.service';
import { ControllerService } from '../controller.service';

@Component({
  selector: 'app-game-view',
  templateUrl: './game-view.component.html',
  styleUrls: ['./game-view.component.css']
})
export class GameViewComponent implements OnInit {
  /**
   * The name of the player, set by the setup component.
   */
  @Input() myName: string;

  /**
   * Math proterty for math operation in if-statement of the playerboxes.
   */
  public math: Math = Math;

  constructor(public gameService: GameService, public controller: ControllerService) { }

  ngOnInit(): void {
  }

}
