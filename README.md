# Property Rental Management System

A complete property rental management system built with PHP and MySQL, featuring multi-role dashboards, advanced search and filtering, booking management, and automated payment tracking.

## Features

### Multi-Role System
- **Admin Dashboard**: Complete platform management with revenue tracking and commission monitoring
- **Owner Dashboard**: Property management, booking oversight, and rental income tracking
- **User Dashboard**: Booking management, payment history, and automatic rent bill generation

### Core Functionality
- **Property Listings**: Advanced search and filtering by location, type, BHK, price range
- **Booking System**: Complete booking workflow from request to confirmation
- **Payment Management**: Automated rent tracking with commission calculations
- **Activity Monitoring**: Real-time activity feeds across all user roles

### Advanced Features
- **Automatic Rent Bill Generation**: Monthly bill generation for tenants
- **Commission Tracking**: Automated commission calculations for platform revenue
- **Responsive Design**: Mobile-first design with modern UI/UX
- **Security**: Role-based access control and secure authentication

## Installation

1. **Database Setup**
   ```bash
   # Import the database schema
   mysql -u root -p < database/schema.sql
   ```

2. **Configuration**
   - Update database credentials in `config/database.php`
   - Ensure proper file permissions

3. **Web Server**
   - Place files in your web server directory
   - Ensure PHP 7.4+ and MySQL 5.7+ are installed

## Default Login Credentials

- **Admin**: admin@renthub.com / password
- **Owner**: raj@example.com / password  
- **User**: amit@example.com / password

## File Structure

```
├── config/
│   ├── database.php          # Database connection
│   └── session.php           # Session management
├── includes/
│   ├── header.php            # Common header
│   └── footer.php            # Common footer
├── database/
│   └── schema.sql            # Database schema
├── index.php                 # Homepage
├── login.php                 # Login page
├── register.php              # Registration page
├── properties.php            # Property listings with filters
├── property_details.php      # Property details and booking
├── add_property.php          # Add new property
├── admin_dashboard.php       # Admin dashboard
├── owner_dashboard.php       # Owner dashboard
├── user_dashboard.php        # User dashboard
└── logout.php                # Logout handler
```

## Key Features Breakdown

### Dashboard Features
- **Overview Tab**: Statistics, recent activity, quick actions
- **Bookings Tab**: Complete booking management with status tracking
- **Revenue Tab**: Financial tracking with commission breakdowns
- **Activity Tab**: Real-time platform activity monitoring

### Property Management
- Advanced filtering system (location, type, BHK, price, sorting)
- Image gallery support
- Amenities management
- Availability tracking

### Payment System
- Monthly rent tracking
- Commission calculations (5% platform fee)
- Payment status monitoring
- Automatic bill generation

### Security & Access Control
- Role-based authentication
- Session management
- Input validation and sanitization
- SQL injection prevention

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3 (Tailwind CSS), JavaScript
- **Icons**: Font Awesome
- **Responsive**: Mobile-first design

## Browser Support

- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License.