# Complete System Documentation: AquaBill by olexto
## Smart Water Supply, Billing, and Customer Management (SaaS)

## System Overview

**AquaBill by olexto** is a comprehensive Software as a Service (SaaS) solution designed to revolutionize water utility operations for Dunsinane Estate. Built on Laravel 12.0 framework, it provides smart water supply, billing, and customer management of water supply services from customer registration to automated billing and payment collection.

### Key Information
- **Project Name**: AquaBill by olexto - Smart Water Supply, Billing, and Customer Management
- **Version**: 1.0 (AquaBill)
- **System Type**: Software as a Service (SaaS)
- **Powered by**: olexto Digital Solutions (Pvt) Ltd
- **Client**: Dunsinane Estate
- **Framework**: Laravel 12.0 (PHP 8.2+)
- **Database**: MySQL/SQLite
- **Frontend**: Blade Templates + Tailwind CSS + Alpine.js
- **API**: RESTful API with Laravel Sanctum authentication
- **Mobile Support**: Native mobile API with offline sync capabilities

---

## Technology Stack

### Backend Technologies
- **PHP**: 8.2+
- **Laravel Framework**: 12.0
- **Authentication**: Laravel Sanctum (API tokens)
- **Authorization**: Role-based access control
- **Database**: MySQL (production) / SQLite (development)
- **Queue System**: Laravel Queues
- **File Storage**: Laravel Filesystem

### Frontend Technologies
- **Template Engine**: Laravel Blade
- **CSS Framework**: Tailwind CSS 3.4.16
- **JavaScript Framework**: Alpine.js 3.14.7
- **Build Tool**: Vite 6.0.5
- **HTTP Client**: Axios 1.7.9

### Third-Party Integrations
- **SMS Service**: Notify.lk (Primary), Twilio SDK (Secondary)
- **QR Code Generation**: SimpleSoftwareIO/SimpleQRCode 4.2
- **PDF Generation**: Built-in Laravel capabilities
- **Maps Integration**: Google Maps (via coordinates)

### Development Tools
- **Code Quality**: Laravel Pint (PHP CS Fixer)
- **Testing**: PHPUnit 11.5.3
- **API Testing**: Built-in Laravel testing
- **Debugging**: Laravel Pail 1.2.2
- **Development Server**: Laravel Sail 1.41

---

## System Architecture

### Application Structure
```
AquaBill/
├── app/
│   ├── Console/           # Artisan commands
│   ├── Helpers/          # Helper functions
│   ├── Http/
│   │   ├── Controllers/   # Web controllers
│   │   │   ├── Api/      # API controllers
│   │   │   └── Auth/     # Authentication controllers
│   │   ├── Middleware/   # Custom middleware
│   │   └── Requests/     # Form request validation
│   ├── Listeners/        # Event listeners
│   ├── Models/           # Eloquent models
│   ├── Providers/        # Service providers
│   ├── Services/         # Business logic services
│   ├── Traits/          # Reusable traits
│   └── View/            # View composers
├── config/              # Configuration files
├── database/
│   ├── factories/       # Model factories
│   ├── migrations/      # Database migrations
│   └── seeders/         # Database seeders
├── resources/
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── views/          # Blade templates
├── routes/
│   ├── web.php         # Web routes
│   ├── api.php         # API routes
│   └── auth.php        # Authentication routes
└── storage/            # File storage
```

---

## Database Schema

### Core Models and Relationships

#### 1. **Customer Model** (`customers` table)
**Primary Entity**: Represents water service customers

**Key Fields**:
- `account_number` - Unique customer identifier
- `reference_number` - Alternative reference
- `title`, `first_name`, `last_name` - Customer personal info
- `email`, `phone`, `phone_two` - Contact information
- `nic`, `epf_number` - Sri Lankan specific identifiers
- `address`, `city`, `postal_code` - Address details
- `status` - Customer status (active/inactive)
- `billing_day`, `next_billing_date` - Billing configuration
- `customer_type_id` - Links to customer type
- `division_id` - Links to geographical division
- `guarantor_id` - Links to guarantor if applicable

