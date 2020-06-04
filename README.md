# mex-game
 The code of a mex game application. The back-end consists of a websocket server in PHP code, and the front-end is build using Angular(2/TS). This project has a MVC structure.

## PHP Websocket server back-end
This websocket server is based on felladrin's: https://github.com/felladrin/php-websocket-server/tree/master/example/server

- MexServer extends the WebSocketServer of felladrin and defines what should be done OnConnect, OnDisconnect and OnMessage.
- WebSocketRequest is altered from felladrin's and handles all traffic between server and clients. It also, among other things, decodes the incomming requests and calls the requested actions of the controllers.
- startServer starts the server.
- the controllers contain the actions that can be called by the clients.
- the models contain the data and data operations.
- the helpers contain parent and other classes needed by the model classes.

## Angular2 front-end
This project was generated with [Angular CLI](https://github.com/angular/angular-cli) version 9.1.1. 

- the Websocket and Api Service are based on the Angular Websocket Tutorial of TutorialEdge.net: https://tutorialedge.net/typescript/angular/angular-websockets-tutorial/
- the Controller Service handles the actions requested from the server and the requests made to the server.
- the incoming request from the Api Service are linked to the Controller Service within the App Component.
- the Game Service handles the data and data operations of the game.
- the Lobby Service handles the data and data operations of the lobby.
- the LobbyView Component and GameView Component setup the display of the Lobby and Game respectively.
- the Setup Component is an overlay and only shows when the player's name is not yet given.
