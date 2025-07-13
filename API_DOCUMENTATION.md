# Water Billing Management System (WBMS) - Mobile API Documentation

## Overview
This comprehensive documentation covers all REST APIs for the Water Billing Management System mobile application. The APIs support mobile meter reading, payment collection, customer management, and comprehensive system integration.

## Base Configuration
- **Base URL**: `http://your-domain.com/api/v1`
- **Authentication**: Bearer Token (Laravel Sanctum)
- **Content-Type**: `application/json` (or `multipart/form-data` for file uploads)
- **API Version**: v1
- **Response Format**: JSON

## Quick Start
1. **Login**: `POST /login` to get authentication token
2. **Get Route**: `GET /meter-reading/route/today` to get assigned customers
3. **Submit Reading**: `POST /meter-reading/submit` to record readings
4. **Collect Payment**: `POST /payments/record` to record payments

---

## Authentication APIs

### 1. Login
Login to the mobile application and receive an authentication token.

**Endpoint**: `POST /login`

**Request Body**:
```json
{
  "email": "reader@wassip.com",
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
      "email": "reader@wassip.com",
      "role": "meter_reader",
      "permissions": {
        "can_read_meters": true,
        "can_view_customer_details": true,
        "can_take_photos": true,
        "can_add_notes": true,
        "can_view_history": false,
        "can_edit_readings": false,
        "can_generate_reports": false
      }
    },
    "app_config": {
      "app_version": "1.0.0",
      "api_version": "v1",
      "features": {
        "offline_mode": true,
        "photo_capture": true,
        "gps_tracking": true,
        "barcode_scanning": false,
        "receipt_printing": true,
        "auto_sync": true
      },
      "settings": {
        "max_photo_size": 5120,
        "auto_sync_interval": 300,
        "offline_storage_limit": 1000,
        "gps_accuracy_threshold": 10
      },
      "server_info": {
        "timezone": "Asia/Colombo",
        "datetime_format": "Y-m-d H:i:s",
        "currency": "LKR"
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
Logout from the mobile application.

**Endpoint**: `POST /logout`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### 3. Check Token
Verify if the current token is valid.

**Endpoint**: `GET /check-token`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
```json
{
  "success": true,
  "message": "Token is valid",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "reader@wassip.com",
      "role": "meter_reader"
    },
    "expires_at": "2024-02-15T10:30:00Z"
  }
}
```

### 4. User Profile
Get current user profile information.

**Endpoint**: `GET /profile`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "reader@wassip.com",
      "role": "meter_reader",
      "permissions": {
        "can_read_meters": true,
        "can_view_customer_details": true,
        "can_take_photos": true,
        "can_add_notes": true,
        "can_view_history": false,
        "can_edit_readings": false,
        "can_generate_reports": false
      },
      "last_login": "2024-01-20T10:30:00Z",
      "created_at": "2024-01-01T00:00:00Z"
    },
    "app_config": {
      "app_version": "1.0.0",
      "api_version": "v1",
      "features": {
        "offline_mode": true,
        "photo_capture": true,
        "gps_tracking": true,
        "receipt_printing": true,
        "auto_sync": true
      }
    }
  }
}
```

### 5. Update Profile
Update user profile information (limited fields for mobile).

**Endpoint**: `PUT /profile`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "name": "John Updated Doe",
  "phone": "0771234567",
  "current_password": "oldpassword",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Response** (Success - 200):
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Updated Doe",
      "email": "reader@wassip.com",
      "role": "meter_reader",
      "phone": "0771234567"
    }
  }
}
```

---

## Meter Reading APIs

### 1. Get Today's Reading Route
Get the list of customers assigned for today's meter reading route.

**Endpoint**: `GET /meter-reading/route/today`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
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
        "area": null,
        "route": null,
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
        "status": "active",
        "billing_status": "active",
        "last_sync": "2024-01-20T10:30:00Z"
      }
    ],
    "total_count": 25,
    "route_info": {
      "area": null,
      "route": null,
      "date": "2024-01-20",
      "reader": "John Doe"
    }
  },
  "timestamp": "2024-01-20T10:30:00Z"
}
```

### 2. Submit Meter Reading
Submit a new meter reading from the mobile app.

**Endpoint**: `POST /meter-reading/submit`
**Headers**: `Authorization: Bearer {token}`

**Request Body** (JSON):
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

