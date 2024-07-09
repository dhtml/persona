import app from 'flarum/forum/app';
import addPersonaMenu from "./component/addPersonaMenu";

app.initializers.add('dhtml/persona', () => {
  //console.log('[dhtml/persona] Hello, forum!');

  addPersonaMenu();

});
