# Data Club Project
**Location:** C:\xampp\htdocs\data-club

## Current Project Structure

### 1. public/
**Purpose:** Contains all files accessible directly by the browser (frontend).

#### 1.1 auth/
**Purpose:** Handles user authentication pages.
**Current Files:**
- `login.php` - User login form and processing (✅ Implemented)
- `.gitkeep` - Placeholder file

**Planned Files:**
- `signup.php` - User registration form and processing
- `logout.php` - Script to log out user and destroy session

#### 1.2 user/
**Purpose:** Pages and features accessible to normal members.
**Current Files:**
- `.gitkeep` - Placeholder file

**Planned Files:**
- `index.php` - User homepage/dashboard
- `profile.php` - View/edit user personal profile
- `events.php` - List upcoming events
- `club_list.php` - Browse available clubs

#### 1.3 admin/
**Purpose:** Admin panel pages to manage site data.
**Current Files:**
- `.gitkeep` - Placeholder file

**Planned Files:**
- `index.php` - Admin dashboard overview
- `manage_clubs.php` - Approve/reject club creation requests
- `manage_events.php` - Add/edit/delete events
- `manage_users.php` - Manage user accounts and roles

#### 1.4 club_owner/
**Purpose:** Pages for club owners/managers.
**Current Files:**
- `.gitkeep` - Placeholder file

**Planned Files:**
- `index.php` - Club owner dashboard
- `club_profile.php` - Edit club info, description, logo
- `create_event.php` - Create new events for the club
- `manage_members.php` - Approve/reject club membership requests

#### 1.5 static/
**Purpose:** Static assets (CSS, JavaScript, images).
**Current Structure:**
- `css/` - CSS files (currently empty)
- `js/` - JavaScript files (currently empty)
- `images/` - Club logos, event banners, user avatars (currently empty)

#### 1.6 includes/
**Purpose:** Reusable page fragments.
**Current Files:**
- `.gitkeep` - Placeholder file

**Planned Files:**
- `header.php` - HTML head, opening `<body>`, nav bar
- `footer.php` - Footer content and closing tags
- `navbar.php` - Navigation menu
- `auth_check.php` - PHP script to check user login & roles
- `database.php` - Database connection configuration

## Current Implementation Status

### ✅ Completed
- Basic project structure with placeholder directories
- Login system (`public/auth/login.php`) with:
  - Admin authentication (privilege level 1)
  - Member authentication (privilege level 2)
  - Session management
  - Basic form validation

### 🔄 In Progress
- Database connection setup (currently embedded in login.php)

### 📋 Planned Features

#### Phase 1: Core Authentication & Structure
1. **Database Configuration**
   - Move database config to `public/includes/database.php`
   - Implement proper connection handling

2. **Authentication System**
   - Complete signup functionality
   - Implement logout system
   - Add password hashing for security

3. **Basic Layout**
   - Create header.php and footer.php
   - Implement navigation system
   - Add basic CSS styling

#### Phase 2: User Management
1. **User Dashboard**
   - User profile management
   - Club browsing functionality
   - Event viewing

2. **Admin Panel**
   - User management interface
   - Club approval system
   - Event management

#### Phase 3: Club & Event System
1. **Club Management**
   - Club creation and editing
   - Member management
   - Club owner dashboard

2. **Event System**
   - Event creation and management
   - Event registration
   - Event calendar

## Database Schema (Current)
Based on the login.php implementation, the system uses:
- `admin` table: `ID_ADMIN`, `EMAIL`, `PASSWORD`
- `member` table: `ID_MEMBER`, `EMAIL`, `PASSWORD`

## Development Notes
- **Current Approach:** Monolithic PHP structure with embedded database logic
- **Security Considerations:** Passwords are currently stored in plain text (needs hashing)
- **Session Management:** Basic session-based authentication implemented
- **File Organization:** All PHP files in `public/` are entry points called directly by browser

## Next Steps
1. Extract database configuration to separate file
2. Implement password hashing
3. Create basic layout templates (header/footer)
4. Add user registration functionality
5. Implement proper redirects after login

## Technical Requirements
- **Server:** XAMPP (Apache + MySQL + PHP)
- **Database:** MySQL (data_club database)
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP with MySQLi