**Relationships**:
- `HasMany`: WaterMeters, Bills, SmsNotifications
- `BelongsTo`: CustomerType, Division, Guarantor
- `HasManyThrough`: MeterReadings

#### 2. **WaterMeter Model** (`water_meters` table)
**Entity**: Represents physical water meters

**Key Fields**:
- `meter_number` - Unique meter identifier
- `meter_brand`, `meter_model`, `meter_size` - Hardware specifications
- `meter_type` - Type classification
- `installation_date`, `last_maintenance_date` - Maintenance tracking
- `initial_reading`, `current_reading` - Reading values
- `multiplier` - Reading calculation multiplier
- `latitude`, `longitude` - GPS coordinates
- `location_notes`, `address` - Location information
- `status` - Meter status

**Relationships**:
- `BelongsTo`: Customer
- `HasMany`: MeterReadings, Bills

**Special Features**:
- QR code generation for mobile scanning
- GPS tracking capabilities
- Maintenance scheduling

#### 3. **MeterReading Model** (`meter_readings` table)
**Entity**: Records meter readings

**Key Fields**:
- `water_meter_id` - Links to specific meter
- `reading_date` - Date of reading
- `current_reading` - Recorded value
- `previous_reading` - Previous recorded value
- `consumption` - Calculated consumption
- `reader_id` - User who took reading
- `reading_type` - Manual/automatic classification
- `status` - Reading status
- `notes` - Additional notes
- `gps_latitude`, `gps_longitude` - Location verification
- `photo_path` - Photo evidence
- `submitted_via` - Source (web/mobile)

**Relationships**:
- `BelongsTo`: WaterMeter, User (reader)
- `HasMany`: Bills

#### 4. **Bill Model** (`bills` table)
**Entity**: Represents water usage bills

**Key Fields**:
- `customer_id`, `water_meter_id`, `meter_reading_id` - Relationships
- `bill_number` - Unique bill identifier
- `bill_date`, `due_date` - Billing dates
- `billing_period_from`, `billing_period_to` - Billing period
- `previous_reading`, `current_reading`, `consumption` - Usage data
- `water_charges`, `fixed_charges`, `service_charges` - Charge breakdown
- `late_fees`, `taxes`, `adjustments` - Additional charges
- `total_amount`, `paid_amount`, `balance_amount` - Payment tracking
- `status` - Bill status (draft/generated/sent/paid/overdue)
- `rate_breakdown` - JSON field with detailed rate calculations

**Relationships**:
- `BelongsTo`: Customer, WaterMeter, MeterReading
- `HasMany`: SmsNotifications

#### 5. **Rate Model** (`rates` table)
**Entity**: Defines pricing structures

**Key Fields**:
- `name` - Rate plan name
- `customer_type_id` - Applicable customer type
- `rate_type` - Tiered/flat/block rate
- `base_charge` - Fixed monthly charge
- `rate_structure` - JSON field with tier definitions
- `effective_from`, `effective_to` - Validity period
- `status` - Active/inactive status

#### 6. **User Model** (`users` table)
**Entity**: System users (admin, staff, meter readers)

**Key Fields**:
- `name`, `email` - User identification
- `role` - User role (admin/manager/staff/meter_reader)
- `email_verified_at` - Email verification
- `password` - Encrypted password

**Roles**:
- **Admin**: Full system access
- **Manager**: Management and reporting access
- **Staff**: Customer and billing management
- **Meter Reader**: Mobile app access for readings

#### 7. **SmsNotification Model** (`sms_notifications` table)
**Entity**: SMS communication tracking

**Key Fields**:
- `customer_id`, `bill_id` - Related entities
- `phone_number` - Recipient phone
- `message` - SMS content
- `message_type` - Notification type
- `status` - Delivery status
- `sent_at`, `delivered_at` - Timing information
- `error_message` - Error details if failed

