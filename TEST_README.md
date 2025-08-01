# Training Platform Test Suite

This document provides an overview of the comprehensive test suite for the Laravel Training Platform API.

## Test Structure

### Feature Tests
Located in `tests/Feature/`

#### 1. AuthTest.php
Tests authentication functionality including:
- User registration with validation
- User login with credential verification
- Logout functionality
- User profile retrieval
- Token management
- Role-based registration

**Key Test Cases:**
- Registration with valid/invalid data
- Login with correct/incorrect credentials
- Token validation and invalidation
- Role validation (admin/student)
- Email uniqueness validation
- Password confirmation validation

#### 2. CourseTest.php
Tests course management functionality including:
- CRUD operations for courses
- Validation of course data
- Error handling for non-existent resources
- Partial updates
- Database integrity

**Key Test Cases:**
- Create, read, update, delete courses
- Validation of required fields (name, duration)
- Validation of data types and constraints
- 404 handling for non-existent courses
- Partial updates with validation

#### 3. StudentTest.php
Tests student management functionality including:
- Student creation with user account
- Student-user relationship management
- Student profile updates
- User filtering and retrieval
- Duplicate prevention

**Key Test Cases:**
- Create student with associated user account
- Update student and user information
- Filter students by user ID
- Create student for existing user
- Prevent duplicate student records
- Validation of student data

#### 4. TrainingScheduleTest.php
Tests training schedule management including:
- Schedule creation with course relationships
- Date validation and logic
- Location management
- Course relationship validation

**Key Test Cases:**
- Create schedules with valid date ranges
- Validate start date before end date
- Handle null locations
- Update schedule information
- Validate course existence

#### 5. TrainingOptTest.php
Tests training opt-in/opt-out functionality including:
- Student training status management
- Status updates and creation
- Status listing and filtering
- Relationship integrity

**Key Test Cases:**
- Opt-in and opt-out functionality
- Update existing status records
- List training statuses by student
- Validate student and schedule existence
- Handle multiple statuses per student

### Unit Tests
Located in `tests/Unit/`

#### 1. CourseTest.php
Tests Course model functionality including:
- Model creation and updates
- Relationship testing
- Fillable fields validation
- Database operations

**Key Test Cases:**
- Course model creation
- Training schedules relationship
- Model updates and deletions
- Null value handling
- Timestamp functionality

## Test Factories

### CourseFactory.php
Generates test data for courses with:
- Random course names
- Random descriptions
- Duration between 1-90 days

### StudentFactory.php
Generates test data for students with:
- Associated user creation
- Random first and last names
- Unique email addresses
- Phone numbers

### TrainingScheduleFactory.php
Generates test data for training schedules with:
- Associated course creation
- Realistic date ranges
- Location names

## Running Tests

### Prerequisites
1. Ensure your database is configured for testing
2. Run migrations: `php artisan migrate`
3. Ensure all dependencies are installed: `composer install`

### Running All Tests
```bash
php artisan test
```

### Running Specific Test Suites
```bash
# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit
```

### Running Specific Test Classes
```bash
# Run only authentication tests
php artisan test tests/Feature/AuthTest.php

# Run only course tests
php artisan test tests/Feature/CourseTest.php
```

### Running Specific Test Methods
```bash
# Run a specific test method
php artisan test --filter=it_can_register_a_new_user
```

### Using the Batch Script (Windows)
```bash
run_tests.bat
```

## Test Configuration

### Database
Tests use the `RefreshDatabase` trait to ensure a clean database state for each test.

### Authentication
Feature tests use Laravel Sanctum for authentication testing with:
- Token-based authentication
- User creation and authentication
- Token validation and invalidation

### Test Data
Tests use factories to generate realistic test data:
- Faker library for random data generation
- Proper relationships between models
- Valid data that passes validation

## Test Coverage

### API Endpoints Covered
- `POST /api/register` - User registration
- `POST /api/login` - User authentication
- `POST /api/logout` - User logout
- `GET /api/me` - Current user profile
- `GET /api/courses` - List courses
- `POST /api/courses` - Create course
- `GET /api/courses/{id}` - Show course
- `PUT /api/courses/{id}` - Update course
- `DELETE /api/courses/{id}` - Delete course
- `GET /api/students` - List students
- `POST /api/students` - Create student
- `GET /api/students/{id}` - Show student
- `PUT /api/students/{id}` - Update student
- `DELETE /api/students/{id}` - Delete student
- `GET /api/students/user/{userId}` - Get student by user ID
- `POST /api/students/user/{userId}` - Create student for user
- `GET /api/training-schedules` - List training schedules
- `POST /api/training-schedules` - Create training schedule
- `GET /api/training-schedules/{id}` - Show training schedule
- `PUT /api/training-schedules/{id}` - Update training schedule
- `DELETE /api/training-schedules/{id}` - Delete training schedule
- `POST /api/training-opt-in-out` - Opt in/out of training
- `GET /api/student-training-statuses` - Get training statuses

### Validation Scenarios Covered
- Required field validation
- Data type validation
- Length constraints
- Unique constraints
- Relationship validation
- Date logic validation
- Email format validation
- Password confirmation
- Role validation

### Error Scenarios Covered
- 404 Not Found responses
- 422 Validation errors
- 401 Unauthorized responses
- 409 Conflict responses
- 500 Internal server errors

## Best Practices Implemented

1. **Test Isolation**: Each test is independent and doesn't rely on other tests
2. **Database Cleanup**: Fresh database state for each test
3. **Realistic Data**: Using factories with faker for realistic test data
4. **Comprehensive Coverage**: Testing both success and failure scenarios
5. **Clear Naming**: Descriptive test method names
6. **Proper Assertions**: Using appropriate assertion methods
7. **Authentication Testing**: Proper token-based authentication testing
8. **Relationship Testing**: Testing model relationships and constraints

## Troubleshooting

### Common Issues

1. **Database Connection**: Ensure your testing database is properly configured
2. **Migration Issues**: Run `php artisan migrate:fresh --seed` if needed
3. **Factory Issues**: Ensure all factories are properly defined
4. **Authentication Issues**: Check Sanctum configuration

### Debugging Tests
```bash
# Run tests with verbose output
php artisan test -v

# Run tests with stop on failure
php artisan test --stop-on-failure

# Run tests with coverage (if configured)
php artisan test --coverage
```

## Adding New Tests

When adding new functionality:

1. Create feature tests for API endpoints
2. Create unit tests for model functionality
3. Add appropriate factories for test data
4. Test both success and failure scenarios
5. Include validation testing
6. Test authentication requirements
7. Update this README with new test information

## Test Statistics

- **Total Test Files**: 6
- **Feature Tests**: 5 files
- **Unit Tests**: 1 file
- **Total Test Methods**: 100+ individual test cases
- **Coverage**: All major API endpoints and model functionality 