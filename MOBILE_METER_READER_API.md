# Mobile Meter Reader API Documentation

## Overview
Complete REST API documentation for building a mobile application for meter readers. This API provides all functionality needed for field meter reading operations.

**📱 Flexible Reading System:**
- ✅ **Photo**: Optional (can submit readings with or without photos)
- ✅ **GPS**: Optional (can submit readings with or without location)
- ✅ **Offline**: Full offline support with sync
- ✅ **QR Scanning**: Simple meter number scanning (e.g., "MTR001")
- ✅ **Auto Receipts**: Generated with every reading

## Base Configuration
- **Base URL**: `http://your-domain.com/api/v1`
- **Authentication**: Bearer Token (Laravel Sanctum)
- **Content-Type**: `application/json` (or `multipart/form-data` for file uploads)
- **API Version**: v1
- **Response Format**: JSON

---

## 🔐 Authentication

### 1. Login
Authenticate meter reader and get access token.

**Endpoint**: `POST /login`

**Request**:
```json
{
  "email": "reader@aquabill.olexto.com",
  "password": "password",
  "device_name": "Samsung Galaxy S21",
  "device_info": {
    "model": "Samsung Galaxy S21",
    "os": "Android 12",
    "app_version": "1.0.0"
  }
}
```

**Response** (Success - 200):
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123def456ghi789",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "reader@aquabill.olexto.com",
      "role": "meter_reader",
      "permissions": {
        "can_read_meters": true,
        "can_view_customer_details": true,
        "can_take_photos": true,
        "can_add_notes": true
      }
    },
    "app_config": {
      "features": {
        "offline_mode": true,
        "photo_capture": true,
        "gps_tracking": true,
        "receipt_printing": true,
        "auto_sync": true
      },
      "settings": {
        "max_photo_size": 5120,
        "auto_sync_interval": 300,
        "gps_accuracy_threshold": 10
      }
    },
    "expires_at": "2024-02-15T10:30:00Z"
  }
}
```

**Response** (Error - 401):
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

### 2. Logout
End user session and revoke token.

**Endpoint**: `POST /logout`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### 3. Check Token
Verify if token is still valid.

**Endpoint**: `GET /check-token`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "success": true,
  "message": "Token is valid",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "reader@aquabill.olexto.com",
      "role": "meter_reader"
    },
    "expires_at": "2024-02-15T10:30:00Z"
  }
}
```

### 4. User Profile
Get current user information.

**Endpoint**: `GET /profile`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "reader@aquabill.olexto.com",
      "role": "meter_reader",
      "last_login": "2024-01-20T10:30:00Z"
    }
  }
}
```

---

## 🔍 Search & Discovery

### 1. Search Customers/Meters
Find customers and meters by various criteria.

**Endpoint**: `GET /meter-reading/customers/search`
**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `q` or `search`: Search term (customer name, meter number, account number)
- `limit`: Number of results (default: 50, max: 100)

**Example**: `GET /meter-reading/customers/search?q=John&limit=10`

**Response** (200):
```json
{
  "success": true,
  "data": {
    "customers": [
      {
        "id": 1,
        "connection_number": "WB001",
        "name": "John Smith",
        "address": "123 Main Street, Colombo 03",
        "phone": "0771234567",
        "meter": {
          "id": 1,
          "meter_number": "MTR001",
          "type": "digital",
          "current_reading": 1250.5,
          "status": "active",
          "location_description": "Front yard, near gate",
          "gps_latitude": 6.9271,
          "gps_longitude": 79.8612
        },
        "last_reading": {
          "reading": 1200.0,
          "date": "2024-01-15",
          "reader": "John Doe"
        },
        "status": "active"
      }
    ],
    "total_count": 1,
    "search_term": "John"
  }
}
```

### 2. Get Customer Details
Get detailed information about specific customer.

**Endpoint**: `GET /meter-reading/customers/{customer_id}`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "success": true,
  "data": {
    "customer": {
      "id": 1,
      "connection_number": "WB001",
      "name": "John Smith",
      "address": "123 Main Street, Colombo 03",
      "phone": "0771234567",
      "email": "john.smith@example.com",
      "status": "active"
    },
    "meter": {
      "id": 1,
      "meter_number": "MTR001",
      "type": "digital",
      "current_reading": 1250.5,
      "status": "active",
      "location_description": "Front yard, near gate",
      "gps_latitude": 6.9271,
      "gps_longitude": 79.8612
    },
    "recent_readings": [
      {
        "id": 123,
        "reading": 1250.5,
        "date": "2024-01-15",
        "consumption": 25.0,
        "reader": "John Doe",
        "status": "verified"
      }
    ]
  }
}
```

