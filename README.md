# Data Club - Project Structure

A comprehensive data science club management system for Moroccan universities.

## 📁 Project Structure

```
data-club/
├── 📄 data_club.sql                    # Database schema and initial data
├── 📄 README.md                        # Project documentation
│
├── 📁 user/                            # User-facing pages and functionality
│   ├── 📄 home.html                     # Landing page
│   ├── 📄 clubs.html                   # Static clubs listing page
│   ├── 📄 clubs.php  done                   # Dynamic clubs listing with database integration
│   ├── 📄 club.html  done                   # Static club detail page
│   ├── 📄 club.php                     # Dynamic club detail page with database integration
│   ├── 📄 events.html                  # Events listing page
│   ├── 📄 event.html                   # Event detail page
│   ├── 📄 contactus.html               # Contact us page
│   └── 📄 .gitkeep                     # Git placeholder file
│
├── 📁 auth/                            # Authentication system
│   ├── 📄 auth.html                    # Login/Signup interface
│   ├── 📄 login.php    done                   # User login functionality        done 
│   ├── 📄 signup.php   done                 # User registration functionality  done 
│   └── 📄 .gitkeep                     # Git placeholder file
│
├── 📁 static/                          # Static assets
│   ├── 📁 images/                      # Image assets
│   │   ├── 📄 mds logo.png             # Main application logo
│   │   ├── 📄 istockphoto.jpg          # Stock photo for UI
│   │   └── 📄 .gitkeep                 # Git placeholder file
│   ├── 📁 css/                         # Stylesheets (future use)
│   │   └── 📄 .gitkeep                 # Git placeholder file
│   └── 📁 js/                          # JavaScript files (future use)
│       └── 📄 .gitkeep                 # Git placeholder file
│
├── 📁 admin/                           # Admin panel (future development)
│   └── 📄 .gitkeep                     # Git placeholder file
│
├── 📁 club_owner/                      # Club owner dashboard (future development)
│   └── 📄 .gitkeep                     # Git placeholder file
│
├── 📁 includes/                        # PHP includes and utilities (future development)
│   └── 📄 .gitkeep                     # Git placeholder file
│
├── 📁 d_imgs/                          # Additional images (empty)
└── 📁 .git/                            # Git version control
```

## 🗄️ Database Structure

The project uses MySQL with the following main tables:

- **`admin`** - Administrator accounts
- **`member`** - Club member accounts
- **`club`** - Club information and details
- **`evenement`** - Event information
- **`topics`** - Club focus areas and topics
- **`speaker`** - Event speakers
- **`requestjoin`** - Club membership requests
- **`registre`** - Event registrations
- **`organizes`** - Club-event relationships
- **`focuses`** - Club-topic relationships
- **`contains`** - Event-topic relationships
- **`speaks`** - Speaker-event relationships

## 🚀 Features

### ✅ Implemented
- **Dynamic Club Listing** (`clubs.php`) - Fetches clubs from database with search and filtering
- **Club Detail Pages** (`club.php`) - Shows detailed club information, events, and members
- **User Authentication** - Login and signup system
- **Responsive Design** - Mobile-friendly interface using Tailwind CSS
- **Database Integration** - Full MySQL integration with prepared statements

### 🔄 In Development
- Admin panel for managing clubs and users
- Club owner dashboard for managing club events
- Event management system
- Member management features

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3 (Tailwind CSS), JavaScript
- **Backend**: PHP 8.0+
- **Database**: MySQL 10.4+
- **Server**: Apache (XAMPP)
- **Icons**: Font Awesome 6.0

## 📋 Setup Instructions

1. **Install XAMPP** and start Apache and MySQL services
2. **Import Database**: Run `data_club.sql` in phpMyAdmin
3. **Place Files**: Copy project files to `htdocs/data-club/`
4. **Configure Database**: Update connection details in PHP files if needed
5. **Access Application**: Navigate to `http://localhost/data-club/user/`

## 🔧 Configuration

### Database Connection
Update these values in PHP files if your setup differs:
```php
$host = "localhost";
$user = "root";
$pass = ""; // Your MySQL password
$db = "data_club";
```

## 📱 Pages Overview

### User Pages
- **Home** (`home.html`) - Landing page with overview
- **Clubs** (`clubs.php`) - Browse and search clubs
- **Club Details** (`club.php`) - Individual club profiles
- **Events** (`events.html`) - Event listings
- **Contact** (`contactus.html`) - Contact information

### Authentication
- **Login/Signup** (`auth.html`) - User authentication interface
- **Login Handler** (`login.php`) - Process user login
- **Signup Handler** (`signup.php`) - Process user registration

## 🎨 Design System

### Colors
- **Primary Red**: `#F05454`
- **Slate**: `#30475E`
- **Gray**: `#F5F5F5`
- **Dark**: `#121212`

### Typography
- Modern, clean design
- Responsive grid layouts
- Card-based UI components

## 🔒 Security Features

- **Prepared Statements** - SQL injection prevention
- **Input Validation** - Client and server-side validation
- **Password Hashing** - Secure password storage
- **Session Management** - User session handling

## 📈 Future Enhancements

- [ ] Admin dashboard for user management
- [ ] Club owner event creation tools
- [ ] Email notification system
- [ ] Advanced search and filtering
- [ ] Mobile app development
- [ ] API endpoints for external integrations

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is developed for educational purposes and club management.

---

**Last Updated**: January 2025
**Version**: 1.0.0
