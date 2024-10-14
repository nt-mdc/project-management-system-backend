![Project Management API](public/logo/banner.png)

This is an API built in Laravel for managing projects and tasks, with support for authentication, comments, user profiles, and password reset. Full API documentation was generated using Scribe.

## Features

- **User Authentication**: Register, login, and logout using tokens.
- **Project Management**: Create, update, view, and delete projects.
- **Task Management**: Associated with projects, with support for comments and priority settings.
- **Comments**: Comments can be associated with both projects and tasks.
- **Password Reset**: Password reset flow via email.
- **Profile Management**: Update user profile and upload profile photo.
- **API Documentation**: Documented using Scribe.

## Technologies Used

- **Laravel**: PHP framework used for building the API.
- **Laravel Sanctum**: For token-based authentication.
- **Scribe**: For automatic generation of API documentation.
- **MySQL**: Relational database used.
- **Puppeteer**: Used for web testing and automation.

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/nt-mdc/project-management-system-backend.git
   cd project-management-system-backend
   ```

2. Install project dependencies:
   ```bash
   composer install
   ```

3. Copy the `.env.example` file to `.env` and configure your environment variables, such as database connection:
   ```bash
   cp .env.example .env
   ```

4. Generate the application key:
   ```bash
   php artisan key:generate
   ```

5. Run the database migrations:
   ```bash
   php artisan migrate
   ```

6. Start the development server:
   ```bash
   php artisan serve
   ```

## Authentication

The API uses Laravel Sanctum for authentication. Users must first register and log in to obtain an authentication token.

- **Register**: `POST /api/register`
- **Login**: `POST /api/login`
- **Logout**: `POST /api/logout` (requires token)

## API Documentation

Full API documentation is available and was generated using Scribe.

To access the documentation, visit: http://your-app-url/docs

## Usage

You can use tools like Postman or curl to test the API endpoints. Ensure to include the authentication token in the header for protected routes.

## Running Tests

The project uses PHPUnit for automated tests. To run the tests:
   ```bash
   php artisan test
   ```

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.