### 3. Get Today's Route
Get assigned customers for today's readings.

**Endpoint**: `GET /meter-reading/route/today`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "success": true,
  "data": {
    "customers": [
      {
        "id": 1,
        "connection_number": "WB001",
        "name": "John Smith",
        "address": "123 Main Street, Colombo 03",
        "phone": "0771234567",
        "meter": {
          "id": 1,
          "meter_number": "MTR001",
          "type": "digital",
          "current_reading": 1250.5,
          "status": "active",
          "location_description": "Front yard, near gate",
          "gps_latitude": 6.9271,
          "gps_longitude": 79.8612
        },
        "last_reading": {
          "reading": 1200.0,
          "date": "2024-01-15",
          "reader": "John Doe"
        },
        "status": "active"
      }
    ],
    "total_count": 25,
    "route_info": {
      "date": "2024-01-20",
      "reader": "John Doe"
    }
  }
}
```

---

## 📱 QR Code Operations

### 1. Scan QR Code
Scan QR code to find meter details.

**Endpoint**: `POST /meter-reading/qr-code/scan`
**Headers**: `Authorization: Bearer {token}`

**Request**:
```json
{
  "qr_data": "MTR001"
}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Meter found successfully",
  "data": {
    "meter": {
      "id": 1,
      "meter_number": "MTR001",
      "type": "digital",
      "status": "active",
      "location_description": "Front yard, near gate",
      "gps_latitude": 6.9271,
      "gps_longitude": 79.8612
    },
    "customer": {
      "id": 1,
      "name": "John Smith",
      "connection_number": "WB001",
      "address": "123 Main Street, Colombo 03",
      "phone": "0771234567"
    },
    "last_reading": {
      "id": 123,
      "reading": 1250.5,
      "previous_reading": 1225.0,
      "consumption": 25.5,
      "reading_date": "2024-01-15",
      "reader_name": "John Doe",
      "status": "verified"
    },
    "scan_timestamp": "2024-01-20T10:30:00Z"
  }
}
```

**Response** (Error - 404):
```json
{
  "success": false,
  "message": "Invalid QR code or meter not found"
}
```

### 2. Generate QR Code
Generate QR code for a meter (if needed for printing).

**Endpoint**: `POST /meter-reading/qr-code/generate`
**Headers**: `Authorization: Bearer {token}`

**Request**:
```json
{
  "meter_id": 1,
  "size": 200,
  "format": "png"
}
```

**Response** (200):
```json
{
  "success": true,
  "message": "QR code generated successfully",
  "data": {
    "meter_id": 1,
    "meter_number": "MTR001",
    "customer_name": "John Smith",
    "qr_code_url": "http://example.com/storage/qr-codes/meters/meter_MTR001_200.png",
    "qr_code_base64": "data:image/png;base64,iVBORw0KGgo...",
    "qr_code_data": "MTR001",
    "download_url": "http://example.com/api/v1/meter-reading/qr-code/download/1"
  }
}
```

---

## 📊 Meter Reading Operations

### 1. Submit Reading (Basic - No Photo/GPS)
Submit meter reading with only required fields.

**Endpoint**: `POST /meter-reading/submit`
**Headers**: `Authorization: Bearer {token}`, `Content-Type: application/json`

**Minimal Request** (Required fields only):
```json
{
  "customer_id": 1,
  "current_reading": 1275.5,
  "reading_date": "2024-01-20"
}
```

**Complete Request** (All optional fields):
```json
{
  "customer_id": 1,
  "meter_id": 1,
  "current_reading": 1275.5,
  "reading_date": "2024-01-20",
  "gps_latitude": 6.9271,
  "gps_longitude": 79.8612,
  "notes": "Meter condition good, reading clear",
  "meter_condition": "good",
  "reading_accuracy": "exact"
}
```

**Field Descriptions**:
- `customer_id`: **Required**, customer ID
- `meter_id`: **Optional**, meter ID (auto-detected if not provided)
- `current_reading`: **Required**, numeric reading value
- `reading_date`: **Required**, date in YYYY-MM-DD format
- `gps_latitude`: **Optional**, latitude coordinate (-90 to 90)
- `gps_longitude`: **Optional**, longitude coordinate (-180 to 180)
- `notes`: **Optional**, additional notes (max 500 chars)
- `meter_condition`: **Optional**, values: good, damaged, broken, needs_repair
- `reading_accuracy`: **Optional**, values: exact, estimated, calculated, actual

### 2. Submit Reading with Photo (Optional)
Submit meter reading with optional photo evidence.

**Endpoint**: `POST /meter-reading/submit`
**Headers**: `Authorization: Bearer {token}`, `Content-Type: multipart/form-data`

**With Photo and GPS**:
```
customer_id: 1
meter_id: 1
current_reading: 1275.5
reading_date: 2024-01-20
gps_latitude: 6.9271
gps_longitude: 79.8612
meter_photo: [file upload - max 5MB, JPEG/PNG]
notes: Reading taken with photo and GPS
meter_condition: good
reading_accuracy: exact
```

**With Photo Only (No GPS)**:
```
customer_id: 1
current_reading: 1275.5
reading_date: 2024-01-20
meter_photo: [file upload - max 5MB, JPEG/PNG]
notes: Reading taken with photo only
```

**With GPS Only (No Photo)**:
```
customer_id: 1
current_reading: 1275.5
reading_date: 2024-01-20
gps_latitude: 6.9271
gps_longitude: 79.8612
notes: Reading taken with GPS only
```

**Response** (Success - 200):
```json
{
  "success": true,
  "message": "Meter reading submitted successfully",
  "data": {
    "reading_id": 123,
    "customer": {
      "name": "John Smith",
      "connection_number": "WB001",
      "address": "123 Main Street, Colombo 03"
    },
    "meter": {
      "meter_number": "MTR001",
      "previous_reading": 1250.5,
      "current_reading": 1275.5,
      "consumption": 25.0
    },
    "reading_details": {
      "date": "2024-01-20",
      "reader": "John Doe",
      "condition": "good",
      "accuracy": "exact",
      "notes": "Meter condition good, reading clear"
    },
    "receipt_data": {
      "receipt_number": "MR-000123",
      "date": "2024-01-20",
      "time": "10:30:00",
      "customer": {
        "name": "John Smith",
        "connection_number": "WB001",
        "address": "123 Main Street, Colombo 03",
        "phone": "0771234567"
      },
      "meter": {
        "meter_number": "MTR001",
        "type": "digital",
        "location": "Front yard, near gate"
      },
      "reading": {
        "previous": 1250.5,
        "current": 1275.5,
        "consumption": 25.0,
        "units": "cubic meters"
      },
      "reader": {
        "name": "John Doe",
        "signature_line": "________________________"
      },
      "footer": {
        "company": "AquaBill by olexto",
        "note": "Thank you for your cooperation",
        "website": "www.waterbilling.com"
      }
    },
    "sync_status": "completed",
    "timestamp": "2024-01-20T10:30:00Z"
  }
}
```

**Response** (Error - 400):
```json
{
  "success": false,
  "message": "Reading cannot be less than previous reading for cumulative meters",
  "previous_reading": 1250.5,
  "submitted_reading": 1200.0
}
```

**Response** (Validation Error - 422):
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "current_reading": ["The current reading field is required."],
    "reading_date": ["The reading date must be a valid date."]
  }
}
```

