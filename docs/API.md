# API Documentation

## Overview
The Scholarship Finder API provides endpoints for managing scholarships, user authentication, and bookmarks. All API responses are in JSON format.

## Base URL
```
/includes/api.php
```

## Authentication
Some endpoints require user authentication. Include session cookies with requests.

## Endpoints

### Get All Scholarships
**GET** `?action=get_all`

Retrieves all scholarships ordered by deadline.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Scholarship Name",
      "provider": "Provider Name",
      "education_level": "Bachelor",
      "field": "Engineering",
      "amount": 50000,
      "deadline": "2024-12-31",
      "eligibility": "Eligibility criteria",
      "application_link": "https://example.com",
      "scholarship_type": "Merit-based"
    }
  ]
}
```

### Get Filter Options
**GET** `?action=get_filters`

Gets available education levels and fields for filtering.

**Response:**
```json
{
  "success": true,
  "data": {
    "levels": ["Bachelor", "Master", "PhD"],
    "fields": ["Engineering", "Business", "Science"]
  }
}
```

### Search Scholarships
**GET** `?action=search`

Search and filter scholarships.

**Parameters:**
- `level` (optional) - Education level filter
- `field` (optional) - Field of study filter
- `deadline` (optional) - Minimum deadline (YYYY-MM-DD)
- `search` (optional) - Text search in title/provider

**Example:** `?action=search&level=Bachelor&field=Engineering`

### Sort by Deadline
**GET** `?action=sort_deadline`

Returns scholarships sorted by deadline (earliest first).

### Sort by Amount
**GET** `?action=sort_amount`

Returns scholarships sorted by amount (highest first).

### Add Bookmark
**POST** `?action=add_bookmark`

Adds a scholarship to user's bookmarks. Requires authentication.

**Parameters:**
- `scholarship_id` - ID of scholarship to bookmark

**Response:**
```json
{
  "success": true,
  "data": {
    "message": "Bookmark added"
  }
}
```

### Remove Bookmark
**POST** `?action=remove_bookmark`

Removes a scholarship from user's bookmarks. Requires authentication.

**Parameters:**
- `scholarship_id` - ID of scholarship to unbookmark

### Check Bookmark Status
**GET** `?action=is_bookmarked&scholarship_id=X`

Checks if a scholarship is bookmarked by current user. Requires authentication.

**Response:**
```json
{
  "success": true,
  "data": {
    "is_bookmarked": true
  }
}
```

### Get User Bookmarks
**GET** `?action=get_bookmarks`

Gets all scholarships bookmarked by current user. Requires authentication.

## Error Responses
All endpoints return error responses in this format:
```json
{
  "success": false,
  "message": "Error description"
}
```

## Rate Limiting
API requests are limited to prevent abuse. Rate limits are enforced per user/IP.

## Data Types
- `id`: integer - Unique identifier
- `title`: string - Scholarship title
- `provider`: string - Organization providing scholarship
- `education_level`: string - "Bachelor", "Master", or "PhD"
- `field`: string - Field of study
- `amount`: number - Scholarship amount in PHP
- `deadline`: string - Date in YYYY-MM-DD format
- `eligibility`: string - Eligibility requirements
- `application_link`: string - URL to apply
- `scholarship_type`: string - "Merit-based", "Need-based", etc.

## Status Codes
- `200` - Success
- `400` - Bad Request (invalid parameters)
- `401` - Unauthorized (authentication required)
- `429` - Too Many Requests (rate limited)
- `500` - Internal Server Error