**With Photo Upload** (multipart/form-data):
```
Content-Type: multipart/form-data

customer_id: 1
meter_id: 1
current_reading: 1275.5
reading_date: 2024-01-20
gps_latitude: 6.9271
gps_longitude: 79.8612
meter_photo: [file upload]
notes: Reading taken with photo
meter_condition: good
reading_accuracy: exact
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
        "company": "Water Billing Management System",
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

### 3. Get Customer Details
Get detailed information about a specific customer.

**Endpoint**: `GET /meter-reading/customers/{customer_id}`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
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
      "area": null,
      "route": null,
      "status": "active",
      "billing_status": "active"
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
    ],
    "billing_info": {
      "last_bill_date": "2024-01-01",
      "last_bill_amount": 1500.00,
      "payment_status": "paid",
      "outstanding_balance": 0.00
    },
    "sync_status": "completed",
    "timestamp": "2024-01-20T10:30:00Z"
  }
}
```

### 4. Search Customers
Search for customers by various criteria.

**Endpoint**: `GET /meter-reading/customers/search`
**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `search` or `q`: Search term (minimum 1 character)
- `area`: Filter by area (optional)
- `route`: Filter by route (optional)
- `limit`: Number of results (default: 50)

**Example**: `GET /meter-reading/customers/search?q=John&limit=10`

**Response** (Success - 200):
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
        "meter_number": "MTR001",
        "status": "active"
      }
    ],
    "total_count": 1,
    "search_term": "John",
    "timestamp": "2024-01-20T10:30:00Z"
  }
}
```

### 5. Get Meter History
Get meter reading history for a specific customer.

**Endpoint**: `GET /meter-reading/customers/{customer_id}/history`
**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `limit`: Number of readings to return (default: 10)
- `from_date`: Start date (YYYY-MM-DD format)
- `to_date`: End date (YYYY-MM-DD format)

**Response** (Success - 200):
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
    ],
    "statistics": {
      "total_readings": 12,
      "average_consumption": 24.5,
      "highest_consumption": 35.0,
      "lowest_consumption": 15.0
    },
    "timestamp": "2024-01-20T10:30:00Z"
  }
}
```

### 6. Get Recent Readings
Get recent readings submitted by the current user.

**Endpoint**: `GET /meter-reading/readings/recent`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
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

### 7. Get Statistics
Get performance statistics for the current user.

**Endpoint**: `GET /meter-reading/stats`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
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

### 8. Bulk Sync Readings
Sync multiple readings at once (for offline mode).

**Endpoint**: `POST /meter-reading/bulk-sync`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
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

**Response** (Success - 200):
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
        "customer_id": 1,
        "status": "success",
        "reading_id": 123,
        "message": "Reading synced successfully"
      }
    ],
    "timestamp": "2024-01-20T10:30:00Z"
  }
}
```

---

## QR Code APIs

### 1. Generate QR Code
Generate a QR code for a specific meter.

**Endpoint**: `POST /meter-reading/qr-code/generate`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "meter_id": 1,
  "size": 200,
  "format": "png"
}
```

**Response** (Success - 200):
```json
{
  "success": true,
  "message": "QR code generated successfully",
  "data": {
    "meter_id": 1,
    "meter_number": "MTR001",
    "customer_name": "John Smith",
    "qr_code_url": "http://example.com/qr-code/1.png",
    "qr_code_base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
    "qr_code_data": "WBMS-MTR001-1",
    "download_url": "http://example.com/api/v1/meter-reading/qr-code/download/1"
  }
}
```

### 2. Scan QR Code
Scan a QR code and get meter details.

**Endpoint**: `POST /meter-reading/qr-code/scan`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "qr_data": "WBMS-MTR001-1"
}
```

**Response** (Success - 200):
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
    "latest_reading": {
      "reading": 1250.5,
      "date": "2024-01-15",
      "reader": "John Doe"
    }
  }
}
```

### 3. Download QR Code
Download a QR code image for a specific meter.

**Endpoint**: `GET /meter-reading/qr-code/download/{meter_id}`
**Headers**: `Authorization: Bearer {token}`

**Response**: PNG image file

### 4. Batch Generate QR Codes
Generate QR codes for multiple meters.

