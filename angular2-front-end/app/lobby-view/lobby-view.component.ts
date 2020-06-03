import { Component, OnInit, Input, ViewChild, ElementRef, AfterViewChecked } from '@angular/core';
import { ControllerService } from '../controller.service';
import { LobbyService } from '../lobby.service';

@Component({
  selector: 'app-lobby-view',
  templateUrl: './lobby-view.component.html',
  styleUrls: ['./lobby-view.component.css']
})
export class LobbyViewComponent implements OnInit, AfterViewChecked {
  /**
   * The name of the player, set by the setup component.
   */
  @Input() myName: string;

  @ViewChild('messagebox') private messageBoxContainer: ElementRef;

  constructor(public lobbyService: LobbyService, public controller: ControllerService) { }

  ngOnInit(): void {
  }

  /**
   * Calls the scroll to bottom function called after the component is updated/a new message is added to lobbyService.messages.
   */
  ngAfterViewChecked(){
    this.scrollMessageBoxToBottom();
  }

  /**
   * Scrolls the messagebox to the bottom, so the last message is visible.
   */
  scrollMessageBoxToBottom(): void {
    try {
      this.messageBoxContainer.nativeElement.scrollTop = this.messageBoxContainer.nativeElement.scrollHeight;
    } catch(err) {}
  }
}
