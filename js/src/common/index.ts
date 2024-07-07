import app from 'flarum/common/app';

app.initializers.add('dhtml/persona', () => {
  console.log('[dhtml/persona] Hello, forum and admin!');
});
