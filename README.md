# Project Overview

The task is to build a currency exchange rate API that will save a set of currency rate amounts with the EUR base currency into a MySQL Database and includes redis cache not to trigger third party api nor the database all the time a request is triggered. The following requirements must be met:

# Content

- [Project Overview](#project-overview)
  * [Content](#content)
  * [Requirements](#requirements)
  * [Evaluation Criteria](#evaluation-criteria)
  * [Getting Started](#getting-started)
    + [Prerequisites](#prerequisites)
    + [Clone the repository](#clone-the-repository)
    + [Configure the environment](#configure-the-environment)
    + [Start the application](#start-the-application)
    + [Stop the application](#stop-the-application)
  * [Usage cases](#usage-cases)


## Requirements

- Build a Symfony 5 project with the following dependencies:
    - Doctrine (with migrations)
    - Redis
    - GuzzleHTTP (for making HTTP requests)

- Create a console command that will fetch the currency exchange rates for a given set of currencies from the Open Exchange Rates API. The command should have the following signature:

  ```
    php bin/console app:currency:rates [base_currency] [target_currency_1] [target_currency_2] ... [target_currency_n]
  ```

  The command should make an HTTP request to the API to fetch the exchange rates for the given currencies, save the rates into a MySQL Database with the EUR base currency and store the rates in Redis. This must be set as a cron job to be triggered daily at 1am.

- Create an endpoint that will return the exchange rates for a given set of currencies. The endpoint should have the following signature:

```
  GET /api/exchange-rates?base_currency=[base_currency]&target_currencies=[target_currency_1,target_currency_2,...,target_currency_n]
```
  The endpoint should first check Redis for the requested rates. If the rates are not in Redis, it should fetch them from the MySQL Database, store them in Redis and return the rates. If the rates are in Redis, it should return the rates directly from Redis.

- Write unit tests to verify the functionality of the console command and the endpoint.
- Use doctrine migrations to manage database schema changes.
- Push your changes to a GitHub account by creating a timeline of git commits.

## Evaluation Criteria

- Code quality and organization
- Correct use of Symfony components and best practices
- Correct implementation of the API requirements
- Unit tests that verify the functionality of the console command and the endpoint
- Effective use of git and GitHub

--- 

## Getting Started

### Prerequisites

Before getting started with the Symfony application, you need to have the following programs installed:

- Docker: [Download here](https://www.docker.com/get-started)
- docker-compose: [Installation instructions](https://docs.docker.com/compose/install/)

### Clone the repository

To get started, clone the application repository from GitHub:

```sh
git clone https://github.com/carlitosry/exchange-rates.git
```

### Configure the environment

- Navigate to the application directory:

```bat
cd exchange-rates
```

- Create a `.env` file from the `.env.example` file:

```bat
cp .env.example .env
```

### Start the application

To start the application, follow these steps:

- Navigate to the application directory:

```bat
cd exchange-rates
```

- Build the container images and start the services:

```bat
docker-compose up -d
```

- Get composer dependencies trough app container

```bat
docker-compose exec app composer install
```

- You should execute the migrations to update the database schema

```bat
docker-compose exec app php bin/console doc:mig:mig
```

- Open your web browser and go to `http://localhost:8200` to see the application in action.

### Stop the application

To stop the application containers, run the following command in the application directory:

```bat
docker-compose down
```

## Usage information

The Symfony application is an API that consumes a third-party API to obtain currency exchange rate information. This application runs in four Docker containers, which include the MySQL database, Redis, the Apache web server, and a cron container that executes the command that retrieves the currency exchange rates from the third-party API.

The command runs automatically once a day at 1am and stores the results in both the MySQL database and Redis. The application also has a public API that allows users to obtain currency exchange rates for a given set of currencies.
this cron is configured on the following file:
```
docker/cron/crontab
```

The application has been developed using the Symfony 5 framework and makes use of various Symfony components, such as Doctrine for database management and GuzzleHTTP for making HTTP requests to the third-party API. Unit tests have also been written to ensure the functionality of both the command and the API.
once running the app, locally you could access to the endpoint through the following url: 
```
http://localhost:8200/api/exchange-rates?base_currency=EUR&target_currencies=USD,COP,BTC
```

This endpoint has two parameter, the first one is required, but the second one is optional, this means that if you not set the target currencies the app fetch all of available currencies.
we would get the following result:
```json
{
  "base_currency": "VES",
  "target_currencies": {
    "USD": 0.0409175214064105,
    "COP": 189.83428763904593,
    "BTC": 0.0000014321132492243673,
    "EUR": 0.03761408623318536
  }
}
```


In summary, the Symfony application is a currency exchange rate API solution that runs in Docker containers and uses a command to consume a third-party API and store the results in the database and Redis. The application also offers a public API for users to query currency exchange rates.

