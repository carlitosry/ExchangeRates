# Project Overview

The task is to build a currency exchange rate API that will save a set of currency rate amounts with the EUR base currency into a MySQL Database and includes redis cache not to trigger third party api nor the database all the time a request is triggered. The following requirements must be met:

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