---

## Core Features and Functionality

### 1. **Customer Management**
- **Registration**: Complete customer onboarding with Sri Lankan specific fields
- **Profile Management**: Contact details, address, billing preferences
- **Document Management**: Profile photos, identification documents
- **Status Tracking**: Active/inactive customer status
- **Guarantor System**: Guarantor assignment for security deposits
- **Division Assignment**: Geographical area management

### 2. **Water Meter Management**
- **Meter Registration**: Complete meter hardware tracking
- **QR Code Integration**: Unique QR codes for mobile scanning
- **GPS Tracking**: Location verification and mapping
- **Maintenance Scheduling**: Automatic maintenance reminders
- **Status Monitoring**: Active/inactive/maintenance status
- **Multi-meter Support**: Multiple meters per customer

### 3. **Meter Reading System**
- **Manual Entry**: Web-based reading entry
- **Mobile App**: Dedicated mobile application for field readings
- **Bulk Entry**: Mass reading import capabilities
- **Photo Evidence**: Camera integration for reading verification
- **GPS Verification**: Location-based reading validation
- **Offline Sync**: Mobile offline capabilities with later synchronization
- **Reading History**: Complete historical tracking

### 4. **Billing System**
- **Automated Bill Generation**: Scheduled bill creation
- **Flexible Rate Structures**: Tiered, flat, and block rate support
- **Multi-charge Support**: Water, fixed, service, and late fees
- **Tax Calculations**: Automatic tax computation
- **Bill Templates**: Customizable bill formats
- **Print Functionality**: PDF generation and printing
- **Payment Tracking**: Complete payment history

### 5. **Payment Management**
- **Multiple Payment Methods**: Cash, bank transfer, cheque support
- **Partial Payments**: Support for installment payments
- **Payment Receipts**: Automatic receipt generation
- **Outstanding Balance Tracking**: Real-time balance calculations
- **Late Fee Calculation**: Automatic late fee application
- **Payment History**: Complete transaction records

### 6. **SMS Notification System**
- **Automated Notifications**: Bill generation, due reminders, overdue alerts
- **Payment Confirmations**: Instant payment acknowledgments
- **Service Notices**: Maintenance and service interruption alerts
- **Bulk Messaging**: Mass communication capabilities
- **Template Management**: Customizable message templates
- **Delivery Tracking**: SMS delivery status monitoring
- **Multiple Providers**: Notify.lk (primary) and Twilio (backup)

### 7. **Mobile API System**
- **RESTful API**: Complete mobile application support
- **Authentication**: Token-based security with Laravel Sanctum
- **Offline Support**: Local data storage and sync capabilities
- **Photo Upload**: Image capture and upload for readings
- **GPS Integration**: Location-based services
- **QR Code Scanning**: Mobile QR code reader integration
- **Receipt Generation**: Mobile receipt printing

### 8. **Reporting and Analytics**
- **Dashboard Analytics**: Real-time system overview
- **Consumption Reports**: Water usage analysis
- **Revenue Reports**: Financial performance tracking
- **Overdue Reports**: Outstanding payment analysis
- **Monthly Active Reports**: Customer activity tracking
- **Custom Reports**: Flexible report generation
- **Export Capabilities**: CSV and PDF export options

### 9. **System Administration**
- **User Management**: Multi-role user system
- **Settings Management**: System configuration
- **Rate Management**: Pricing structure configuration
- **Division Management**: Geographical area organization
- **Customer Type Management**: Customer classification
- **Activity Logging**: Complete audit trail
- **System Status Control**: Enable/disable system functionality

### 10. **Security Features**
- **Role-Based Access Control**: Fine-grained permissions
- **API Authentication**: Secure token-based access
- **Activity Logging**: Complete user action tracking
- **Data Validation**: Comprehensive input validation
- **CSRF Protection**: Cross-site request forgery prevention
- **Password Security**: Encrypted password storage