### 3. Get Reading History
Get historical readings for a customer.

**Endpoint**: `GET /meter-reading/customers/{customer_id}/history`
**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `limit`: Number of readings (default: 10)
- `from_date`: Start date (YYYY-MM-DD)
- `to_date`: End date (YYYY-MM-DD)

**Response** (200):
```json
{
  "success": true,
  "data": {
    "customer": {
      "id": 1,
      "name": "John Smith",
      "connection_number": "WB001"
    },
    "meter": {
      "id": 1,
      "meter_number": "MTR001",
      "type": "digital"
    },
    "readings": [
      {
        "id": 123,
        "reading": 1250.5,
        "date": "2024-01-15",
        "consumption": 25.0,
        "reader": "John Doe",
        "status": "verified",
        "notes": "Normal reading"
      }
    ]
  }
}
```

---

## 📈 Statistics & Performance

### 1. Get Statistics
Get reading statistics for current user.

**Endpoint**: `GET /meter-reading/stats`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "success": true,
  "data": {
    "today": {
      "readings_completed": 15,
      "customers_visited": 15
    },
    "this_month": {
      "total_readings": 350,
      "total_consumption": "8750.25"
    },
    "performance": {
      "average_readings_per_day": "11.7",
      "accuracy_rate": 98.5
    }
  }
}
```

### 2. Get Recent Readings
Get recent readings submitted by current user.

**Endpoint**: `GET /meter-reading/readings/recent`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "customer_name": "John Smith",
      "connection_number": "WB001",
      "meter_number": "MTR001",
      "reading": 1250.5,
      "consumption": 25.0,
      "date": "2024-01-15",
      "status": "completed",
      "submitted_via": "mobile"
    }
  ]
}
```

