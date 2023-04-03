Project Overview
================

The task is to build a currency exchange rate API that will save a set of currency rate amounts with the EUR base currency into a MySQL Database and include redis cache to avoid triggering third-party APIs and databases all the time a request is triggered. The following requirements must be met:

Content
-------

*   [Project Overview](#project-overview)
*   [Requirements](#requirements)
*   [Evaluation Criteria](#evaluation-criteria)
*   [Getting Started](#getting-started)
    * [Prerequisites](#prerequisites)
    * [Installation](#installation)
    * [Usage](#usage)
       *   [Fetch Exchange Rates Command](#fetch-exchange-rates-command)
       *   [Exchange Rates Endpoint](#exchange-rates-endpoint)
*   [API Reference](#api-reference)
    *   [Endpoints](#endpoints)
    *   [Requests](#requests)
        *   [Fetch Exchange Rates Request](#fetch-exchange-rates-request)
    *   [Responses](#responses)
        *   [Fetch Exchange Rates Response](#fetch-exchange-rates-response)
*   [Testing](#testing)

* * *

Requirements
------------

*   Build a Symfony 5 project with the following dependencies:
    +   Doctrine (with migrations)
    +   Redis
    +   GuzzleHTTP (for making HTTP requests)
*   Create a console command that will fetch the currency exchange rates for a given set of currencies from the Open Exchange Rates API. The command should have the following signature:

    `php bin/console app:exchange-rates:fetch [base_currency] [target_currency_1] [target_currency_2] ... [target_currency_n]`

    The command should make an HTTP request to the API to fetch the exchange rates for the given currencies, save the rates into a MySQL Database with the EUR base currency, and store the rates in Redis. This must be set as a cron job to be triggered daily at 1 am.

*   Create an endpoint that will return the exchange rates for a given set of currencies. The endpoint should have the following signature:
   
    `GET /api/exchange-rates?base_currency=[base_currency]&target_currencies=[target_currency_1,target_currency_2,...,target_currency_n]`
   
    The endpoint should first check Redis for the requested rates. If the rates are not in Redis, it should fetch them from the MySQL Database, store them in Redis, and return the rates. If the rates are in Redis, it should return the rates directly from Redis.

*   Write unit tests to verify the functionality of the console command and the endpoint.
*   Use doctrine migrations to manage database schema changes.
*   Push your changes to a GitHub account by creating a timeline of git commits.

Evaluation Criteria
-------------------

*   Code quality and organization
*   Correct use of Symfony components and best practices
*   Correct implementation of the API requirements
*   Unit tests that verify the functionality of the console command and the endpoint
*   Effective use of git and GitHub

* * *

Getting Started
---------------

### Prerequisites

Before getting started with the Symfony application, you need to have the following programs installed:

*   Docker: [Download here](https://www.docker.com/get-started)
*   docker-compose: [Installation instructions](https://docs.docker.com/compose/install/)

### Installation

1.  Clone the repository from GitHub:

    `git clone https://github.com/carlitosry/exchange-rates.git`

2.  Navigate to the application directory:

    `cd exchange-rates`

3.  Create a `.env` file from the `.env.example` file:

    `cp .env.example .env`

4. Build the container images and start the services:

    `docker-compose up -d`

5.  Get composer dependencies trough app container

    `docker-compose exec app composer install`

6.  You should execute the migrations to update the database schema

    `docker-compose exec app php bin/console doc:mig:mig`

7.  Open your web browser and go to `http://localhost:8200` to see the application is running.

### Stop the application

To stop the application containers, run the following command in the application directory:

`docker-compose down`


Usage
-----

This application is a RESTful API that provides real-time currency exchange rate information. It consumes a third-party API and stores the results in a **MySQL**  database and **Redis**  cache. The application runs in four Docker containers, including the database, web server, cache server, and a scheduled **cron**  container.

The Cron container executes a command automatically once a day at 1am to retrieve the latest currency exchange rates from the third-party API. These rates are then stored in both the MySQL database and Redis cache for fast and efficient access.

In addition, the API allows users to query currency exchange rates for a given set of currencies. The API is public and can be accessed by any client with appropriate authorization.

Please note that the cron is configured in the following file:
`docker/cron/crontab`

### Fetching Exchange Rates Command

To retrieve the latest exchange rates data from the third-party API, use the following command:

```shell
docker-compose exec app php bin/console app:exchange:rates [base_currency] [target_currency_1] [target_currency_2] ... [target_currency_n]
```

The first argument, `[base_currency]`, specifies the base currency for the exchange rates and is required. The following arguments, `[target_currency_1] ... [target_currency_n]`, are optional and specify the target currencies for which to retrieve exchange rates. If no target currencies are specified, the command retrieves all available exchange rates.

By default, the command is scheduled to run once a day at 1am using the `docker/cron/crontab` file. The retrieved exchange rates are stored in both the `MySQL` database and `Redis` cache for faster access.

You can modify the cron schedule or the command to retrieve different exchange rates.

For example, to retrieve the latest exchange rates for the Euro, run the following command:

```shell
docker-compose exec app php bin/console app:exchange:rates EUR
```

Please note that the application validates the currency arguments using the `src/Service/Validator/CurrencyValidator.php` service. This validation is also used in the public API endpoint to ensure that only valid currencies are accepted.

### Exchange Rates Endpoint
The application is a currency exchange rate API solution that provides users with up-to-date exchange rates between various currencies. This uses the Doctrine component for database management and GuzzleHTTP for making HTTP requests to a third-party API. The API endpoint can be accessed via the following URL:

```
http://localhost:8200/api/exchange-rates?base_currency=EUR&target_currencies=USD,COP,BTC
```

API Reference
-------------

### Endpoints

* **`/api/exchange-rates`**: Returns the exchange rates for a given base currency and target currencies.

### Requests
#### Exchange Rates Request
* **URL:** `/api/exchange-rates`
* **Method:** `GET`
* **URL Parameters:**

| Parameter | Required | Description |
| --- | --- | --- |
| base_currency | Yes | The base currency used for the exchange rates. |
| target_currencies | No  | A comma-separated list of target currencies for which to retrieve the exchange rates. If not specified, all available target currencies will be retrieved. |

### Responses
#### Exchange Rates Response

* **Status Code:** `200 OK`
* **Headers:**

| Header | Value |
| --- | --- |
| Content-Type | application/json |

* **Response Body:**
```json
{
"base_currency": "USD",
"target_currencies": {
    "EUR": 0.84664,
    "JPY": 105.34,
    "GBP": 0.76726
    }
}
```

* **Status Code:** `400 Bad Request`
* **Response Body:**
```json
{
  "message": "Invalid currency: %SOME DESCRIPTION%"
}
```

### Errors

If an error occurs during the API request, the response will contain an error message with a corresponding HTTP status code.

| HTTP Status Code | Error Message           | Description                                                |
|------------------|-------------------------|------------------------------------------------------------|
| 400              | "Bad Request"           | The request was malformed or missing required parameters\. |
| 404              | "Not Found"             | The requested resource was not found\.                     |
| 500              | "Internal Server Error" | An error occurred while processing the request\.           |

Testing
-------
To execute the testing about the functionality you should to run the following command:

```shell
docker-compose exec app php bin/phpunit --debug tests/
```