---

## API Documentation

### Base Configuration
- **Base URL**: `http://your-domain.com/api/v1`
- **Authentication**: Bearer Token (Laravel Sanctum)
- **Content-Type**: `application/json`
- **API Version**: v1

### Key API Endpoints

#### Authentication
- `POST /login` - User authentication
- `POST /logout` - User logout
- `GET /check-token` - Token validation
- `GET /profile` - User profile
- `PUT /profile` - Update profile

#### Meter Reading
- `GET /meter-reading/route/today` - Get daily reading route
- `POST /meter-reading/submit` - Submit new reading
- `POST /meter-reading/bulk-sync` - Bulk sync readings
- `GET /meter-reading/customers/search` - Search customers
- `GET /meter-reading/customers/{id}` - Customer details
- `GET /meter-reading/customers/{id}/history` - Reading history

#### QR Code Management
- `POST /meter-reading/qr-code/generate` - Generate QR code
- `POST /meter-reading/qr-code/scan` - Scan QR code
- `GET /meter-reading/qr-code/download/{id}` - Download QR code
- `POST /meter-reading/qr-code/batch-generate` - Batch generate

#### Payment Management
- `GET /payments/customer/{id}/bills` - Get customer bills
- `POST /payments/record` - Record payment
- `GET /payments/customer/{id}/history` - Payment history
- `GET /payments/customers/search` - Search for payment

#### Utility Functions
- `GET /health` - Health check
- `GET /app-info` - Application information
- `GET /utils/system-info` - System status

---

## Web Interface

### User Interface Structure

#### 1. **Dashboard**
- **Overview Widgets**: Key metrics and statistics
- **Recent Activity**: Latest system activities
- **Quick Actions**: Common task shortcuts
- **Chart Analytics**: Visual data representation
- **Notification Center**: System alerts and messages

#### 2. **Customer Management**
- **Customer List**: Searchable and filterable customer directory
- **Customer Details**: Comprehensive customer profiles
- **Add/Edit Forms**: Customer registration and modification
- **Meter Association**: Link customers to water meters
- **Bill History**: Customer-specific billing records

#### 3. **Meter Management**
- **Meter Registry**: Complete meter inventory
- **Meter Details**: Comprehensive meter information
- **Location Mapping**: GPS-based meter locations
- **QR Code Management**: Generate and download QR codes
- **Maintenance Tracking**: Maintenance schedule management

#### 4. **Reading Management**
- **Reading Entry**: Manual reading input forms
- **Bulk Entry**: Mass reading import interface
- **Reading History**: Historical reading data
- **Verification Tools**: Reading validation interface
- **Schedule Management**: Monthly reading schedules

#### 5. **Billing Interface**
- **Bill Generation**: Automated and manual bill creation
- **Bill Management**: Bill editing and status updates
- **Payment Processing**: Payment recording interface
- **Print Functions**: Bill and receipt printing
- **Late Fee Management**: Late charge calculation and application

#### 6. **SMS Management**
- **Notification Center**: SMS campaign management
- **Template Editor**: Message template configuration
- **Delivery Tracking**: SMS status monitoring
- **Bulk Messaging**: Mass communication tools
- **Statistics Dashboard**: SMS performance analytics

#### 7. **Settings and Configuration**
- **System Settings**: Global system configuration
- **Rate Management**: Pricing structure setup
- **User Management**: User account administration
- **Division Setup**: Geographical area configuration
- **Template Management**: Document and message templates

---

## File Structure and Key Components

### Controllers

#### Web Controllers
- **`DashboardController`**: Main dashboard functionality
- **`CustomerController`**: Customer CRUD operations
- **`WaterMeterController`**: Meter management
- **`MeterReadingController`**: Reading operations
- **`BillController`**: Billing functionality
- **`SmsNotificationController`**: SMS management
- **`SettingsController`**: System configuration
- **`UserController`**: User management
- **`ActivityLogController`**: System audit logs

