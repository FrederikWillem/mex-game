import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppComponent } from './app.component';
import { ApiService } from './api.service';
import { WebsocketService } from './websocket.service';
import { GameViewComponent } from './game-view/game-view.component';
import { DicesPipe } from './dices.pipe';
import { SafeHtmlPipe } from './safe-html.pipe';
import { SetupComponent } from './setup/setup.component';
import { PassedValuePipe } from './passed-value.pipe';
import { LobbyViewComponent } from './lobby-view/lobby-view.component';
import { SuccessiveAdvicePipe } from './successive-advice.pipe';
import { SuccessivePassedValuePipe } from './successive-passed-value.pipe';

@NgModule({
  declarations: [
    AppComponent,
    GameViewComponent,
    DicesPipe,
    SafeHtmlPipe,
    SetupComponent,
    PassedValuePipe,
    LobbyViewComponent,
    SuccessiveAdvicePipe,
    SuccessivePassedValuePipe
  ],
  imports: [
    BrowserModule
  ],
  providers: [
    WebsocketService,
    ApiService
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
