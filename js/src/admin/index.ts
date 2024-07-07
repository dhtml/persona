import app from 'flarum/admin/app';

app.initializers.add('dhtml/persona', () => {
  console.log('[dhtml/persona] Hello, admin!');
});
