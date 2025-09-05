# Powers - Laravel Product Management System

A modern Laravel application for product and task management with real-time features, authentication, and comprehensive admin capabilities.

## 🚀 Features

### Core Functionality
- **Product Management**: Complete CRUD operations for products with image uploads
- **Category Management**: Organize products with hierarchical categories
- **Task Management**: Create, track, and manage tasks with status tracking
- **User Management**: Full user authentication and authorization system
- **Real-time Interface**: Built with Livewire for dynamic, reactive components

### Technical Features
- **Modern Laravel 12**: Latest Laravel framework with PHP 8.2+
- **Livewire 3**: Real-time components without writing JavaScript
- **Livewire PowerGrid**: Advanced data tables with sorting, filtering, and pagination
- **Tailwind CSS**: Modern, utility-first CSS framework
- **Vite**: Fast build tool for frontend assets
- **Laravel Breeze**: Authentication scaffolding
- **Laravel Sanctum**: API authentication
- **Laravel Passport**: OAuth2 server implementation
- **Laravel Telescope**: Application debugging and monitoring
- **Laravel Pulse**: Real-time application monitoring
- **Queue System**: Background job processing
- **Email Notifications**: Product creation notifications
- **File Uploads**: Image handling with validation

### Development Tools
- **Laravel Debugbar**: Development debugging
- **Laravel Pail**: Real-time log viewing
- **PHPStan**: Static analysis
- **Larastan**: Laravel-specific static analysis
- **Psalm**: PHP static analysis tool
- **PHP CS Fixer**: Code style fixing
- **PHPUnit**: Testing framework
- **Laravel Dusk**: Browser testing

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL/PostgreSQL/SQLite
- Redis (for queues and caching)

## 🛠️ Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd powers
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your database**
   Edit `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=powers
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

## 🚀 Quick Start with Development Script

For a complete development environment setup, use the provided composer script:

```bash
composer run dev
```

This will start:
- Laravel development server
- Queue worker
- Real-time log viewer (Pail)
- Vite development server

## 📁 Project Structure

```
powers/
├── app/
│   ├── Http/Controllers/     # REST API controllers
│   ├── Livewire/            # Livewire components
│   ├── Models/              # Eloquent models
│   ├── Jobs/                # Queue jobs
│   └── Mail/                # Email notifications
├── database/
│   ├── migrations/          # Database migrations
│   ├── seeders/            # Database seeders
│   └── factories/          # Model factories
├── resources/
│   ├── views/              # Blade templates
│   ├── css/                # Stylesheets
│   └── js/                 # JavaScript files
└── routes/
    ├── web.php             # Web routes
    └── api.php             # API routes