---

## 🔄 Offline Sync Support

### 1. Bulk Sync Readings
Sync multiple readings (for offline mode).

**Endpoint**: `POST /meter-reading/bulk-sync`
**Headers**: `Authorization: Bearer {token}`

**Request**:
```json
{
  "readings": [
    {
      "customer_id": 1,
      "meter_id": 1,
      "current_reading": 1275.5,
      "reading_date": "2024-01-20",
      "gps_latitude": 6.9271,
      "gps_longitude": 79.8612,
      "notes": "Bulk sync reading",
      "meter_condition": "good",
      "reading_accuracy": "exact",
      "offline_timestamp": "2024-01-20T10:30:00Z"
    }
  ]
}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Bulk sync completed",
  "data": {
    "total_readings": 1,
    "successful": 1,
    "failed": 0,
    "results": [
      {
        "index": 0,
        "status": "success",
        "data": {
          "reading_id": 123,
          "customer": "John Smith",
          "meter_number": "MTR001"
        }
      }
    ],
    "timestamp": "2024-01-20T10:30:00Z"
  }
}
```

### 2. Get Sync Status
Check pending sync data.

**Endpoint**: `GET /sync/pending`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "success": true,
  "data": {
    "pending_uploads": 0,
    "last_sync": "2024-01-20T10:30:00Z",
    "sync_status": "up_to_date"
  }
}
```

### 3. Force Sync
Force synchronization of all data.

**Endpoint**: `POST /sync/force`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "success": true,
  "message": "Sync completed successfully",
  "data": {
    "synced_at": "2024-01-20T10:30:00Z",
    "items_synced": 0
  }
}
```

---

## 🛠️ Utility Endpoints

### 1. Health Check
Check API availability (no auth required).

**Endpoint**: `GET /health`

**Response** (200):
```json
{
  "status": "ok",
  "version": "1.0.0",
  "timestamp": "2024-01-20T10:30:00Z",
  "server": "AquaBill by olexto API"
}
```

### 2. App Info
Get application information (no auth required).

**Endpoint**: `GET /app-info`

**Response** (200):
```json
{
  "app_name": "AquaBill Mobile",
  "version": "1.0.0",
  "api_version": "v1",
  "features": {
    "offline_mode": true,
    "photo_capture": true,
    "gps_tracking": true,
    "receipt_printing": true,
    "auto_sync": true
  },
  "contact": {
    "support_email": "support@waterbilling.com",
    "website": "https://waterbilling.com"
  }
}
```

### 3. System Info
Get system information.

**Endpoint**: `GET /utils/system-info`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "success": true,
  "data": {
    "server_time": "2024-01-20T10:30:00Z",
    "timezone": "Asia/Colombo",
    "app_version": "1.0.0",
    "api_version": "v1",
    "maintenance_mode": false
  }
}
```

---

## ⚠️ Error Handling

### Common HTTP Status Codes
- **200**: Success
- **401**: Unauthorized (invalid or expired token)
- **403**: Forbidden (insufficient permissions)
- **404**: Not Found (resource doesn't exist)
- **422**: Validation Error (invalid input data)
- **500**: Internal Server Error

### Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### Common Error Examples

**401 Unauthorized**:
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

**403 Forbidden**:
```json
{
  "success": false,
  "message": "Unauthorized access. Meter reader permissions required."
}
```

**422 Validation Error**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "current_reading": ["The current reading field is required."],
    "reading_date": ["The reading date must be a valid date."]
  }
}
```

**404 Not Found**:
```json
{
  "success": false,
  "message": "Customer not found"
}
```

**500 Server Error**:
```json
{
  "success": false,
  "message": "Internal server error",
  "error": "Database connection failed"
}
```

---

## 🔒 Security & Authentication

