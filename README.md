# Nizek Task

[**TODO:** Add a brief 1-2 sentence description of what this project does here.]

This guide provides instructions on how to set up, run, and manage the Nizek Task application using Docker.

## Prerequisites

Before you begin, ensure you have the following installed on your system:

* **Git:** For cloning the repository.
* **Docker:** For containerization. ([Install Docker](https://docs.docker.com/get-docker/))
* **Docker Compose:** For managing multi-container Docker applications. (Usually included with Docker Desktop, otherwise [Install Docker Compose](https://docs.docker.com/compose/install/))

## Getting Started

Follow these steps to get the application running locally.

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/starfall1991/nizek-task.git
    cd nizek-task
    ```

2.  **Configure Environment Variables:**
    Copy the example environment file. You **must** review and potentially edit the `.env` file to set necessary configuration variables (like database credentials, API keys, ports, etc.) specific to your environment.
    ```bash
    cp .env.example .env
    ```
    *Edit `.env` with your preferred text editor.*

3.  **Prepare Database File:**
    This setup requires the SQLite database file to exist before starting the containers. Create the empty file:
    ```bash
    touch database/database.sqlite
    ```

4.  **Build and Start Containers:**
    This command will build the Docker images (if they don't exist or if changes were made) and start the services defined in `docker-compose.yml` in detached mode (running in the background).
    ```bash
    docker-compose up -d --build
    ```

5.  **Wait for Services:**
    Allow some time for the containers to start and become healthy. The application might need a moment to initialize fully after the container starts. You can check the status using:
    ```bash
    docker-compose ps
    ```
    Or view the logs (press `Ctrl+C` to stop viewing):
    ```bash
    docker-compose logs -f
    ```

## Accessing the Application

Once the containers are running and healthy, you should be able to access the application via your web browser at:

`http://localhost:9000`

## Managing the Application

### Stopping the Application
To stop the running containers:
```bash
docker-compose down
