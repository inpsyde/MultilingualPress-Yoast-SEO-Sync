#!/bin/bash
set -e
cd "$(dirname "$0")/.."

SCRIPT_NAME=${0##*/}
SCRIPT_VERSION='1.0'
COMPOSE_SERVICES=( 'codecept' 'chrome' 'wordpress' 'cli' 'mysql' 'phpmyadmin' )

# Print a coloured line to console
function line() {
	case "$1" in
		error)
			printf " \033[1;31m$2\033[0m\n"
			;;
		info)
			printf " \033[1;36m$2\033[0m\n"
			;;
		highlight)
			printf " \033[0;32m$2\033[0m\n"
			;;
		*)
			printf " $1\n"
			;;
	esac
}

# Cleanup previous status of Docker
function delete_docker() {
    line 'info' "Cleaning up Docker status..."
    docker-compose down --remove-orphans >/dev/null 2>&1
    if [ "$(docker volume ls | grep multilingualpress-yoast-seo-sync_wordpress)" ]; then
        docker volume rm multilingualpress-yoast-seo-sync_wordpress >/dev/null 2>&1
    fi
    if [ "$(docker volume ls | grep multilingualpress-yoast-seo-sync_codeception)" ]; then
        docker volume rm multilingualpress-yoast-seo-sync_codeception >/dev/null 2>&1
    fi
}

# Start docker, will pull images if not yet there
function start_docker() {
    docker-compose pull --parallel >/dev/null 2>&1
    docker-compose up -d >/dev/null 2>&1
    line 'info' "Waiting for WordPress container at $MACHINE_URL (and it takes some time as well)..."
    until $(curl -L ${MACHINE_URL} -so - 2>&1 | grep -q "WordPress"); do
        sleep 3
    done
    line 'highlight' "WordPress container ready!"
}

# Run WP CLI commands to prepare WordPress container
function prepare_wordpress() {
    docker-compose run -u 33 cli core install --url=${MACHINE_URL} --title=WordPress --admin_user=admin --admin_password=password --admin_email=admin@example.com >/dev/null 2>&1
    docker-compose run -u 33 cli plugin delete hello
    docker-compose run -u 33 cli plugin delete akismet
    docker-compose run -u 33 cli core multisite-convert >/dev/null 2>&1
    docker-compose run -u 33 cli site create --slug=site2 --title="Site 2" --email=site2@example.com >/dev/null 2>&1
    docker-compose run cli option update home ${MACHINE_URL} >/dev/null 2>&1
    docker-compose run cli option update siteurl ${MACHINE_URL} >/dev/null 2>&1

    docker-compose run -u 33 cli core update

    line 'info' "Installing Yoast SEO..."
    docker-compose run -u 33 cli plugin install wordpress-seo
    line 'info' "Activating Yoast SEO..."
    docker-compose run --rm cli plugin activate wordpress-seo --network
    line 'info' "Activating MultilingualPress..."
    docker-compose run --rm cli plugin activate multilingualpress
    line 'info' "Activating MultilingualPress Yoast SEO Sync..."
    docker-compose run --rm cli plugin activate multilingualpress-yoast-seo-sync
}

# Export WP db to data folder
function prepare_data() {
    mkdir -p ./tests/codeception/_data
    docker-compose run cli db export wp-content/plugins/multilingualpress-yoast-seo-sync/tests/codeception/_data/dump.sql >/dev/null 2>&1
    docker-compose run --rm cli eval-file '../codeception/url-setter.php' ${MACHINE_URL} --skip-wordpress
}

# do anything needs to be dobe to prepare tests environment
function prepare_tests_environment() {
    delete_docker
    line 'info' "Starting up up Docker. Grab a coffee, it could take a while..."
    start_docker
    line 'info' "Installing and preparing WordPress..."
    prepare_wordpress
    line 'info' "Preparing tests data..."
    prepare_data
}

# just a wrapper around docker-compose run codecept
function run_tests() {
    if [ -z "$1" ]; then
        docker-compose run --rm codecept run
    else
        docker-compose run --rm codecept run "$@"
    fi
}

