Gbowy Backend Server
This repository contains the backend server for the Gbowy platform, built using Laravel, a popular PHP framework. It handles user management, API integrations, transactions, and various other services required by the Gbowy platform.
Table of Contents

1. Prerequisites
2. Installation
3. Configuration
4. Running the Development Server
5. Testing
6. Deployment
7. API Documentation
   Prerequisites
   Ensure you have the following installed on your local machine:
   • PHP 8.2 or higher
   • Composer (for managing PHP dependencies)
   • MySQL (or a compatible database)
   • Laravel 10.x (this project is built using Laravel)
   • Node.js (for managing frontend assets, if applicable)
   Installing PHP, Composer, MySQL, and Laravel
   • Install PHP.
   • Install Composer.
   • Install MySQL or use a database like MariaDB.
   • Install Laravel via Composer:
   bash
   Copy code
   composer global require laravel/installer
   Installation
8. Clone the Repository
   Clone the repository to your local machine:
   bash
   Copy code
   git clone https://github.com/kaykayreal/gbowy-backend.git
9. Install PHP Dependencies
   Navigate to the project directory and install the required dependencies using Composer:
   bash
   Copy code
   cd gbowy-backend
   composer install
10. Install Frontend Dependencies (Optional)
    If the backend has any assets, you may also need to install the frontend dependencies:
    bash
    Copy code
    npm install
    OR
    bash
    Copy code
    yarn install
    Configuration
11. Create Environment File
    In the project root directory, create a .env file by copying the .env.example file:
    bash
    Copy code
    cp .env.example .env
12. Set Database Credentials
    Open the .env file and configure your database settings. For example:
    makefile
    Copy code
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=gbowy_database
    DB_USERNAME=root
    DB_PASSWORD=
13. Generate Application Key
    Laravel requires an application key, which you can generate by running:
    bash
    Copy code
    php artisan key:generate
14. Set Up Other Configurations
    You may also need to configure other services like Mail, SMS, Stripe, Paystack, or other integrations by modifying the respective values in the .env file.
    Running the Development Server
15. Start the Laravel Development Server
    Run the following command to start the development server:
    bash
    Copy code
    php artisan serve
    By default, the server will be accessible at http://localhost:8000.
16. Frontend Assets (Optional)
    If you have frontend assets that need to be compiled (e.g., for Vue.js or React), run the following command:
    bash
    Copy code
    npm run dev
    OR
    bash
    Copy code
    yarn dev
    Testing
    You can run the tests to ensure everything is working correctly.
17. Run PHPUnit Tests
    To run unit tests:
    bash
    Copy code
    php artisan test
18. Run Dusk Tests (for browser automation testing)
    bash
    Copy code
    php artisan dusk
    Deployment
19. Prepare for Deployment
    Ensure that you have a production environment .env file, configure the appropriate database, mail, and API keys for your production setup.
20. Deploying to Server
    You can deploy to a server using tools like Forge, Envoyer, Git, or manually. To deploy manually:
    • Pull the latest changes:
    bash
    Copy code
    git pull origin main
    • Install dependencies:
    bash
    Copy code
    composer install --optimize-autoloader --no-dev
    npm install --production
    • Run migrations:
    bash
    Copy code
    php artisan migrate --force
    • Clear cache and config:
    bash
    Copy code
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    API Documentation
    You can document the API routes by either using tools like Swagger or Postman. Below are some of the essential API routes (this section should be customized based on your routes):
    • GET /api/users - Retrieve a list of users.
    • POST /api/users - Create a new user.
    • PUT /api/users/{id} - Update user details.
    • DELETE /api/users/{id} - Delete a user.
