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
- [Post-installation configuration](#post-installation-configuration)
  - [First Admin Access](#first-admin-access)
  - [Plugins](#plugins)
  - [CKAN usage](#ckan-usage)
  - [SMTP service](#smtp-service)
  - [Manage pages](#manage-pages)
  - [SPOD language](#spod-language)
  
  


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

Set `HOST_URL`, it is the most important. It is the URL of the machine where the SPOD application is installed. It is used to configure the Nginx reverse proxy.
If you are installing the application on a local machine, you can use `localhost` as value. If you are installing locally with custom URL, you have to add an entry to the `/etc/hosts` file.

:warning: In local installation we suggest to use your **local IP address** instead of `localhost` or `custom URL`, in this way you can access to the application from other devices in the same network and **you haven't to add an entry to the `/etc/hosts` file**, both for locating oxwall and for the CKAN service.

For example, if you are using `spod.local` as URL, you have to add the following line to the `/etc/hosts` file:
```
127.0.0.1 spod.local
```

You also need to configure the CKAN environment variables in the `.ckan-env.example` file in `/docker/ckan` directory. You have to change `sysadmin` infos. The following table shows the environment variables and their description:
| Variable | Description |
| --- | --- |
| CKAN_SYSADMIN__NAME | The name of the sysadmin user. |
| CKAN_SYSADMIN__EMAIL | The email of the sysadmin user. |
| CKAN_SYSADMIN__PASSWORD | The password of the sysadmin user. |


CKAN isn't managed by the Nginx reverse proxy, so you have to add the following entry to the `/etc/hosts` if you are using local deployment with `localhost` as `HOST_URL`. 
```
127.0.0.1 ckan
```

It's required because SPOD plugins use the CKAN API to retrieve data from CKAN, and requests are sent from the browser. Containers DNS resolve containers name, but the browser is an external component and it isn't aware of container names. So, we have to add an entry to the `/etc/hosts` file to resolve the containers name.

 ---

You can install the whole SPOD application using the following command:
```bash
sudo bash deploy.sh
```
It downloads CKAN repository, builds the Docker images, starts containers using environment variables and create admin users. The installation process can take a while, depending on your machine.

## Post-installation configuration

### First Admin Access

When the installation is completed, you can access the SPOD application at `http://yourdomain` and complete the initial configuration.

:warning: **Be consistent with the credentials**: you need to use the same credentials of environment variables of the `.env` file during the form-based installation. Oxwall admin user must be is the same of Oauth2 server.

Completed the web-based installation, you can access to the SPOD admin panel at `http://yourdomain/admin` and complete the configuration of the SPOD plugins. By default, all the plugins are enabled. You can disable the plugins that you don't need.

The `SPOD Theme Matter` is automatically enabled and configured.
It is the theme of the SPOD application.


### Plugins

There are some plugins that require some additional configuration. The following table shows the plugins that require additional configuration and the related documentation:
| Plugin | Documentation | 
| --- | --- |
| ODE| *DEEP* paths are already configured. You need to change the URL from `http://deep.routetopa.eu` to `http://yourdomain`, leaving the rest of the path unchanged. In `Providers` section you have to add dataset provider url and click `Create cache`. You can also add CKAN service created before. The url should be like `http://yourdomain:5000` (or `http://ckan:5000` if you configured `/etc/hosts` in a local installation)|
| OAuth2 | Fill the entries with:  <ul> <li> `Client ID` = `spod-website`</li> `Client secret` = Your password in the `.env` file </li> <li>`Grant Type`= `authorization_code`</li> <li> `Scope` = `authenticate` </li> <li> `base URL` = `http://yourdomain/oauht2/`  </li> <li> `Authorize endpoint` = `http://yourdomain/oauth2/oauth/authorize` </li> <li> `Request token` = `http://yourdomain/oauth2/oauth/token` </li> <li> `User info` = `http://yourdomain/oauth2/oauth/v1/userinfo` </li> </ul> |

### CKAN usage
SPOD can retrieve dataset from different sources. You can add CKAN services in the `Providers` section of the `ODE` plugin as described in the previous section.

From `http://yourdomain:5000` (or `http://ckan:5000` if you configured `/etc/hosts`) you can access to the CKAN web interface. For first access, you can use the credentials of the CKAN sysadmin user that you configured in the `.ckan-env` file. 
First, you have to create an organization. You can create an organization for each course or for each group of users. You can also create a single organization for all the users. 
Then, you can create datatests and upload data.
These datasets will be available when you create a datalet. If you can't find the dataset, you have to click on `Create cache` button in the `Providers` section of the `ODE` plugin to refresh the cache. If you still can't find the dataset, check your CKAN configuration.

### SMTP service
To send emails, expecially to sign-up users, you have to configure it in the `SMTP` section of `Settings Admin Panel`. You have to enable the SMTP service and fill the entries with your SMTP server configuration. You can use a SMTP service like [Elastic Email](https://elasticemail.com/). 
You can test the SMTP service using the `Test` button before saving the configuration.

### Manage pages
Oxwall has a page management system. Some pages are already created and you can edit them in the `Pages` section of the `Settings Admin Panel`. You can also create new pages and add them to the menu. Plugins can also create pages. You can edit the content of the pages using the WYSIWYG editor.
Some plugins, like `Agora`, require a page created manually from the admin panel. You have to provide general information about the plugin and the URL of the page. The URL of the page must be unique and it is used to access to the plugin. For example, if you create a page for `Agora`, you can access to the plugin at `http://yourdomain/agora` because is the `Key Value` of the plugin.

### SPOD language
Oxwall by default is in English. You can change the language in the `Settings` section of the admin panel. You could manually add a new language, but it is not recommended. We provide a zip file with the translation of the SPOD application in Italian. To add the `ita-ln-pack.zip` file, you have to click `other languages` and then `Add new language pack`. The zip file is available in this repository. 

