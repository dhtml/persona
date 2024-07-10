import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import HeaderSecondary from 'flarum/common/components/HeaderSecondary';
import SelectDropdown from 'flarum/common/components/SelectDropdown';


export const loginUser = (username) => {
  if(username=='Add New User') {
    return addNewUser();
  }

  return app.request({
    method: 'POST',
    url: `${app.forum.attribute('baseUrl')}/api/persona-login`,
    body: {
      username: username
    }
  }).then(response => {
    window.location.reload();
  }).catch(error => {
    console.error('Login failed', error);
    alert("Login failed");
    // Handle login error, e.g., show an error message to the user
  });
}

export const addNewUser = () => {
  let username = prompt("Enter username","");
  username = username.trim();

  if(username=="") {return;}

  return app.request({
    method: 'POST',
    url: `${app.forum.attribute('baseUrl')}/api/persona-register`,
    body: {
      username: username
    }
  }).then(response => {
    window.location.reload();
  }).catch(error => {
    console.log(error);
    console.error('Register failed');
  });
}

export const doesEmailMatchPatterns = (email, patterns) => {
  return Object.values(patterns).some(pattern => pattern && email.includes(pattern));
};


export default function () {

  extend(HeaderSecondary.prototype, 'items', function (items) {


    const user = app.session.user;

    let isModerator = false;
    let email = '';

    if (user) {
      email = user.email();
      isModerator = user.groups().some(group => {
        const groupId = group.data.id;
        return groupId === '1' || groupId === '4';
      });
    } else {
      return; //no point
    }

    const _username = user.username();

    const patterns = {
      pattern1: app.forum.attribute('domainpattern1'),
      pattern2: app.forum.attribute('domainpattern2'),
      pattern3: app.forum.attribute('domainpattern3')
    };

    const userData = {email, patterns};

    const isFilteredUser = doesEmailMatchPatterns(email,patterns);


    if(!isModerator && !isFilteredUser) {return;}

    const decodedArray = JSON.parse(app.forum.attribute('dhtmlPersonaUsers'));
    decodedArray.unshift("Add New User");


    const users = [];


    for (let i = 0; i < decodedArray.length; i++) {
      const user = decodedArray[i];

      if(_username == user) continue;

      users.push(
        <Button icon={'fas fa-user'} onclick={() => {loginUser(user);}}
        >
          {user}
        </Button>
      );
    }

    const label = app.translator.trans('dhtml-persona.forum.persona')

    items.add(
      'persona',
      <SelectDropdown
        buttonClassName="Button Button--link"
        accessibleToggleLabel={label}
      >
        {users}
      </SelectDropdown>,
      21
    );

  });

}
