# Laravel Application Deployment Guide

<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
    </a>
</p>

<p align="center">
    <a href="https://github.com/laravel/framework/actions">
        <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
    </a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Deployment Guide

This guide will help you set up and deploy the Laravel application locally using Docker.

### Prerequisites

- Docker should be installed and running on your system. If not, you can install Docker by following the instructions [here](https://docs.docker.com/get-docker/).

## Deployment Steps

1. **Clone the Repository**

    ```bash
    git clone git@github.com:guillermovergara88/heytutor.git
    cd heytutor
    ```

2. **Run the Start Script**

    Execute the `start.sh` command in the root directory of the cloned repository. This script will:

    - Check if Docker is installed and running.
    - Start the application containers, including necessary migrations and seeders.

    ```bash
    ./start.sh
    ```

3. **Access the Endpoints**

    The Laravel application exposes the following endpoints:

    - GET /users/expensive-order
    - GET /users/highest-sales
    - GET /users/purchased-all-products

    You can use tools like `curl` or Postman to access these endpoints. Refer to the provided documentation for details on how these endpoints work.

4. **Environment Variables**

    To connect to the database, you need to set the following environment variables in the `.env` file en the project's root folder:

    ```makefile
    APP_NAME=Laravel
    APP_ENV=local
    APP_KEY=base64:1fqyHwdSLVmJtFOFegJAnU8xPuf8VZTnISA8eTKIfSU=
    APP_DEBUG=true
    APP_URL=http://localhost

    DB_CONNECTION=mysql
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=heytutor
    DB_USERNAME=heytutor
    DB_PASSWORD=secret
    ```

    Note that we're using port `33060` to connect locally to the database to avoid conflicts with other MySQL instances.

## Complex Queries explanation

```bash
    - Retrieve Users and Their Most Expensive Order:

    SELECT users.*, orders.*
    FROM users
    LEFT JOIN orders ON users.id = orders.user_id
    JOIN (
        SELECT 
            user_id, 
            MAX(total_amount) AS max_amount
        FROM 
            orders 
        GROUP BY 
            user_id
    ) AS max_orders ON orders.user_id = max_orders.user_id AND orders.total_amount = max_orders.max_amount;

1. First select all columns from both the users and orders tables.
2. The first join establishes a connection between the users and orders tables using the user_id column from orders and the id column from users.
3. "max_amount" alias is created for readability purposes on the total_amount column.
4. The second join uses the previously created alias to represent the subquery results. The MAX() function is utilized to determine the highest value in the total_amount column from the orders table. These values are grouped by user_id to ensure unique values for each user.
```
  
    
```bash
    - Retrieve Users Who Have Purchased All Products:

    SELECT u.*
    FROM users u
    JOIN orders o ON u.id = o.user_id
    GROUP BY u.id
    HAVING COUNT(DISTINCT o.product_id) = (SELECT COUNT(*) FROM products);
    
1. First select all columns from users table.
2. Then join users with orders based on the order->user_id field and user->id
3. Group them by users id to get unique results on the return.
4. HAVING clause to filter the results to users who have purchased all products, the count of DISTINCT should be equal to the total count of products to match the criteria.
```


```bash
    - Retrieve the User or Users (if they have the same total sales) with the Highest Total Sales:
    
    SELECT * FROM `users` WHERE `users`.`id` IN (
    SELECT `user_id`
    FROM (
        SELECT `user_id`, SUM(total_amount) AS total_sales
        FROM `orders`
        GROUP BY `user_id`
    ) AS `user_sales`
    WHERE `user_sales`.`total_sales` >= (
        SELECT SUM(total_amount) AS max_total_sales
        FROM `orders`
        GROUP BY `user_id`
        ORDER BY `max_total_sales` DESC
        LIMIT 1
    )
);

1. First select all columns from the users table.
2. The subquery calculates the total sales for each user by summing up the `total_amount` column from the `orders` table, grouping them by user_id.
3. The outer subquery `(SELECT user_id ...)` selects the user IDs where the total sales are greater than or equal to the maximum total sales achieved by any user.
4. The inner subquery `(SELECT SUM(total_amount) ...)` calculates the maximum total sales achieved by any user and orders the results in descending order.
5. The final result returns a list of users whose total sales are equal to or exceed the highest total sales achieved by any user.
```

## Multiple-Workers Approach

For implementing multiple workers will require involving a queue system such as Laravel's built-in queue functionality that utilizes tools like Redis. 

1. First we should configure the queue connectiong on config/queue.php
2. Then we should create the tasks we want to asynchronously process (Jobs).
3. Once dispatched, and we have the application running on `php artisan queue:work" depending on how many workers we want. In example, if we want two workers it should be: 

```bash
php artisan queue:work --queue=default --tries=3 & 
php artisan queue:work --queue=default --tries=3 &
```

Optionally, we could use Supervisor to monitor and restart workers if they fail or are terminated.

Source: [Laravel Documentation](https://laravel.com/docs/10.x/queues#running-the-queue-worker)
## Troubleshooting

If you encounter any issues during deployment, please feel free to reach out to me for assistance.