```

## 🗄️ Database Schema

### Products Table
- `id` - Primary key
- `name` - Product name
- `description` - Product description
- `price` - Product price (decimal)
- `stock_quantity` - Available stock
- `category_id` - Foreign key to categories
- `status` - Product status (active/inactive)
- `sku` - Stock keeping unit
- `image_url` - Product image path
- `meta_title` - SEO meta title
- `meta_description` - SEO meta description
- `user_id` - Owner user ID

### Categories Table
- `id` - Primary key
- `name` - Category name
- `slug` - URL-friendly slug
- `description` - Category description
- `is_active` - Active status
- `sort_order` - Display order

### Tasks Table
- `id` - Primary key
- `title` - Task title
- `description` - Task description
- `status` - Task status (pending/in_progress/completed)
- `due_date` - Task due date
- `user_id` - Assigned user ID

## 🔐 Authentication

The application uses Laravel Breeze for authentication with the following features:
- User registration and login
- Email verification
- Password reset functionality
- Profile management
- Session management

## 🎨 Frontend

### Technologies Used
- **Tailwind CSS**: Utility-first CSS framework
- **Livewire**: Real-time components
- **Alpine.js**: Lightweight JavaScript framework
- **Vite**: Build tool and development server

### Key Components
- **ProductForm**: Create and edit products
- **ProductTable**: Display products with PowerGrid
- **ProductShow**: Product detail view
- **UsersTable**: User management interface

## 📧 Email System

The application includes email notifications for:
- Product creation notifications
- Email failure alerts
- Daily summary reports

## 🔧 Configuration

### Required API Keys & Services

#### 1. **Laravel Application Key**
Generate your application key:
```bash
php artisan key:generate
```
This creates the `APP_KEY` in your `.env` file.

#### 2. **Database Configuration**
Configure your database connection in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=powers
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**How to get database credentials:**
- **Local Development**: Use XAMPP, Laragon, or Docker
- **Production**: Contact your hosting provider or set up a cloud database (AWS RDS, DigitalOcean, etc.)

#### 3. **Mail Service Configuration**
Configure your email service in `.env`:

**Option A: Gmail SMTP**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Option B: Mailgun**
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-mailgun-api-key
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Option C: SendGrid**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-verified-sender@domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**How to get mail credentials:**
- **Gmail**: Enable 2FA and generate an App Password
- **Mailgun**: Sign up at mailgun.com and get API key from dashboard
- **SendGrid**: Sign up at sendgrid.com and create API key

#### 4. **Redis Configuration (for Queues & Caching)**
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**How to get Redis:**
- **Local**: Install Redis server or use Docker
- **Production**: Use Redis Cloud, AWS ElastiCache, or DigitalOcean Redis

#### 5. **Queue Configuration**
```env
QUEUE_CONNECTION=redis
QUEUE_FAILED_DRIVER=database-uuids
```

#### 6. **Laravel Passport (OAuth2)**
```env
PASSPORT_PRIVATE_KEY="-----BEGIN RSA PRIVATE KEY-----\n...\n-----END RSA PRIVATE KEY-----"
PASSPORT_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"
```

**How to generate Passport keys:**
```bash
php artisan passport:keys
```

#### 7. **Laravel Sanctum (API Authentication)**
```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8080
SESSION_DOMAIN=localhost
```

#### 8. **Laravel Telescope (Debugging)**
```env
TELESCOPE_ENABLED=true
TELESCOPE_DRIVER=database
```

#### 9. **Laravel Pulse (Monitoring)**
```env
PULSE_ENABLED=true
PULSE_DRIVER=database
```

### Additional Services (Optional)

#### 10. **Bugsnag (Error Tracking)**
```env
BUGSNAG_API_KEY=your-bugsnag-api-key
BUGSNAG_NOTIFY_RELEASE_STAGES=production
```

**How to get Bugsnag:**
1. Sign up at bugsnag.com
2. Create a new project
3. Get API key from project settings

### Environment Setup Checklist

Before running the application, ensure you have:

- [ ] Generated Laravel application key (`php artisan key:generate`)
- [ ] Configured database connection
- [ ] Set up mail service (Gmail, Mailgun, or SendGrid)
- [ ] Installed and configured Redis (for queues)
- [ ] Set up file storage (local or AWS S3)
- [ ] Generated Passport keys (`php artisan passport:keys`)
- [ ] Configured Telescope and Pulse (optional)
- [ ] Set up Pusher for real-time features (optional)
- [ ] Configured error tracking (optional)
- [ ] Set up social login (optional)

### Security Best Practices

1. **Never commit .env files** to version control
2. **Use strong, unique passwords** for all services
3. **Enable 2FA** on all service accounts
4. **Rotate API keys** regularly
5. **Use environment-specific configurations**
6. **Limit API key permissions** to minimum required access
7. **Monitor API usage** for unusual activity

## 🧪 Testing

Run the test suite:
```bash
composer test
```

Run browser tests:
```bash
php artisan dusk
```

## 📊 Monitoring & Debugging

### Laravel Telescope
Access Telescope dashboard at `/telescope` for:
- Request/response monitoring
- Database queries
- Cache operations
- Job execution tracking

### Laravel Pulse
Monitor application performance at `/pulse` for:
- Real-time metrics
- Performance insights
- Error tracking

### Laravel Pail
View real-time logs:
```bash
php artisan pail
```

## 🚀 Deployment

### Production Build
```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Variables
Ensure all production environment variables are set:
- Database credentials
- Mail configuration
- Queue configuration
- File storage settings
- Application key

## 🆘 Support

For support and questions:
- Check the Laravel documentation
- Review the application logs
- Use Laravel Telescope for debugging
- Check the Laravel Pulse dashboard for performance issues

## 🔄 Updates

Keep your application updated:
```bash
composer update
php artisan migrate
npm update
npm run build
```

## 🆕 What's New

- Soft delete support in `ProductTable`
  - Datasource now includes soft-deleted rows via `withTrashed()`.
  - Added `deleted_at` field to table data for state-aware actions.
  - New row actions when a product is soft-deleted: Restore and Force Delete (with authorization checks and confirmation modals).
  - Delete/Restore/Force Delete actions emit `deleteCompleted`, `restoreCompleted`, and `forceDeleteCompleted` events with success messages.

- Filter improvements
  - Category filter changed to `Filter::select` with a direct Eloquent data source.
  - Sub-category filter now depends on selected categories and loads options dynamically based on `category_name` selection.

- Action UI/UX updates
  - Replaced icon font buttons with inline SVG icons and added tooltips.
  - Unified button styling for better contrast and accessibility.

- Safer delete flow
  - Strongly typed `delete(int $rowId, bool $confirmed = false)`.
  - Centralized confirmation dispatch payloads for delete/restore/force delete operations.

Notes:
- Ensure your `Product` model uses Laravel's `SoftDeletes` trait for the new soft delete features to work.
- Authorization policies (`delete`, `restore`, `forceDelete`) must be defined in `ProductPolicy`.

---

**Built with ❤️ using Laravel 12, Livewire 3, and Tailwind CSS**