#### API Controllers
- **`AuthApiController`**: API authentication
- **`MeterReadingApiController`**: Mobile reading API
- **`PaymentApiController`**: Payment processing API

### Models and Business Logic
- **`Customer`**: Customer data and relationships
- **`WaterMeter`**: Meter management and QR code generation
- **`MeterReading`**: Reading data and calculations
- **`Bill`**: Billing calculations and status management
- **`Rate`**: Pricing structure definitions
- **`SmsNotification`**: SMS communication tracking
- **`ActivityLog`**: System activity auditing

### Services
- **`SmsNotificationService`**: SMS delivery handling
- **`BillingService`**: Billing calculations and automation
- **`QrCodeService`**: QR code generation and management

### Routes
- **Web Routes**: Complete web application routing
- **API Routes**: RESTful API endpoint definitions
- **Authentication Routes**: User authentication flows

---

## Configuration and Setup

### Environment Configuration
The system uses environment variables for configuration:

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=water_billmspro
DB_USERNAME=root
DB_PASSWORD=

# SMS Configuration (Notify.lk)
NOTIFYLK_USER_ID=your_user_id
NOTIFYLK_API_KEY=your_api_key
NOTIFYLK_SENDER_ID=your_sender_id

# SMS Configuration (Twilio Backup)
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=your_twilio_number

