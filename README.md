# dokuwiki-plugin-foundryvttstatus
Plugin for dokuwiki that shows the status of a foundryvtt instance

NOTES: WORK IN PROGRESS
- install is now manual only
- foundryvtt url is hard-coded
- secred api key is hard-coded


Usage: 

```
<foundryvttstatus route="vttdinsdag" port="30000">   
```

if the user belongs to group $route, the status is checked of the foundryvtt instance on http://foundryvtt.lan:$port/$route using a custom added API that:
  * returns the status and active world of the instance;
  * adds the user as a foundryvtt user;
  * creates/updates the password from the foundryvtt user (a random password with 24 letters or digits);
  * if the user belongs to group gm$route it will receive  assistant GM permissions and player permissions otherwise;
  * returns a password that can be used to login this user in this foundryvtt instance.
If the user belongs to group $route a link to the instance is presented and a hidden <div> will contain the password for use by the foundryvtt autologin javascript.

 

All documentation for this plugin can be found at
http://www.dokuwiki.org/plugin:foundryvttstatus (URL INCORRECT)

If you install this plugin manually, make sure it is installed in
lib/plugins/foundryvttstatus/ - if the folder is called different it
will not work!

Please refer to http://www.dokuwiki.org/plugins for additional info
on how to install plugins in DokuWiki.

----
Copyright (C) Martijn Sanders

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See the LICENSE file in your DokuWiki folder for details
