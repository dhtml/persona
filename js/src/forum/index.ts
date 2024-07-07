import app from 'flarum/forum/app';

app.initializers.add('dhtml/persona', () => {
  console.log('[dhtml/persona] Hello, forum!');
});
