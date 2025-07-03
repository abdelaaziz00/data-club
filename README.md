Project: Data Club
Location: C:\xampp\htdocs\data-club

-------------------------------------------------------
1. public/
   - Purpose: Contains all files accessible directly by the browser (frontend).
   
   1.1 auth/
       - Purpose: Handles user authentication pages.
       - Suggested files:
         • login.php       - User login form and processing.
         • signup.php      - User registration form and processing.
         • logout.php      - Script to log out user and destroy session.
   
   1.2 user/
       - Purpose: Pages and features accessible to normal members.
       - Suggested files:
         • index.php       - User homepage/dashboard.
         • profile.php     - View/edit user personal profile.
         • events.php      - List upcoming events.
         • club_list.php   - Browse available clubs.
   
   1.3 admin/
       - Purpose: Admin panel pages to manage site data.
       - Suggested files:
         • index.php           - Admin dashboard overview.
         • manage_clubs.php    - Approve/reject club creation requests.
         • manage_events.php   - Add/edit/delete events.
         • manage_users.php    - Manage user accounts and roles.
   
   1.4 club_owner/
       - Purpose: Pages for club owners/managers.
       - Suggested files:
         • index.php           - Club owner dashboard.
         • club_profile.php    - Edit club info, description, logo.
         • create_event.php    - Create new events for the club.
         • manage_members.php  - Approve/reject club membership requests.
   
   1.5 static/
       - Purpose: Static assets (CSS, JavaScript, images).
       - Suggested folders/files:
         • css/                - Tailwind CSS file(s).
         • js/                 - JavaScript files.
         • images/             - Club logos, event banners, user avatars.
   
   1.6 includes/
       - Purpose: Reusable page fragments.
       - Suggested files:
         • header.php          - HTML head, opening <body>, nav bar.
         • footer.php          - Footer content and closing tags.
         • navbar.php          - Navigation menu.
         • auth_check.php      - PHP script to check user login & roles.
   
-------------------------------------------------------
2. src/
   - Purpose: Backend PHP source files containing application logic.
   
   2.1 controllers/
       - Purpose: Handle form submissions and page-specific business logic.
       - Suggested files:
         • AuthController.php      - Login, logout, signup handling.
         • UserController.php      - User profile and actions.
         • AdminController.php     - Admin functions.
         • ClubOwnerController.php - Club owner actions.
         • EventController.php     - Event creation and management.
   
   2.2 models/
       - Purpose: Database access and data representation.
       - Suggested files:
         • User.php                - User data and DB queries.
         • Club.php                - Club data and DB queries.
         • Event.php               - Event data and DB queries.
   
   2.3 config/
       - Purpose: Configuration files.
       - Suggested files:
         • database.php            - Database connection setup.
         • config.php              - Site-wide constants.
   
   2.4 helpers/
       - Purpose: Utility functions used throughout the app.
       - Suggested files:
         • auth.php                - Authentication helpers.
         • validation.php          - Form input validation functions.
         • utils.php               - Misc helper functions.
   
-------------------------------------------------------
Root files:
- tailwind.config.js              - Tailwind CSS configuration.
- .htaccess                      - Apache URL rewriting and security.
- README.md                      - Project overview and setup instructions.

-------------------------------------------------------
Notes:
- All PHP files in 'public/' are entry points called directly by browser.
- Logic and DB code stay inside 'src/' and are included where needed.
- Use 'includes/' for reusable HTML/PHP snippets.
- Protect pages by including 'auth_check.php' that verifies user sessions and roles.
- Static assets served from 'public/static/'.

