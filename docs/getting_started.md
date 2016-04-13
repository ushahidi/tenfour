## Getting Started

Before setting up the project, make sure you have the following installed:

- [Composer](https://getcomposer.org/)
- [Vagrant](https://www.vagrantup.com/)

### Setup

```bash
git clone git@github.com:ushahidi/rollcall-api.git
cd rollcall-api
composer install
vagrant up
```

The box comes with all these goodies:

```bash
vagrant@rollcall-api:~/rollcall-api$ node -v
v0.10.31

vagrant@rollcall-api:~/rollcall-api$ npm -v
1.4.23

vagrant@rollcall-api:~/rollcall-api$ php -v
PHP 5.6.0-1+deb.sury.org~trusty+1 (cli) (built: Aug 28 2014 14:55:42)

vagrant@rollcall-api:~/rollcall-api$ ruby -v
ruby 1.9.3p484 (2013-11-22 revision 43786) [x86_64-linux]
```

#### Environment configuration

Copy the environment configuration.

```bash
cp .env.gen .env
```

And finally, add the following to your `/etc/hosts` file:

```
192.168.10.10 rollcall.dev
```

Go to http://rollcall.dev/ in your browser to verify that everything worked.
