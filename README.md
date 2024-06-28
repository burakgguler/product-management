# Product Management System

## Description

This case project is a product management system that allows users to create, list, and search for products. The system is built using the PHP Symfony framework and leverages MySQL for database management, Redis for caching, and Elasticsearch for search functionality. The application is fully containerized using Docker for easy setup and deployment.

## Technologies Used

- **Framework**: PHP Symfony
- **Database**: MySQL
- **Cache**: Redis
- **Search**: Elasticsearch
- **Containerization**: Docker

## Setup Instructions

Follow these steps to get the project up and running on your local machine.

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/product-management.git
cd product-management
```

### 2. Environment Configuration

Create a .env file in the root directory and configure the necessary environment variables. You can copy the .env.example file as a template.

```bash
cp .env.example .env
```

Ensure your .env file contains the following variables:

```
APP_ENV=dev
APP_SECRET=your_app_secret
DATABASE_URL=mysql://db_user:db_password@db:3306/product_db
REDIS_URL=redis://redis:6379
ELASTICSEARCH_URL=http://elasticsearch:9200
```

### 3. Docker Setup

Build and run the Docker containers.

```bash
docker-compose up -d --build
```

This command will start the following services:

PHP: Runs the Symfony application.

MySQL: Database service.

Redis: Cache service.

Elasticsearch: Search service.

### 4. Database Migration
Run the database migrations to set up the database schema.

```bash
docker-compose exec php bin/console doctrine:migrations:migrate
```

### 5. Accessing the Application
The application should now be running. You can access it in your browser at:

```
http://localhost:8000
```

## API Endpoints
### Create a Product
Endpoint: POST /api/products

Request Body:
```json
{
    "name": "Product Name",
    "category": "Product Category",
    "price": 100.0,
    "stock": 50
}
```
Response:
```json
{
    "id": 1,
    "name": "Product Name",
    "category": "Product Category",
    "price": 100.0,
    "stock": 50,
    "sku": "UNIQUE_SKU"
}
```

### List All Products
Endpoint: GET /api/products

Response:

```json
[
    {
        "id": 1,
        "name": "Product Name",
        "category": "Product Category",
        "price": 100.0,
        "stock": 50,
        "sku": "UNIQUE_SKU"
    },
    ...
]

```

### Get Product by ID

Endpoint: GET /api/products/{id}

Response:
```json
{
    "id": 1,
    "name": "Product Name",
    "category": "Product Category",
    "price": 100.0,
    "stock": 50,
    "sku": "UNIQUE_SKU"
}
```

### Search Products
Endpoint: GET /api/products/search?q={query}

Response:
```json
[
    {
        "id": 1,
        "name": "Product Name",
        "category": "Product Category",
        "price": 100.0,
        "stock": 50,
        "sku": "UNIQUE_SKU"
    },
    ...
]

```

## Running Tests
To run the tests, use the following command:
```bash
docker-compose exec php bin/phpunit
```

## Contact
If you have any problems with running the application, please contact me.

e-mail: burakkguler@outlook.com

mobile: +90 554 320 8843