## Getting Started

## TODO review this document after TenFour rebrand

Before setting up the project, make sure you have the following installed:

- [Composer](https://getcomposer.org/)
- [Vagrant](https://www.vagrantup.com/)

### Setup

```bash
git clone git@github.com:ushahidi/tenfour-api.git
cd tenfour-api
composer install
```

### Environment configuration

Copy the environment configuration.

```bash
cp .env.example .env
```

### Run migrations and add sample data

```bash
vagrant up
vagrant ssh
cd tenfour
./artisan migrate
./artisan db:seed
```

And finally, add the following to your `/etc/hosts` file:

```
192.168.10.10 tenfour.local
```

Go to http://tenfour.local/ in your browser to verify that everything worked.

### Log in as an organization owner
Use organization `tenfourtest`
Use the email `tenfour@ushahidi.com` with password `westgate`