**Endpoint**: `POST /meter-reading/qr-code/batch-generate`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "meter_ids": [1, 2, 3],
  "size": 200,
  "format": "png"
}
```

**Response** (Success - 200):
```json
{
  "success": true,
  "message": "Batch QR code generation completed",
  "data": {
    "total_requested": 3,
    "successful": 3,
    "failed": 0,
    "results": [
      {
        "meter_id": 1,
        "meter_number": "MTR001",
        "qr_code_url": "http://example.com/qr-code/1.png",
        "status": "success"
      }
    ]
  }
}
```

---

## Payment APIs

### 1. Get Customer Bills
Get bills for a specific customer that need payment.

**Endpoint**: `GET /payments/customer/{customer_id}/bills`
**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `status`: Filter by bill status (optional: generated, sent, overdue, paid)

**Response** (Success - 200):
```json
{
  "success": true,
  "data": {
    "customer": {
      "id": 1,
      "name": "John Smith",
      "connection_number": "WB001",
      "address": "123 Main Street, Colombo 03",
      "phone": "0771234567"
    },
    "bills": [
      {
        "id": 1,
        "bill_number": "BILL-2024-001",
        "bill_date": "2024-01-01",
        "due_date": "2024-01-31",
        "billing_period": "December 2023",
        "previous_reading": 1200.0,
        "current_reading": 1250.5,
        "consumption": 50.5,
        "water_charges": 1200.00,
        "fixed_charges": 150.00,
        "late_fees": 0.00,
        "total_amount": 1350.00,
        "paid_amount": 0.00,
        "balance_amount": 1350.00,
        "status": "generated",
        "is_overdue": false,
        "days_overdue": 0
      }
    ],
    "summary": {
      "total_bills": 1,
      "total_amount": 1350.00,
      "total_paid": 0.00,
      "total_balance": 1350.00,
      "overdue_count": 0,
      "overdue_amount": 0.00
    }
  }
}
```

### 2. Record Payment
Record a payment for one or more bills.

**Endpoint**: `POST /payments/record`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "customer_id": 1,
  "bills": [
    {
      "bill_id": 1,
      "amount": 1350.00
    }
  ],
  "payment_method": "cash",
  "payment_date": "2024-01-20",
  "reference_number": "PAY-001",
  "notes": "Full payment received",
  "collected_by": "John Doe"
}
```

**Response** (Success - 200):
```json
{
  "success": true,
  "message": "Payment recorded successfully",
  "data": {
    "payment_id": 1,
    "customer": {
      "name": "John Smith",
      "connection_number": "WB001",
      "address": "123 Main Street, Colombo 03",
      "phone": "0771234567"
    },
    "payment_details": {
      "total_amount": 1350.00,
      "payment_method": "cash",
      "payment_date": "2024-01-20",
      "reference_number": "PAY-001",
      "collected_by": "John Doe"
    },
    "bills_paid": [
      {
        "bill_id": 1,
        "bill_number": "BILL-2024-001",
        "amount_paid": 1350.00,
        "remaining_balance": 0.00,
        "status": "paid"
      }
    ],
    "receipt_data": {
      "receipt_number": "RCP-2024-001",
      "date": "2024-01-20",
      "time": "10:30:00",
      "customer": {
        "name": "John Smith",
        "connection_number": "WB001",
        "address": "123 Main Street, Colombo 03",
        "phone": "0771234567"
      },
      "payment": {
        "amount": 1350.00,
        "method": "cash",
        "reference": "PAY-001"
      },
      "bills": [
        {
          "bill_number": "BILL-2024-001",
          "amount": 1350.00,
          "period": "December 2023"
        }
      ],
      "collector": {
        "name": "John Doe",
        "signature_line": "________________________"
      },
      "company": {
        "name": "Water Board Management System",
        "address": "Your Company Address",
        "phone": "Your Company Phone"
      }
    }
  }
}
```

### 3. Get Payment History
Get payment history for a specific customer.

**Endpoint**: `GET /payments/customer/{customer_id}/history`
**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `limit`: Number of payments to return (default: 10)
- `from_date`: Start date (YYYY-MM-DD format)
- `to_date`: End date (YYYY-MM-DD format)

**Response** (Success - 200):
```json
{
  "success": true,
  "data": {
    "customer": {
      "id": 1,
      "name": "John Smith",
      "connection_number": "WB001"
    },
    "payments": [
      {
        "id": 1,
        "amount": 1350.00,
        "payment_method": "cash",
        "payment_date": "2024-01-20",
        "reference_number": "PAY-001",
        "collected_by": "John Doe",
        "bills_paid": [
          {
            "bill_number": "BILL-2024-001",
            "amount": 1350.00,
            "period": "December 2023"
          }
        ]
      }
    ],
    "summary": {
      "total_payments": 1,
      "total_amount": 1350.00,
      "average_payment": 1350.00,
      "payment_methods": {
        "cash": 1350.00,
        "bank_transfer": 0.00,
        "cheque": 0.00
      }
    }
  }
}
```

### 4. Search Customers for Payment
Search for customers who need to make payments.

**Endpoint**: `GET /payments/customers/search`
**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `search` or `q`: Search term (minimum 1 character)
- `status`: Filter by payment status (optional: pending, overdue, paid)
- `limit`: Number of results (default: 50)

