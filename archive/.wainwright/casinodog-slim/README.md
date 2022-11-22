## casinoman: wainwright's casinodog


base plugin with all game sense, package works best under fresh install of laravel ++ breeze "api" package, but should work within any existing laravel package aswell. 

**Still somewhat rough edges, however you can use installer to setup: https://github.com/casino-man/wainwright-installer.sh.**

Make sure to set correct .env

After installing laravel/running the installer script, run:
`php artisan casino-dog:install`

After which you can login at admin: `/allseeingdavid` with login entered on install.

To import default gameslistings:
`php artisan casino-dog:retrieve-default-gameslist {provider}`


Alternatively you can deploy app using Heroku using the skeleton at Gitlab.com/casinoman.


# bit more:
There is a 'cheat' plugin that is called "game respin templates" basically when player spins slotmachine and loses their full bet it saves this game to the database as a 'template'. 
This 'template' (game result) is saved so it can be used for any other player in future on the slotmachine getting a big win, we then simply swap the game result with the loss (under any bet amount, depending on the provider it maps any game, that means a player losing their bet on 0.10$ spins can be used on a player spinning 1000$ per spin).

Bonus features are saved under specific bonus_game id's where possible. I'm in progress of writing the documentations, but it's lot of stuff to write, so few more days the most I hope to have a 'basic' documentation ready atleast explaining and showing some of the features.

You can use "GameKernelTrait" anywhere in the package. To create/scaffold a new gameprovider, if you wanna implement any other slotmachine provider, just call `php artisan casino-dog:create-gameprovider` and all controllers, datatables, frontend launcher etc. will be scaffolded for new gameprovider.

You can automatically import games from SOFTSWISS, PARIMATCH and PLAYTECH game formats, but that's more advanced and honestly with the default gameslist, I saved most current provider's their full listings so just use the retriever command.


This is only the aggregation, best is to use this in combination with the client SDK the `casino-dog-operator-api`.


You can import over 20+ game providers, under which Pragmatic Play, 3oaks, Relax Gaming and many more to be used for your own casino, your own aggregation business or just like me wanting to learn how these criminals are achieving their goals out of mere curousity.


App is inspired by fraud committed by various parties lead by Vlad Suciu, Laurence Phillipe, David G. Wainwright, Max Wright and many more.


## Workers

Worker & schedule runner required to run automated game importer jobs:

Setting up cronjob to run every minute `php artisan schedule:run`:

Run `crontab-e` select vi/nano editor.

Paste at bottom:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Check if cron running:
```bash
systemctl status cron
```

Setting up supervisor:
```bash
sudo apt-get install supervisor
cd /etc/supervisor/config.d
sudo nano laravel-worker.conf
```

laravel-worker.conf:
```bash
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/laravel/artisan queue:work --sleep=0.1 --tries=2 >
autostart=true
autorestart=true
user=root
numprocs=10
redirect_stderr=true
stdout_logfile=/var/www/laravel/storage/logs/worker.log
```

```bash
service supervisor restart
```


You can disable automated game import processing in config/casino-dog.php
