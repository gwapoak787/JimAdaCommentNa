# Scholarship Finder - Documentation

## Project Overview
A web application for students to discover and manage scholarships. Features user authentication, scholarship browsing, filtering, sorting, and bookmarking capabilities.

## Project Structure

### Directory Organization
```
/
├── config/           # Configuration files
├── src/             # Source code (organized by type)
│   ├── controllers/ # Request handlers
│   ├── models/      # Data models
│   ├── services/    # Business logic
│   ├── utils/       # Utility classes
│   └── views/       # Templates (future)
├── public/          # Public web assets
│   ├── css/         # Stylesheets
│   ├── js/          # JavaScript files
│   └── images/      # Image assets
├── docs/            # Documentation
└── tests/           # Test files
```

### Key Components

#### Configuration (`config/`)
- `config.php` - Main application configuration
- `database.php` - Database connection and query methods
- `security.php` - Security functions and session management

#### Services (`src/services/`)
- `AuthService.php` - User authentication and session management
- `ScholarshipService.php` - Scholarship CRUD operations and business logic

#### Models (`src/models/`)
- `ScholarshipTree.php` - Hierarchical organization of scholarships

#### Utilities (`src/utils/`)
- `ScholarshipSorter.php` - Sorting algorithms for scholarships

#### Controllers (`src/controllers/`)
- `ApiController.php` - Handles AJAX API requests

## Getting Started

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Web server (Apache/Nginx)

### Installation
1. Clone the repository
2. Configure database settings in `config/config.php`
3. Run database setup script
4. Access the application through your web server

### Configuration
Edit `config/config.php` to set:
- Database credentials
- Application settings
- Security parameters

## API Endpoints

### Scholarship Operations
- `GET /includes/api.php?action=get_all` - Get all scholarships
- `GET /includes/api.php?action=search&level=X&field=Y` - Search/filter scholarships
- `GET /includes/api.php?action=sort_deadline` - Sort by deadline
- `GET /includes/api.php?action=sort_amount` - Sort by amount

### User Operations
- `POST /includes/api.php?action=add_bookmark` - Add bookmark
- `POST /includes/api.php?action=remove_bookmark` - Remove bookmark
- `GET /includes/api.php?action=get_bookmarks` - Get user bookmarks

## Security Features
- Password hashing with bcrypt
- CSRF protection
- Input sanitization
- Rate limiting
- Secure session management

## Development Guidelines

### Code Organization
- Use service classes for business logic
- Keep controllers thin and focused on request handling
- Separate data models from business logic
- Use dependency injection where possible

### Security
- Always validate and sanitize user input
- Use prepared statements for database queries
- Implement proper error handling
- Follow principle of least privilege

### Performance
- Use database indexing for frequently queried fields
- Implement caching for expensive operations
- Optimize database queries
- Minify static assets

## Testing
Run tests from the `tests/` directory:
```bash
php tests/run.php
```

## Contributing
1. Follow the established code structure
2. Add appropriate documentation
3. Write tests for new features
4. Follow security best practices
5. Submit pull requests for review

## License
This project is licensed under the MIT License.