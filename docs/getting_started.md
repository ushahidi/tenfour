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
./artisan migrate
```

### Get some sample data

```bash
./artisan db:seed
```

#### Environment configuration

Copy the environment configuration.

```bash
cp .env.example .env
```

And finally, add the following to your `/etc/hosts` file:

```
192.168.10.10 rollcall.dev
```

Go to http://rollcall.dev/ in your browser to verify that everything worked.