**Response** (Success - 200):
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
        "pending_bills": 1,
        "total_balance": 1350.00,
        "overdue_amount": 0.00,
        "last_payment_date": "2024-01-20"
      }
    ],
    "total_count": 1,
    "search_term": "John"
  }
}
```

---

## Sync Management APIs

### 1. Get Pending Sync Data
Get information about pending synchronization data.

**Endpoint**: `GET /sync/pending`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
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

### 2. Force Sync
Force synchronization of all data.

**Endpoint**: `POST /sync/force`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
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

## Utility APIs

### 1. Get Areas
Get available areas for filtering (placeholder for future implementation).

**Endpoint**: `GET /utils/areas`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
```json
{
  "success": true,
  "data": [],
  "message": "Area functionality not implemented yet"
}
```

### 2. Get Routes
Get available routes for filtering (placeholder for future implementation).

**Endpoint**: `GET /utils/routes`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
```json
{
  "success": true,
  "data": [],
  "message": "Route functionality not implemented yet"
}
```

### 3. Get System Info
Get system information and server status.

**Endpoint**: `GET /utils/system-info`
**Headers**: `Authorization: Bearer {token}`

**Response** (Success - 200):
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

## Health Check APIs

### 1. Health Check
Check API health and availability.

**Endpoint**: `GET /health`

**Response** (Success - 200):
```json
{
  "status": "ok",
  "version": "1.0.0",
  "timestamp": "2024-01-20T10:30:00Z",
  "server": "Water Billing Management System API"
}
```

### 2. App Info
Get application information and supported features.

**Endpoint**: `GET /app-info`

**Response** (Success - 200):
```json
{
  "app_name": "WBMS Mobile",
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

---

## Error Handling

All API endpoints return consistent error responses:

### Common Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### HTTP Status Codes
- **200**: Success
- **201**: Created
- **400**: Bad Request
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **422**: Validation Error
- **500**: Internal Server Error

### Example Error Responses

**401 Unauthorized**:
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

**422 Validation Error**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 6 characters."]
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

---

## Authentication & Security

### Token Management
- **Token Type**: Bearer Token (Laravel Sanctum)
- **Token Expiry**: 30 days
- **Token Refresh**: Use `/refresh` endpoint
- **Token Revocation**: Use `/logout` endpoint

### Security Headers
```
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

## Mobile App Integration

### Offline Mode Support
- Cache customer data locally
- Queue readings for sync when online
- Store photos locally until sync
- Use `/sync/pending` to check sync status

### GPS Integration
- Include GPS coordinates in readings
- Validate location accuracy
- Track reader movements

### Photo Capture
- Maximum photo size: 5MB
- Supported formats: JPEG, PNG
- Compress images before upload
- Use multipart/form-data for uploads

### Receipt Printing
- Use `receipt_data` from responses
- Format for thermal printers
- Include QR codes for verification

---

## Database Reset Command

To reset the database while preserving system users, use:

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

---

## Testing

### Test Accounts
After database reset, use these credentials:

**Admin Account**:
- Email: `admin@wassip.com`
- Password: `password`

**Test Meter Readers**:
- Email: `reader1@wassip.com` to `reader50@wassip.com`
- Password: `password`

**Test Staff**:
- Email: `staff1@wassip.com` to `staff30@wassip.com`
- Password: `password`

**Test Managers**:
- Email: `manager1@wassip.com` to `manager20@wassip.com`
- Password: `password`

### Sample API Requests

**Login Test**:
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"reader1@wassip.com","password":"password","device_name":"Test Device"}'
```

**Get Route Test**:
```bash
curl -X GET http://localhost:8000/api/v1/meter-reading/route/today \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Submit Reading Test**:
```bash
curl -X POST http://localhost:8000/api/v1/meter-reading/submit \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"customer_id":1,"meter_id":1,"current_reading":1500.5,"reading_date":"2024-01-20","meter_condition":"good","reading_accuracy":"exact"}'
```

---

## Support & Maintenance

### API Versioning
- Current Version: `v1`
- Base URL: `/api/v1`
- Backward Compatibility: Maintained for major versions

### Monitoring
- All API calls are logged
- Activity tracking for security
- Performance metrics available

### Contact
- Technical Support: `support@waterbilling.com`
- Documentation: This document
- System Status: Check `/health` endpoint

---

## Changelog

### Version 1.0.0 (Current)
- Initial API implementation
- Authentication system
- Meter reading functionality
- Payment collection
- QR code support
- Offline sync capabilities
- Comprehensive error handling
- Database reset with user preservation

### Planned Features
- Area and route management
- Advanced reporting
- Push notifications
- Multi-language support
- Advanced analytics
- Bulk operations
- API documentation UI

---

*Last Updated: January 2024*
*API Version: v1.0.0*
*Document Version: 1.0.0* 