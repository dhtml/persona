import app from 'flarum/admin/app';

app.initializers.add('dhtml/persona', () => {
  //console.log('[dhtml/translate] Hello, admin!');


  app.extensionData.for('dhtml-persona')
    .registerSetting({
      setting: 'dhtml-persona.pattern1',
      label: app.translator.trans('dhtml-persona.admin.settings.pattern1'),
      type: 'text',
      required: true,
      help: app.translator.trans('dhtml-persona.admin.settings.plugin.help'),
      default: '.site1.com',
    })
    .registerSetting({
      setting: 'dhtml-persona.pattern2',
      label: app.translator.trans('dhtml-persona.admin.settings.pattern2'),
      type: 'text',
      required: true,
      help: app.translator.trans('dhtml-persona.admin.settings.plugin.help'),
      default: '.site2.com',
    })
    .registerSetting({
      setting: 'dhtml-persona.pattern3',
      label: app.translator.trans('dhtml-persona.admin.settings.pattern3'),
      type: 'text',
      required: true,
      help: app.translator.trans('dhtml-persona.admin.settings.plugin.help'),
      default: '.site3.com',
    });


});
