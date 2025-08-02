# Training Platform Backend

A robust Laravel-based REST API backend for managing training courses, students, and training schedules. This platform provides comprehensive functionality for training management with authentication, course management, student enrollment, and training schedule coordination.

## 🚀 Features

- **Authentication & Authorization**: Secure user registration, login, and logout using Laravel Sanctum
- **Course Management**: Create, read, update, and delete training courses
- **Student Management**: Manage student profiles and enrollments
- **Training Schedules**: Schedule and manage training sessions with location tracking
- **Opt-in/Opt-out System**: Students can opt in or out of training sessions
- **RESTful API**: Clean, well-structured API endpoints
- **Database Relationships**: Properly structured relationships between courses, students, and schedules
- **Testing**: Comprehensive test suite for all features
- **Docker Support**: Containerized deployment ready
- **Cloud Deployment**: Configured for Render.com deployment

## 🛠️ Tech Stack

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: MySQL/PostgreSQL (configurable)
- **Authentication**: Laravel Sanctum
- **Testing**: PHPUnit
- **Containerization**: Docker
- **Deployment**: Render.com

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL database
- Node.js (for frontend assets if needed)
- Docker (optional, for containerized deployment)

## 🚀 Installation

### Local Development

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd training-platform-backend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Edit `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=training_platform
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

### Docker Deployment

1. **Build and run with Docker**
   ```bash
   docker build -t training-platform-backend .
   docker run -p 8080:80 training-platform-backend
   ```

2. **Using Docker Compose** (if available)
   ```bash
   docker-compose up -d
   ```

## 📚 API Documentation

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register a new user |
| POST | `/api/login` | Login user |
| POST | `/api/logout` | Logout user (requires auth) |
| GET | `/api/me` | Get current user info (requires auth) |

### Course Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/courses` | List all courses |
| POST | `/api/courses` | Create a new course |
| GET | `/api/courses/{id}` | Get specific course |
| PUT | `/api/courses/{id}` | Update course |
| DELETE | `/api/courses/{id}` | Delete course |

### Student Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/students` | List all students |
| POST | `/api/students` | Create a new student |
| GET | `/api/students/{id}` | Get specific student |
| PUT | `/api/students/{id}` | Update student |
| DELETE | `/api/students/{id}` | Delete student |
| GET | `/api/students/user/{userId}` | Get student by user ID |
| POST | `/api/students/user/{userId}` | Create student for user |

### Training Schedules

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/training-schedules` | List all training schedules |
| POST | `/api/training-schedules` | Create a new training schedule |
| GET | `/api/training-schedules/{id}` | Get specific training schedule |
| PUT | `/api/training-schedules/{id}` | Update training schedule |
| DELETE | `/api/training-schedules/{id}` | Delete training schedule |

### Training Opt-in/Opt-out

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/training-opt-in-out` | Opt in or out of training |
| GET | `/api/student-training-statuses` | Get training status list |

## 🗄️ Database Schema

### Core Tables

- **users**: User authentication and profiles
- **courses**: Training course information
- **students**: Student profiles and information
- **training_schedules**: Scheduled training sessions
- **student_training**: Pivot table for student-schedule relationships

### Key Relationships

- Courses have many Training Schedules
- Students belong to many Training Schedules (many-to-many)
- Students belong to Users (one-to-one)
- Training Schedules belong to Courses

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with coverage (if configured)
php artisan test --coverage
```

### Test Categories

- **Authentication Tests**: User registration, login, logout
- **Course Tests**: CRUD operations for courses
- **Student Tests**: Student management functionality
- **Training Schedule Tests**: Schedule management
- **Training Opt Tests**: Opt-in/opt-out functionality

## 🚀 Deployment

### Render.com Deployment

The project is configured for deployment on Render.com with the provided `render.yaml` file.

1. Connect your repository to Render
2. Render will automatically detect the Dockerfile
3. The service will be deployed with automatic migrations

### Manual Deployment

1. **Production environment setup**
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Database migration**
   ```bash
   php artisan migrate --force
   ```

3. **Set up web server** (Apache/Nginx) pointing to the `public` directory

## 🔧 Configuration

### Environment Variables

Key environment variables to configure:

```env
APP_NAME="Training Platform"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=training_platform
DB_USERNAME=your-username
DB_PASSWORD=your-password

SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com
SESSION_DOMAIN=your-domain.com
```

## 📁 Project Structure

```
training-platform-backend/
├── app/
│   ├── Http/Controllers/Api/     # API Controllers
│   ├── Models/                   # Eloquent Models
│   └── Providers/                # Service Providers
├── database/
│   ├── factories/                # Model Factories
│   ├── migrations/               # Database Migrations
│   └── seeders/                  # Database Seeders
├── routes/
│   └── api.php                   # API Routes
├── tests/                        # Test Files
├── Dockerfile                    # Docker Configuration
├── render.yaml                   # Render Deployment Config
└── composer.json                 # PHP Dependencies
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:

- Create an issue in the repository
- Contact the development team
- Check the Laravel documentation for framework-specific questions

## 🔄 Version History

- **v1.0.0**: Initial release with core training platform functionality
- Authentication system with Laravel Sanctum
- Course and student management
- Training schedule system
- Opt-in/opt-out functionality
- Comprehensive test suite
- Docker and Render deployment support

---

**Built with ❤️ using Laravel** 
