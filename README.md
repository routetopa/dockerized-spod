# DOCKERIZED SPOD
The Social Platform for Open Data (SPOD) enables social interactions among citizens around open datasets coming from different dataset providers.

This is a full guide for SPOD installation as Dockerized application. You can install in All-in-one machine with `docker-compose` or in a cluster using `Kubernetes`. For the correct functioning of the entire SPOD environment, in addition to the core containers, other services are deployed such as CKAN and reverse proxy server. 

## Table of contents
<!-- no toc -->
- [Services](#services)
  - [Oxwall](#oxwall)
  - [SPOD services](#spod-services)
  - [MySQL](#mysql)
  - [DEEP](#deep)
  - [Oauth2 Server](#oauth2-server)
  - [CKAN](#ckan)
  - [Ngnix Reverse Proxy](#ngnix-reverse-proxy)
- [Pre-requisites](#pre-requisites)
- [Installation](#installation)
  - [All-in-one machine](#all-in-one-machine)
  - [Cluster](#cluster)
- [Post-Installion configuration](#post-installion-configuration)


## Services
The whole SPOD application is composed of several services, from the social platform to dataset management. Each service is a Docker container. The following list shows the services and the related Docker image.

### Oxwall
Oxwall is an open source social network platform, which allows you to create your own social network website. It is a PHP/MySQL based software, which is easy to install, customize and use. The SPOD version of Oxwall is customized to support PHP 7.4, latest MySql version the SPOD plugins.

Docker image: [isislab/oxwall](https://hub.docker.com/r/isislab/oxwall)

### SPOD services
Some SPOD plugins work as services (NodeJS applications), so they need to be installed/executed as Docker containers. From github repostories, we created Docker images for each service. The following list shows the SPOD services and the related Docker image:
- **Etherpad**: required for [Cocreation](https://github.com/routetopa/spod-plugin-cocreation), a real-time collaborative editor created by Ether group.
  Docker image: [spod/etherpad](https://hub.docker.com/r/spod/etherpad)
- **Ethersheet**: required for [Cocre[Docker](https://docs.docker.com/install/) and [Docker Compose](https://docs.docker.com/compose/install/) installed on the machine.
-  version greater than 5.7. We use the official MySQL Docker image. We tested the SPOD application with latest MySQL version.

Docker image: [mysql](https://hub.docker.com/_/mysql)

### DEEP
The **D**atalEts **E**cosystem **P**rovider (**DEEP**) is a RESTful web service written in PHP. It make available a public discovery service that retrieve all the Datalet available into the system and a service call that provide a specific Datalet. Developed by the [UNISA TEAM](http://www.isislab.it/) for the [ROUTE-TO-PA PROJECT](http://www.routetopa.eu/).

Docker image: [isislab/deep](https://hub.docker.com/r/isislab/deep)

### Oauth2 Server
Provides an authorization server via OAuth2 and identity server via OpenID Connect. Works with the SPOD plugins to provide authentication and authorization. 
It's based on a PHP application using Laravel framework.
Docker Image builds and deploys the application and initialize the database creating the tables and the admin user.

Docker image: [isislab/oauth2](https://hub.docker.com/r/isislab/oauth2) 

### CKAN
CKAN is a powerful data management system that makes data accessible – by providing tools to streamline publishing, sharing, finding and using data. It is a Python/PostgreSQL based software, which is easy to install, customize and use. The whole CKAN application is composed of 5 services:
- **CKAN**: the main service, which provides the web interface and the REST API.
- **PostgreSQL**: the database service.  
- **Solr**: the search service. It is the popular, blazing-fast, open source enterprise search platform built on Apache Lucene
- **DataPusher**: it is a standalone web service that automatically downloads any tabular data files like CSV or Excel from a CKAN site's resources when they are added to the CKAN site, parses them to pull out the actual data, then uses the DataStore API to push the data into the CKAN site's DataStore.
- **Redis**: it is an open source (BSD licensed), in-memory data structure store, used as a database, cache and message broker.

All the services are based on [Keitaro CKAN Docker image](https://github.com/keitaroinc/docker-ckan) (except for Redis). We customized CKAN image to support the SPOD plugins.

Docker image: [isislab/ckan](https://hub.docker.com/r/isislab/ckan)  

### Ngnix Reverse Proxy
Nginx container provides a reverse proxy for the SPOD services. It is based on the official Nginx Docker image. It is used to expose the SPOD application on port 80 and redirect the requests to the right service.

<!-- To Check -->
Docker image: [nginx-proxy](https://hub.docker.com/r/nginxproxy/nginx-proxy)

## Pre-requisites
To install SPOD as Dockerized application, you need to install:
- [Docker](https://docs.docker.com/engine/install/) as container engine.
- [Docker Compose](https://docs.docker.com/compose/install/) for all-in-one machine installation.
- [Kubernetes](https://kubernetes.io/docs/setup/) for cluster installation.
- [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git) to clone some external repositories.

Containers expose the following ports, so you need to make sure that they are not already used by other services:
- 80: Nginx reverse proxy
- 5000: CKAN
- 8000: DataPusher to upload data to CKAN


## Installation
### All-in-one machine
To install SPOD on a single machine, you need to clone the [SPOD Docker repository](no-url-yet).

Before deploying the application, you need to configure the environment variables in the `.env.example` file in `/docker` directory.
The following table shows the environment variables and their description:
| Variable | Description |
| --- | --- |
| MYSQL_ROOT_PASSWORD | Password for the MySQL root user |
| MYSQL_PASSWORD | Password for the MySQL user |
| MYSQL_DATABASE | Name of the MySQL database |
| HOST_URL | Host URL of the SPOD application |
| MAIL | Email address of the SPOD administrator |
| PASSWORD | Password of the SPOD administrator |

Set `HOST_URL` is the most importabt. It is the URL of the machine where the SPOD application is installed. It is used to configure the Nginx reverse proxy. If you are installing the application on a local machine, you can use `localhost` as value. If you are installing locally with custom URL, you have to add an entry to the `/etc/hosts` file. For example, if you are using `spod.local` as URL, you have to add the following line to the `/etc/hosts` file:
```
127.0.0.1 spod.local
```

You also need to configure the CKAN environment variables in the `.ckan-env.example` file in `/docker/ckan` directory. You have to change `sysadmin` infos. The following table shows the environment variables and their description:
| Variable | Description |
| --- | --- |
| CKAN_SYSADMIN__NAME | The name of the sysadmin user. |
| CKAN_SYSADMIN__EMAIL | The email of the sysadmin user. |
| CKAN_SYSADMIN__PASSWORD | The password of the sysadmin user. |

CKAN isn't managed by the Nginx reverse proxy, so you have to add the following entry to the `/etc/hosts` if you are using local deployment. 
```
127.0.0.1 ckan
```

It's required because SPOD plugins use the CKAN API to retrieve data from CKAN, and requests are sent from the browser. Containers DNS resolve containers name, but the browser is an external component and it doesn't know the containers name. So, we have to add an entry to the `/etc/hosts` file to resolve the containers name.

 ---

You can install the whole SPOD application using the following command:
```bash
sudo bash deploy.sh
```
It downloads CKAN repository, builds the Docker images, starts containers using environment variables and create admin users. The installation process can take a while, depending on your machine.

## Post-installation

When the installation is completed, you can access the SPOD application at `http://yourdomain` and complete the initial configuration.

:warning: **Be consistent with the credentials**: you need to use the same credentials of environment variables of the `.env` file during the form-based installation. Oxwall admin user must be is the same of Oauth2 server.

Completed the web-based installation, you can access to the SPOD admin panel at `http://yourdomain/admin` and complete the configuration of the SPOD plugins. By default, all the plugins are enabled. You can disable the plugins that you don't need.

You have to activate `SPOD Theme` in the `Appearance` section of the admin panel. It is the theme of the SPOD application.

There are some plugins that require some additional configuration. The following table shows the plugins that require additional configuration and the related documentation:
| Plugin | Documentation | 
| --- | --- |
| ODE| *DEEP* paths are already configured. You need to change the URL from `http://deep.routetopa.eu` to `http://yourdomain`, leaving the rest of the path unchanged. In `Providers` section you have to add CKAN url and click `Create cache`. |
| OAuth2 | Fill the entries with:  <ul> <li> `Client ID` = `spod-website`</li> `Client secret` = Your password in the `.env` file </li> <li>`Grant Type`= `authorization_code`</li> <li> `Scope` = `authenticate` </li> <li> `base URL` = `http://yourdomain/oauht2/`  </li> <li> `Authorize endpoint` = `http://yourdomain/oauth2/oauth/authorize` </li> <li> `Request token` = `http://yourdomain/oauth2/oauth/token` </li> <li> `User info` = `http://yourdomain/oauth2/oauth/v1/userinfo` </li> </ul> |

