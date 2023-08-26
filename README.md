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

    SELECT u.*, o.*
    FROM users u
    JOIN orders o ON o.user_id = u.id
    JOIN (
        SELECT user_id, MAX(total_amount) AS max_amount
        FROM orders
        GROUP BY user_id
    ) AS max_orders ON o.user_id = max_orders.user_id AND o.total_amount = max_orders.max_amount

1. First select all columns from both users and orders table.
2. Setup an alias for readability purposes.
3. The first join just connects the users and orders table based on the user_id column from orders and the id from users.
4. The second join uses an alias which is the max_amount and, by using the MAX() MySQL function we retrieve the highest value on the total_amount column from orders, we group them by user so we get unique values for each user.
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
    
    SELECT `users`.*
    FROM `users`
    INNER JOIN `orders` AS `max_sales_orders` ON `users`.`id` = `max_sales_orders`.`user_id`
    GROUP BY `users`.`id`
    HAVING SUM(max_sales_orders.total_amount) = (
        SELECT SUM(total_amount)
        FROM orders
        GROUP BY user_id
        ORDER BY SUM(total_amount) DESC
        LIMIT 1
    );
    
1. Select all columns from the users table.
2. Use an inner join to combine the users table with a subquery aliased as max_sales_orders using the user_id field.
3. Group the result by the users->id column.
4. Use the HAVING clause to filter the result based on the sum of total_amount from the max_sales_orders subquery, which calculates the highest total sales. The subquery in the HAVING clause finds the total sales of the user (or users) with the highest sales by summing the total_amount individually, then ordering the results in descending order and selecting the highest value with `LIMIT 1`.

```
## Troubleshooting

If you encounter any issues during deployment, please feel free to reach out to me for assistance.