# check if a given (as param) argument is a valid IP
function valid_ip()
{
    local  ip=$1
    local  stat=1
    if [[ $ip =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
        OIFS=$IFS
        IFS='.'
        ip=($ip)
        IFS=$OIFS
        [[ ${ip[0]} -le 255 && ${ip[1]} -le 255 \
            && ${ip[2]} -le 255 && ${ip[3]} -le 255 ]]
        stat=$?
    fi
    return $stat
}

# Print an error in case of invalid IP
function invalid_ip_error() {
    line
    line 'error' 'Please provide your machine IP as argument. Use:'
    line 'error' "'${SCRIPT_NAME} help docker' for more info."
}

# Print script version
function version() {
    line
    line 'highlight' "MultilingualPress Codeception tests runner v$SCRIPT_VERSION."
}

# Print help for all the commands
function print_help() {
	case "$1" in
		docker)
			line
			line 'highlight' "Startup Docker and prepare containers to run tests for given ID."
			line 'highlight' "Do NOT run any test."
			line
			line 'info' "USAGE:"
			line "  ${SCRIPT_NAME} docker [IP-ADDRESS]"
			line
			line 'info' "[IP-ADDRESS]:"
			line "  The IP address of the machine running Docker."
			line "  In most cases it will be the IP of the host, but in case Docker is ran from"
			line "  a virtual machine (e.g. Docker Machine) his must be the virtual machine address"
			line "  This must be the address of the virtual machine."
			line "  In case of docker-machine (e.g. on Windows 7) the IP can be retrieved via:"
			line "  $ docker-machine ip"
			;;
		tests)
			line
			line 'highlight' "Run the tests. If Docker has not be setup earlier this command will do it."
			line 'highlight' "To setup Docker it is necessary to pass the IP of the machine."
			line 'highlight' "use '${SCRIPT_NAME} help docker' for more info."
			line
			line 'info' "USAGE: ${SCRIPT_NAME} tests [[IP]] [[TEST-SUITE]] [[TEST-NAME]]"
			line
			line 'info' "       ${SCRIPT_NAME} tests [[TEST-SUITE]] [[TEST-NAME]]"
			line
			line 'info' "[IP] (optional):"
			line '  The IP address of the machine running Docker.'
			line '  When provided it will (re)start docker just like the `docker` command does.'
			line '  So providing an IP equals to run `docker` first and `tests` after that.'
			line '  When not provided, the tests will run just fine if docker services are already "up",'
			line '  but will fail if docker services are "down".'
			line
			line 'info' "[TEST-SUITE] (optional):"
			line "  The test suite to run. If not provided, all tests from all suites will be runned."
			line
			line 'info' "[TEST-NAME] (optional):"
			line "  The test name to run for given suite."
			line "  If not provided, all tests from given suite will be run."
			line "  A test name can only be given if the the suite has been also given."
			line
			line 'info' "Examples:"
			line "  ${SCRIPT_NAME} tests"
			line "  ${SCRIPT_NAME} tests 192.168.1.2"
			line "  ${SCRIPT_NAME} tests 192.168.1.2 acceptance"
			line "  ${SCRIPT_NAME} tests 192.168.1.2 acceptance MyClassName:myTestName"
			line "  ${SCRIPT_NAME} tests acceptance"
			line "  ${SCRIPT_NAME} tests acceptance MyClassName:myTestName"
			;;
		*)
			version
			line
			line 'info' "USAGE: ${SCRIPT_NAME} [COMMAND] [ARGS]"
			line
			line 'info' "[COMMAND]:"
			line
			line "  docker: Startup Docker and prepare containers to run tests for given ID."
			line
			line "  tests:  Run the tests optionally boostrapping Docker if not done earlier."
			line
			line "  help:   This help. Use: "
			line "          $ ${SCRIPT_NAME} help [COMMAND]"
			line "          to get guidance for specific command."
			;;
	esac
	exit 0
}

# Check if all docker services are up and echo 'up' if so.
function is_docker_up() {
    local all='';
    local up='';
    local is_up='';
    for i in "${COMPOSE_SERVICES[@]}"
    do
        is_up=`docker-compose ps -q ${i}`
        all="$all."
        if [[ "$is_up" != "" ]]; then
            up="$up."
        fi
    done
    if [[ "$all" == "$up" ]]; then
        echo 'up';
    else
        echo '';
    fi
}


case "$1" in
	docker)
	    if [ "$2" == 'down' ]; then
		    delete_docker
		    exit 0
        fi
		if ! valid_ip "$2"; then
		    invalid_ip_error
		    exit 1
        fi
        MACHINE_URL="http://${2}"
        prepare_tests_environment
		;;
	tests)
	    args=( "${@:2}" )
	    if valid_ip $2; then
	        args="${@:3}"
	        MACHINE_URL="http://${2}"
	        prepare_tests_environment
	        docker_up='up'
	    else
	        line 'info' "No IP provided. Checking if Docker services are running..."
	        docker_up=$(is_docker_up)
	    fi
	    if [ "$docker_up" != 'up' ]; then
	        line 'error' "No IP provided and Docker services are not running. Cannot run tests."
	        line 'error' "Cannot run tests."
	        line 'error' 'Run `docker` command first or pass IP to `test` command.'
	        line 'error' 'Run `help tests` for more info.'
	        invalid_ip_error
	        exit 1
        fi
        line 'info' "Docker services are running. Proceeding with tests..."
	    run_tests "${args[@]}"
		;;
	--version)
		version
		;;
	help)
		print_help "$2"
		;;
	*)
	    line 'error' 'Invalid Usage.'
	    line
		print_help
		;;
esac

