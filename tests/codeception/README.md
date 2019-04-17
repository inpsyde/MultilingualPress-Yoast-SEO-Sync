# Codeception Tests

## Intro

The repository contains some acceptance and end-to-end test witten for [Codeception](https://codeception.com/)
framework.

These tests require [**Docker**](https://www.docker.com/) and
**[Docker Compose](https://docs.docker.com/compose/install/)** to be ran.



## Codeception configuration

Codeception tests are organized in "suites".

Every suite, is a folder inside `tests/codeception/tests` folder.

For each suite, Codeception requires a file named like `<suite name>.suite.yml`, but this files
are *not* in the repository after cloning.

What is found in the repository instead are files named: `<suite name>.suite.template.yml`.

These files are used to create the proper configuration files during the Docker bootstrap process.

The reason is that an URL need to be replaced to those files to work. More on this below.

The generated `<suite name>.suite.yml` files are git-ignored.

It is **important** to note that in case a _new_ suite is added, a "template" configuration file
must be put in place for the tests run successfully.

Renaming (or copying) the usual `<suite name>.suite.yml` (e.g. generated via Codeception) to 
`<suite name>.suite.template.yml` will suffice.



## Commands

The repository contains a file located at **`/bin/codeception.sh`** that facilitates the running
of of Codeception tests.

This file contains 4 commands:

- **`help`**
- **`docker`**
- **`tests`**

(plus a `--version` flag).


All the commands can also be safely run from the `/bin` directory, saving some typing.

E.g. instead of

```
cd multilingualpress-yoast-seo-sync
bin/codeception.sh --version
```

we can do:

```
cd multilingualpress-yoast-seo-sync/bin
codeception.sh --version
```

For brevity sake in the rest of this README the latter syntax is assumend.



## Help Command

By running:

```
codeception.sh help
```

it is possible to get the full list of the commands provided by the script, with a short description.

By passing as argument a command name, e.g.:

```
codeception.sh help docker

codeception.sh help tests
```

it is possible to get quite detailed information about the usage of command.



## Docker Command

The `docker` commands will:

1. Cleanup any previous Docker status and (re)start all Docker services necessary to run the tests
2. Prepare the WordPress container by installing core, adding users, converting installation to multisite
   and activating MultilingualPress.
3. Export database in the Codeception data folder
4. Setup Codeception configuration files (`*.suites.yml`) for the machine.

To be able to do all the above, **the command needs to know the IP of the machine running Docker**.

The IP is passed as argument to the command. For example:

```
codeception.sh docker 192.168.1.2
```

**Please note**: In most cases the IP address to pass will be the IP of the _host_ (something that
could be retrieved in Linux systems via `ifconfig` or on Windows via `ipconfig`).

However, in case Docker is ran from virtual machine (e.g. [Docker Machine](https://docs.docker.com/machine/))
the IP to pass must be the address of the virtual machine.

In case of Docker Machine is in use (very likely on Windows 7), the IP can be retrieved via:

```
docker-machine ip
```


### Shutting Down Docker

It may be desirable to shoutdown the Docker environment ftaer we are done with tests.

The `docker` command can (and should) be used for the scope, because it not only save some typing,
but also cleanup Docker volumes.

To shoutdown Docker environment just pass 'down' as argument:

```
codeception.sh docker down
```

For a comparison, the line above is equal to:

```
docker-compose down --remove-orphans
docker volume rm multilingualpresspro_wordpress
docker volume rm multilingualpresspro_codeception
```


## Tests Command

The `tests` command is what needs to be used to actually run codeception tests.

It could be used in two ways:

1. First the `docker` command is ran to prepare the environment and then the `tests` command is
   used to just run tests
2. The `tests` command is used to _both_ prepare the environment and run the tests.


In the first case, we will do something like this:

```
codeception.sh docker 192.168.1.2
codeception.sh tests
```

In the second case, because we need to prepare the environment, we need to pass the IP, so we will
do something like this:

```
codeception.sh tests 192.168.1.2
```

Please note that when the IP is provided, the Docker environment is always restarted, no matter
if it was already running.

Also note that if the IP is not provided and the Docker environment is not running, the command will
raise an error.


### Running specific suites/tests

Codeception allows to run only specific tests suites or even specific tests via its `codecept run`
command, `codeception.sh tests` allows to do the same, and uses the same syntax.

So we could do something like:

```
codeception.sh tests acceptance

codeception.sh tests acceptance MyClassName:myTestName
```

This works even if we use `tests` command to  prepare the Docker environment, in fact, the following
commands are valid syntax:

```
codeception.sh tests 192.168.1.2 acceptance

codeception.sh tests 192.168.1.2 acceptance MyClassName:myTestName
```

Just remember that the IP address, when provided, must be the first argument of the command.


#### Running tests via `docker-compose`

After the Docker environment is ready (e.g. prepared via `docker` command), it is absolutely
fine to run tests via `docker-compose run` command, it is just a bit more verbose.

E.g. the following commmand:

```
codeception.sh tests system MyClassName:myTestName
```

is equivalent to:

```
docker-compose run --rm codecept run system MyClassName:myTestName
```
