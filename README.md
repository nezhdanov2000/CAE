# Learning Time Slot Management System

A web application for managing learning time slots, allowing tutors to create slots for sessions and students to enroll in them.

## Features

### For Tutors:
- View all available time slots
- Create new time slots
- Manage personal time slots
- View enrolled students
- Cancel or modify slots

### For Students:
- View available time slots
- Enroll in time slots
- View personal enrollments
- Cancel enrollment
- View tutor information

## Technologies

- PHP
- MySQL
- HTML/CSS/JavaScript
- XAMPP (for local development)
- Font Awesome (for icons)
- Google Fonts (Roboto)

## Installation

1. Clone the repository:
```bash
git clone [repository URL]
```

2. Set up the database:
- Create a MySQL database
- Import the database structure from `cae_structure.sql`
- Create `db.php` with database connection settings

3. Configure the web server:
- Place files in the web server directory (e.g., htdocs for XAMPP)
- Ensure PHP and MySQL are running

4. Open the application in your browser:
```
http://localhost/Project2
```

## Project Structure

- `frontend/` - Frontend files
  - `*.html` - HTML interface files
  - `style.css` - Global styles
  - `img/` - Images and icons
- `*.php` - PHP files for request handling
- `cae_structure.sql` - SQL file with database structure
- `db.php` - Database connection configuration

## Security

- All database queries use prepared statements
- User authentication and role verification
- SQL injection protection
- XSS protection through input sanitization
- CSRF protection for forms
- Secure password hashing
- Session management and timeout

## Development

The project uses Git for version control with the following branches:
- `main` - Production branch
- `development` - Development branch

## Contributing

1. Create a new branch for your feature
2. Make your changes
3. Submit a pull request to the development branch

## License

This project is licensed under the MIT License - see the LICENSE file for details. 