### Token Management
- **Token Type**: Bearer Token (Laravel Sanctum)
- **Token Expiry**: 30 days
- **Token Scope**: `meter-reading` abilities
- **Device Binding**: Tokens are tied to device names

### Required Headers
```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Rate Limiting
- **Rate Limit**: 60 requests per minute per user
- **Rate Limit Headers**:
  - `X-RateLimit-Limit`: 60
  - `X-RateLimit-Remaining`: 59
  - `X-RateLimit-Reset`: 1642665600

---

## 📱 Mobile App Implementation Guide

### 1. App Flow
```
1. Login → Get auth token
2. Check today's route → Get assigned customers
3. Search/Scan QR → Find specific meters
4. Take readings → Submit (photo + GPS optional)
5. Print receipts → Use receipt_data
6. Sync offline readings → Bulk sync when online
```

### 2. Reading Submission Flexibility
Your system supports **multiple reading scenarios**:

✅ **Basic Reading**: Only customer_id + reading + date  
✅ **With Photo**: Add meter verification photo  
✅ **With GPS**: Add location verification  
✅ **Full Reading**: Photo + GPS + all details  
✅ **Offline Reading**: Store locally, sync later  

**All combinations work:**
- Reading only (minimum required)
- Reading + Photo
- Reading + GPS  
- Reading + Photo + GPS
- Any of above + additional notes/conditions

### 3. App Permissions
- **Camera**: Optional - for meter photo capture
- **Location**: Optional - for GPS coordinates  
- **Storage**: Required - for offline data storage
- **Network**: Required - for API communication

**Note**: Camera and Location permissions are optional since photo and GPS are not required for readings.

### 3. Offline Support
- Store readings locally when offline
- Queue for sync when connection restored
- Use `offline_timestamp` field for proper ordering
- Use bulk sync endpoint for efficient uploads

### 4. Photo Handling
- **Max Size**: 5MB per photo
- **Formats**: JPEG, PNG
- **Compression**: Recommended before upload
- **Storage**: Temporary local storage until uploaded

### 5. GPS Integration
- **Accuracy**: Within 10 meters recommended
- **Validation**: Include with every reading
- **Privacy**: Only location during reading submission
- **Fallback**: Manual location entry if GPS unavailable

---

## 🧪 Testing

### Test Accounts
Use these credentials for testing:

**Meter Reader Accounts**:
- Email: `reader1@wassip.com` to `reader50@wassip.com`
- Password: `password`

### Sample API Calls

**Login Test**:
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"reader1@wassip.com","password":"password","device_name":"Test Device"}'
```

**Search Test**:
```bash
curl -X GET "http://localhost:8000/api/v1/meter-reading/customers/search?q=John" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Submit Reading Test**:
```bash
curl -X POST http://localhost:8000/api/v1/meter-reading/submit \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"customer_id":1,"meter_id":1,"current_reading":1500.5,"reading_date":"2024-01-20","meter_condition":"good","reading_accuracy":"exact"}'
```

**QR Scan Test**:
```bash
curl -X POST http://localhost:8000/api/v1/meter-reading/qr-code/scan \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"qr_data":"MTR001"}'
```

**Minimal Reading Test** (No photo/GPS):
```bash
curl -X POST http://localhost:8000/api/v1/meter-reading/submit \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"customer_id":1,"current_reading":1500.5,"reading_date":"2024-01-20"}'
```

---

## 🎯 Quick Start Checklist

### For Mobile App Developers:

✅ **Setup Base URL** and API version  
✅ **Implement Login** with token storage  
✅ **Add Authorization Headers** to all requests  
✅ **Handle Error Responses** consistently  
✅ **Implement QR Scanner** for meter identification  
✅ **Add Camera Integration** for meter photos  
✅ **Optionally Include GPS** in readings  
✅ **Store Offline Data** for sync later  
✅ **Print Receipts** using receipt_data  
✅ **Test with Sample Accounts**  

### Key Features to Implement:
- **Flexible Reading Input** - photo and GPS optional
- **Offline Reading Storage** with sync capability
- **Optional Photo Capture** for meter verification
- **Optional GPS Services** for location verification
- **QR Code Scanning** for quick meter identification
- **Receipt Printing** for customer confirmation
- **Search Functionality** for finding customers/meters

---

*Last Updated: January 2025*  
*API Version: v1.0*  
*Documentation Version: 1.0*