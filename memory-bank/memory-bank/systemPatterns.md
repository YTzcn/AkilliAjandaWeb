## System Patterns (systemPatterns.md)

This document details the system patterns employed in the Akıllı Ajanda Web Projesi, outlining the architectural design, data models, API definitions, component structure, and integration points.

## Architectural Design: Layered MVC with Repository Service Pattern

The application follows a layered MVC (Model-View-Controller) architecture enhanced with the Repository and Service patterns. This design promotes separation of concerns, testability, and maintainability.

*   **Presentation Layer (View):** Implemented using Blade templates and Bootstrap for a responsive and visually appealing user interface.  Handles user interactions and displays data.
*   **Controller Layer:** Receives user requests from the View, orchestrates the business logic through the Service Layer, and determines the appropriate View to render.
*   **Service Layer:**  Contains the application's business logic. It acts as an intermediary between the Controller and the Repository Layer.  This layer provides a clear separation of concerns and allows for easier testing and modification of business rules.
*   **Repository Layer:** Provides an abstraction over the data access layer. It interacts directly with the database (PostgreSQL) using Eloquent ORM.  This decouples the business logic from the specific database implementation, making it easier to switch databases if needed.
*   **Model Layer:** Represents the data structure and defines relationships between different data entities. Eloquent models are used to interact with the database tables.

## Data Models

The application will primarily revolve around the following data models, implemented as Eloquent models:

*   **User:** (users table)
    *   `id`: INT (Primary Key, Auto-increment)
    *   `name`: VARCHAR (User's full name)
    *   `email`: VARCHAR (Unique, User's email address)
    *   `password`: VARCHAR (Hashed password)
    *   `email_verified_at`: TIMESTAMP (Nullable, Timestamp of email verification)
    *   `remember_token`: VARCHAR (Nullable, Token for "remember me" functionality)
    *   `created_at`: TIMESTAMP
    *   `updated_at`: TIMESTAMP
*   **Event:** (events table)
    *   `id`: INT (Primary Key, Auto-increment)
    *   `user_id`: INT (Foreign Key, References users.id, User who created the event)
    *   `title`: VARCHAR (Event title)
    *   `description`: TEXT (Event description)
    *   `start_time`: DATETIME (Event start time)
    *   `end_time`: DATETIME (Event end time)
    *   `location`: VARCHAR (Nullable, Event location)
    *   `created_at`: TIMESTAMP
    *   `updated_at`: TIMESTAMP
*   **Task:** (tasks table)
    *   `id`: INT (Primary Key, Auto-increment)
    *   `user_id`: INT (Foreign Key, References users.id, User who created the task)
    *   `title`: VARCHAR (Task title)
    *   `description`: TEXT (Task description)
    *   `due_date`: DATE (Task due date)
    *   `is_completed`: BOOLEAN (Flag indicating task completion)
    *   `created_at`: TIMESTAMP
    *   `updated_at`: TIMESTAMP
*   **Note:** (notes table)
    *   `id`: INT (Primary Key, Auto-increment)
    *   `user_id`: INT (Foreign Key, References users.id, User who created the note)
    *   `title`: VARCHAR (Note title)
    *   `content`: TEXT (Note content)
    *   `created_at`: TIMESTAMP
    *   `updated_at`: TIMESTAMP

**Relationships:**

*   A `User` can have many `Events`, `Tasks`, and `Notes`.

## API Definitions

While a full-fledged API is not the primary focus, certain functionalities can be exposed as API endpoints for future expansion or integration with other services. These APIs will follow RESTful principles and return JSON responses.  Example endpoints include:

*   **`GET /api/events`:**  Retrieve all events for the authenticated user.
    *   **Request:** `Authorization: Bearer {token}`
    *   **Response:** `JSON` array of `Event` objects.
*   **`POST /api/events`:** Create a new event for the authenticated user.
    *   **Request:** `Authorization: Bearer {token}`, `JSON` body with event details (`title`, `description`, `start_time`, `end_time`, `location`).
    *   **Response:** `JSON` representation of the created `Event` object or error message.
*   **`GET /api/tasks`:** Retrieve all tasks for the authenticated user.
    *   **Request:** `Authorization: Bearer {token}`
    *   **Response:** `JSON` array of `Task` objects.
*   **`POST /api/tasks`:** Create a new task for the authenticated user.
    *   **Request:** `Authorization: Bearer {token}`, `JSON` body with task details (`title`, `description`, `due_date`).
    *   **Response:** `JSON` representation of the created `Task` object or error message.

Authentication for these APIs will be handled using Laravel Sanctum or Passport.

## Component Structure

The application's component structure is organized based on the MVC pattern and further modularized within each layer:

*   **`app/Http/Controllers`:** Contains controllers responsible for handling user requests and coordinating the interaction between the Service and View layers.  Examples: `EventController`, `TaskController`, `NoteController`, `AuthController`.
*   **`app/Services`:** Contains service classes that implement the application's business logic.  Examples: `EventService`, `TaskService`, `NoteService`.
*   **`app/Repositories`:** Contains repository classes that abstract the data access layer. Examples: `EventRepository`, `TaskRepository`, `NoteRepository`, `UserRepository`.
*   **`app/Models`:** Contains Eloquent models representing the data entities. Examples: `User`, `Event`, `Task`, `Note`.
*   **`resources/views`:** Contains Blade templates for rendering the user interface.  Organized into directories reflecting the application's features (e.g., `events`, `tasks`, `notes`).
*   **`resources/js`:** Contains Vanilla JavaScript code for enhancing the user interface and handling client-side interactions.  Organized into modules for better maintainability.
*   **`routes/web.php`:** Defines the routes for the web application.
*   **`routes/api.php`:** Defines the routes for the API endpoints.

## Integration Points

The application integrates with the following external components:

*   **PostgreSQL Database:** Stores all persistent data.
*   **Email Service (Optional):** For sending email notifications (e.g., password reset). Can be integrated using Laravel's built-in Mail facade and configured with services like Mailgun, SendGrid, or SMTP.
*   **Third-Party Libraries:**  Leverages various third-party libraries through Composer for functionalities like date/time manipulation, form validation, and more.

Created on 24.04.2025
```