# Application Settings
APP_NAME="AquaBill by olexto"
APP_ENV=production
APP_KEY=base64:generated_key
APP_DEBUG=false
APP_URL=http://your-domain.com
```

### Installation Steps
1. **Clone Repository**: Download the application code
2. **Install Dependencies**: Run `composer install` and `npm install`
3. **Environment Setup**: Configure `.env` file
4. **Database Setup**: Run migrations and seeders
5. **Build Assets**: Compile frontend assets with `npm run build`
6. **Configure Web Server**: Set up Apache/Nginx
7. **Set Permissions**: Configure file permissions
8. **Test Installation**: Verify all components work

### Database Migrations
The system includes comprehensive migrations for:
- User management and authentication
- Customer and meter management
- Billing and payment tracking
- SMS notification system
- Activity logging and auditing
- System configuration

---

## Security Implementation

### Authentication and Authorization
- **Multi-role System**: Admin, Manager, Staff, Meter Reader roles
- **Token-based API**: Laravel Sanctum for mobile apps
- **Session Management**: Secure web session handling
- **Password Security**: Encrypted password storage

### Data Protection
- **Input Validation**: Comprehensive form validation
- **SQL Injection Prevention**: Eloquent ORM protection
- **CSRF Protection**: Cross-site request forgery prevention
- **XSS Protection**: Output sanitization

### Activity Monitoring
- **Complete Audit Trail**: All user actions logged
- **IP Address Tracking**: Source identification
- **Timestamp Recording**: Action timing
- **Module-based Logging**: Categorized activity tracking

---

## Mobile Application Support

### Mobile API Features
- **Complete Mobile API**: Full functionality via REST API
- **Offline Capability**: Local data storage and sync
- **Photo Integration**: Camera capture for reading verification
- **GPS Integration**: Location-based services
- **QR Code Support**: Mobile QR code scanning
- **Receipt Generation**: Mobile receipt printing

### Mobile App Capabilities
- **Daily Route Management**: Assigned customer routes
- **Reading Collection**: Meter reading with photo evidence
- **Customer Search**: Find and view customer details
- **Payment Collection**: Process customer payments
- **Sync Management**: Offline/online data synchronization
- **Performance Tracking**: Reader statistics and performance

---

## Business Features

### Customer Types and Divisions
- **Customer Classification**: Residential, commercial, industrial
- **Geographical Divisions**: Area-based organization
- **Custom Fields**: Sri Lankan specific requirements (NIC, EPF)
- **Guarantor System**: Security deposit management

### Billing Features
- **Flexible Rate Structures**: Tiered, flat, block rate support
- **Multiple Charge Types**: Water, fixed, service charges
- **Automatic Calculations**: Consumption and charge calculations
- **Late Fee Management**: Automatic late charge application
- **Payment Tracking**: Comprehensive payment history

### Communication Features
- **Automated SMS**: Bill notifications and reminders
- **Template Management**: Customizable message templates
- **Delivery Tracking**: SMS delivery status monitoring
- **Bulk Messaging**: Mass communication capabilities

---

## System Administration

### User Management
- **Role-based Access**: Granular permission system
- **User Profiles**: Complete user information management
- **Activity Monitoring**: User action tracking
- **Password Management**: Secure password policies

### System Control
- **Global Enable/Disable**: System-wide operation control
- **Maintenance Mode**: Controlled system access
- **Configuration Management**: Centralized settings
- **Backup and Recovery**: Data protection measures

### Monitoring and Maintenance
- **Activity Logs**: Complete system audit trail
- **Performance Monitoring**: System performance tracking
- **Error Logging**: Comprehensive error tracking
- **Health Checks**: System status monitoring

---

## Route Documentation

### Web Routes Structure

#### Always Accessible Routes (Even When System is Disabled)
- **Dashboard**: `/dashboard` - Main system overview
- **Profile Management**: `/profile` - User profile operations
- **Customer Views**: `/customers`, `/customers/{customer}` - Read-only customer access
- **Bill Views**: `/bills`, `/bills/{bill}` - Read-only bill access
- **Payments**: `/bills/{bill}/payment` - Payment processing (always enabled)

#### System-Controlled Routes (Disabled When System is Off)
- **User Management**: `/users/*` - Complete user administration
- **Customer Management**: `/customers/create`, `/customers/{customer}/edit` - Customer modifications
- **Meter Management**: `/water-meters/*` - All meter operations
- **Reading Management**: `/meter-readings/*` - All reading operations
- **Bill Management**: `/bills/create`, `/bills/{bill}/edit` - Bill modifications
- **SMS Management**: `/sms-notifications/*` - SMS operations
- **Settings**: `/settings/*` - System configuration
- **Reports**: `/reports/*` - Report generation
- **Activity Logs**: `/activity-logs/*` - System monitoring

#### System Control Routes (Admin Only)
- **System Status**: `/system/status` - View system status
- **System Control**: `/system/enable`, `/system/disable`, `/system/toggle` - System control
- **System Disabled Page**: `/system/disabled` - Maintenance page

### API Routes Structure

#### Public Routes
- **Health Check**: `GET /api/v1/health` - System health status
- **App Info**: `GET /api/v1/app-info` - Application information
- **Login**: `POST /api/v1/login` - User authentication

#### Protected Routes (Require Authentication)
- **Authentication Management**: Logout, refresh, token validation
- **User Profile**: Profile viewing and updating
- **Meter Reading Operations**: Complete mobile reading functionality
- **QR Code Operations**: Generate, scan, download QR codes
- **Payment Operations**: Bill viewing, payment recording, history
- **Sync Operations**: Data synchronization for offline support
- **Utility Operations**: System information, areas, routes

---

## Test Files and Development Tools

### Test Scripts Available
- **SMS Testing**: `test_sms_*.php` - Various SMS functionality tests
- **System Control Testing**: `test_system_*.php` - System control tests
- **Billing Testing**: `test_*_billing.php` - Billing functionality tests
- **Navigation Testing**: `test_*_navigation.php` - UI navigation tests
- **Relationship Testing**: `test_relationship_fix.php` - Database relationship tests

### Development Utilities
- **Sample Data**: `add_sample_sms_log.php`, `send_all_samples.php`
- **Configuration Files**: `env_complete.txt`, `sms_config.txt`
- **Usage Examples**: `SMS_Usage_Examples.md`
- **API Testing**: `API_TEST_REPORT.md`

---

## Future Development and Roadmap

### Planned Features
- **Area and Route Management**: Enhanced geographical organization
- **Advanced Reporting**: Extended analytics and reporting
- **Push Notifications**: Mobile push notification support
- **Multi-language Support**: Internationalization capabilities
- **Advanced Analytics**: Business intelligence features
- **Bulk Operations**: Enhanced batch processing
- **API Documentation UI**: Interactive API documentation

### Technical Improvements
- **Performance Optimization**: Enhanced system performance
- **Scalability Enhancements**: Multi-tenant support
- **Integration Capabilities**: Third-party system integration
- **Advanced Security**: Enhanced security features
- **Mobile App Enhancement**: Native mobile applications

---

## Support and Maintenance

### Technical Support
- **System Monitoring**: 24/7 system availability monitoring
- **Bug Tracking**: Issue identification and resolution
- **Performance Optimization**: Continuous performance improvement
- **Security Updates**: Regular security patch management

### Business Support
- **User Training**: System usage training programs
- **Documentation**: Comprehensive user manuals
- **Feature Requests**: Custom feature development
- **Consulting Services**: Business process optimization

---

## Marketing and Business Information

### Business Description
**Smart Water Management, Simplified** - The Water Supply and Management System is a comprehensive SaaS solution designed to revolutionize water utility operations. The platform digitizes and streamlines the entire water supply ecosystem, from customer onboarding to automated billing and service management.

### Key Benefits
- Reduce operational costs by up to 40%
- Eliminate manual paperwork and errors
- Improve customer satisfaction with digital services
- Access data and manage operations from anywhere
- Automatic updates and cloud-based reliability

### Target Market
- Municipal water departments
- Private water companies
- Utility service providers
- Estate and community water management
- Government water authorities

### Competitive Advantages
- Mobile-first approach with offline capabilities
- Sri Lankan market specific features (NIC, EPF integration)
- Comprehensive SMS notification system
- QR code integration for modern operations
- Role-based multi-user system
- Complete API for third-party integrations

---

## Database Reset and Maintenance

### Database Reset Command
To reset the database while preserving system users:

```bash
php artisan db:refresh-preserve-users
```

**Options**:
- `--force`: Skip confirmation prompts
- `--preserve-admins`: Only preserve admin users

**What's Preserved**:
- System users (admin, manager, staff, meter_reader)
- User authentication data
- Critical user settings

**What's Reset**:
- All customer data
- All water meters and readings
- All bills and payments
- All activity logs
- All system configurations

### Test Accounts
After database reset, use these credentials:

**Admin Account**:
- Email: `admin@aquabill.olexto.com`
- Password: `password`

**Test Meter Readers**:
- Email: `reader1@aquabill.olexto.com` to `reader50@aquabill.olexto.com`
- Password: `password`

**Test Staff**:
- Email: `staff1@aquabill.olexto.com` to `staff30@aquabill.olexto.com`
- Password: `password`

**Test Managers**:
- Email: `manager1@aquabill.olexto.com` to `manager20@aquabill.olexto.com`
- Password: `password`

---

## Conclusion

AquaBill by olexto represents a comprehensive, modern SaaS solution for water utility management. Built with cutting-edge technologies and designed for scalability, it provides smart water supply, billing, and customer management supply operations from customer registration through billing and payment collection.

The system's mobile-first approach, combined with robust web interfaces and comprehensive API support, ensures that water utilities can operate efficiently in both office and field environments. With features like offline synchronization, QR code integration, automated billing, and SMS communications, AquaBill provides the technological foundation for modern water utility operations as a Software as a Service platform.

**Powered by Software as a System by olexto Digital Solutions (Pvt) Ltd** - this system represents the future of water utility management in Sri Lanka and beyond.

---

*Last Updated: January 2025*
*System Version: 3.0*
*Documentation Version: 1.0*