# Training / HRD API Documentation

Complete reference for all Training API endpoints — parameters, request bodies, and responses.

---

## Contents

1. [Common rules](#common-rules)
2. [Endpoint list](#endpoint-list)
3. [Create Project](#1-create-project)
4. [Cancel Project](#2-cancel-project)
5. [Get Transaction](#3-get-transaction)
6. [Approve Transactions](#4-approve-transactions)
7. [Get Project Detail](#5-get-project-detail)
8. [Add Date](#6-add-date)
9. [Edit Date](#7-edit-date)
10. [Remove Date](#8-remove-date)
11. [Add Time](#9-add-time)
12. [Edit Time](#10-edit-time)
13. [Remove Time](#11-remove-time)
14. [Add Participant](#12-add-participant)
15. [Remove Participant](#13-remove-participant)
16. [Add Lecturer](#14-add-lecturer)
17. [Remove Lecturer](#15-remove-lecturer)
18. [Shared error responses](#shared-error-responses)

---

## Common rules

### Base URL

```text
{APP_URL}/api
```

### Required headers

```http
Authorization: Bearer {Token_API}
Content-Type: application/json
Accept: application/json
```

| Header | Required | Value |
|---|---|---|
| `Authorization` | Yes | `Bearer` + space + `Token_API` from `.env` |
| `Content-Type` | Yes for POST | `application/json` |
| `Accept` | Recommended | `application/json` |

### Date / time formats

| Type | Format | Example |
|---|---|---|
| Date | `Y-m-d` | `2026-08-25` |
| Time | `H:i` | `09:00`, `13:30` |
| Datetime | `Y-m-d H:i:s` | `2026-08-01 08:00:00` |

### Project type options

| Value | Meaning |
|---|---|
| `single` | Register once |
| `multiple` | Can register more than once |
| `attendance` | Attendance-only |

### Boolean options

Send JSON booleans: `true` or `false`

### User lookup note

For lecturer APIs (and create-project lecturers):

1. Find employee in local DB by `userid`
2. If missing → call staff API and auto-create user
3. If still not found → that user fails / is skipped

---

## Endpoint list

| # | Method | Path | Purpose |
|---|---|---|---|
| 1 | `POST` | `/api/create-project` | Create project + dates/times + users + lecturers |
| 2 | `POST` | `/api/cancel-project` | Delete project and related data |
| 3 | `GET` | `/api/get-transaction` | List attendance by date/time |
| 4 | `POST` | `/api/approve-transactions` | Approve one or many attendance records |
| 5 | `GET` | `/api/project-detail` | Full project detail |
| 6 | `POST` | `/api/date/add` | Add date (optional times) |
| 7 | `POST` | `/api/date/edit` | Edit date fields |
| 8 | `POST` | `/api/date/remove` | Soft-remove date |
| 9 | `POST` | `/api/time/add` | Add time slot |
| 10 | `POST` | `/api/time/edit` | Edit time slot |
| 11 | `POST` | `/api/time/remove` | Soft-remove time slot |
| 12 | `POST` | `/api/participant/add` | Add participants to a time |
| 13 | `POST` | `/api/participant/remove` | Soft-remove participant |
| 14 | `POST` | `/api/lecturer/add` | Add lecturers to a date |
| 15 | `POST` | `/api/lecturer/remove` | Soft-remove lecturer |

---

## 1. Create Project

`POST /api/create-project`

Creates a project, one time per date, registers `users` into every time, and adds `lecturers` to each date.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `document_id` | string / number / null | Optional | any | Saved as `dms_id` |
| `type` | string | **Required** | `single` \| `multiple` \| `attendance` | — |
| `title` | string | **Required** | any text | Project name |
| `detail` | string / null | Optional | any text | Can omit or `null` |
| `project_start_register` | datetime | **Required** | `Y-m-d H:i:s` | Registration open |
| `project_end_register` | datetime | **Required** | `Y-m-d H:i:s` | Registration close |
| `users` | string[] | **Required** | employee IDs | Registered into every created time |
| `lecturers` | string[] | Optional | employee IDs | Added to **every** created date |
| `dates` | object[] | Optional | date objects | If omitted → project with no dates |
| `dates[].dateString` | date | **Required if dates sent** | `Y-m-d` | Training date |
| `dates[].start_time` | time | **Required if dates sent** | `H:i` | Slot start |
| `dates[].end_time` | time | **Required if dates sent** | `H:i` | Slot end |
| `dates[].lecturers` | string[] | Optional | employee IDs | Overrides top-level `lecturers` for that date |

### Lecturer options

| Option | Behavior |
|---|---|
| Top-level `lecturers` | Same lecturers on every date |
| `dates[].lecturers` | Lecturers only for that date (overrides top-level) |
| Omit both | No lecturers created |

### Fixed system values (not sendable)

| Field | Fixed value |
|---|---|
| `project_seat_assign` | `false` |
| `project_group_assign` | `false` |
| `project_register_today` | `false` |
| date title | auto Thai date from `dateString` |
| time title | auto `"start - end"` |
| `time_limit` | `true` |
| `time_max` | `count(users)` |

### Request — lecturers for all dates

```json
{
  "document_id": "DMS-12345",
  "type": "single",
  "title": "หลักสูตรความปลอดภัย",
  "detail": "รายละเอียดโครงการ",
  "project_start_register": "2026-08-01 08:00:00",
  "project_end_register": "2026-08-20 17:00:00",
  "users": ["650001", "650002", "650003"],
  "lecturers": ["640100", "640101"],
  "dates": [
    {
      "dateString": "2026-08-25",
      "start_time": "09:00",
      "end_time": "12:00"
    },
    {
      "dateString": "2026-08-26",
      "start_time": "13:00",
      "end_time": "16:00"
    }
  ]
}
```

### Request — different lecturers per date

```json
{
  "document_id": "DMS-12345",
  "type": "single",
  "title": "หลักสูตรความปลอดภัย",
  "detail": null,
  "project_start_register": "2026-08-01 08:00:00",
  "project_end_register": "2026-08-20 17:00:00",
  "users": ["650001", "650002"],
  "dates": [
    {
      "dateString": "2026-08-25",
      "start_time": "09:00",
      "end_time": "12:00",
      "lecturers": ["640100"]
    },
    {
      "dateString": "2026-08-26",
      "start_time": "13:00",
      "end_time": "16:00",
      "lecturers": ["640101", "640102"]
    }
  ]
}
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Project created successfully!",
  "project": {
    "id": 1,
    "dms_id": "DMS-12345",
    "project_type": "single",
    "project_name": "หลักสูตรความปลอดภัย",
    "project_detail": "รายละเอียดโครงการ",
    "project_seat_assign": false,
    "project_group_assign": false,
    "project_start_register": "2026-08-01T08:00:00.000000Z",
    "project_end_register": "2026-08-20T17:00:00.000000Z",
    "project_register_today": false,
    "project_active": true,
    "project_delete": false,
    "created_at": "2026-08-17T04:00:00.000000Z",
    "updated_at": "2026-08-17T04:00:00.000000Z"
  },
  "dates": [
    {
      "date_id": 10,
      "date_title": "25 สิงหาคม 2569",
      "date_datetime": "2026-08-25",
      "time_id": 20,
      "time_title": "09:00 - 12:00",
      "time_start": "09:00",
      "time_end": "12:00",
      "lecturers": [
        {
          "lecture_id": 5,
          "userid": "640100",
          "name": "วิทยากร ตัวอย่าง"
        },
        {
          "lecture_id": 6,
          "userid": "640101",
          "name": "วิทยากร คนที่สอง"
        }
      ]
    },
    {
      "date_id": 11,
      "date_title": "26 สิงหาคม 2569",
      "date_datetime": "2026-08-26",
      "time_id": 21,
      "time_title": "13:00 - 16:00",
      "time_start": "13:00",
      "time_end": "16:00",
      "lecturers": [
        {
          "lecture_id": 7,
          "userid": "640100",
          "name": "วิทยากร ตัวอย่าง"
        },
        {
          "lecture_id": 8,
          "userid": "640101",
          "name": "วิทยากร คนที่สอง"
        }
      ]
    }
  ]
}
```

### Response fields

| Field | Type | Description |
|---|---|---|
| `success` | boolean | Always `true` on success |
| `message` | string | Status message |
| `project` | object | Created project row |
| `dates` | array | Created dates/times/lecturers summary |
| `dates[].date_id` | integer | New date id |
| `dates[].date_title` | string | Thai date title |
| `dates[].date_datetime` | date | `Y-m-d` |
| `dates[].time_id` | integer | New time id |
| `dates[].time_title` | string | Time title |
| `dates[].time_start` | time | `H:i` |
| `dates[].time_end` | time | `H:i` |
| `dates[].lecturers` | array | Lecturers created for that date |
| `dates[].lecturers[].lecture_id` | integer | New lecture id |
| `dates[].lecturers[].userid` | string | Employee id |
| `dates[].lecturers[].name` | string | Employee name |

### Error responses

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 2. Cancel Project

`POST /api/cancel-project`

Permanently deletes project and related dates/times/attends/results/links/groups/seats.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `project_id` | integer | **Required** | existing `hr_projects.id` | — |

### Request

```json
{
  "project_id": 1
}
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Project cancelled successfully!",
  "project": {
    "id": 1,
    "dms_id": "DMS-12345",
    "project_type": "single",
    "project_name": "หลักสูตรความปลอดภัย",
    "project_detail": "รายละเอียดโครงการ",
    "project_seat_assign": false,
    "project_group_assign": false,
    "project_start_register": "2026-08-01T08:00:00.000000Z",
    "project_end_register": "2026-08-20T17:00:00.000000Z",
    "project_register_today": false,
    "project_active": true,
    "project_delete": false,
    "created_at": "2026-08-17T04:00:00.000000Z",
    "updated_at": "2026-08-17T04:00:00.000000Z",
    "dates": [],
    "attends": [],
    "results": [],
    "result_header": null,
    "links": []
  }
}
```

### Error responses

**Missing project_id**

```json
{
  "success": false,
  "message": "Project ID is required!"
}
```

**Project not found — `404`**

```json
{
  "message": "No query results for model [App\\Models\\HrProject] 999"
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 3. Get Transaction

`GET /api/get-transaction`

Returns attendance grouped by date title → time title.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `project_id` | integer | **Required** | existing project id | Prefer query string |

### Request

```text
GET /api/get-transaction?project_id=1
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Transaction retrieved successfully!",
  "transaction": {
    "25 สิงหาคม 2569": {
      "09:00 - 12:00": [
        {
          "id": 101,
          "userid": "650001",
          "name": "สมชาย ใจดี",
          "attend_datetime": "09:05",
          "approve_datetime": null
        },
        {
          "id": 102,
          "userid": "650002",
          "name": "สมหญิง รักดี",
          "attend_datetime": null,
          "approve_datetime": null
        }
      ]
    },
    "26 สิงหาคม 2569": {
      "13:00 - 16:00": []
    }
  }
}
```

### Response field meaning

| Field | Type | Description |
|---|---|---|
| `transaction` | object | Key = date title |
| `transaction[date][time]` | array | Participants in that time |
| `id` | integer | `hr_attends.id` (= transaction_id / attend_id) |
| `userid` | string | Employee id |
| `name` | string | Employee name |
| `attend_datetime` | time / null | Check-in `H:i` |
| `approve_datetime` | time / null | Approve `H:i` |

### Error responses

**Missing project_id**

```json
{
  "success": false,
  "message": "Project ID is required!"
}
```

**Project not found — `404`**

```json
{
  "message": "No query results for model [App\\Models\\HrProject] 999"
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 4. Approve Transactions

`POST /api/approve-transactions`

Approves one or many attendance records in one request.

### All parameters

Choose **one** option:

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `transaction_id` | integer | Option A | existing `hr_attends.id` | Approve one |
| `transaction_ids` | integer[] | Option B | existing `hr_attends.id` list | Approve many |
| `transaction_ids[]` | integer | Option B | each must exist | — |
| `time_id` | integer | Option C | existing time id | Approve all pending in that time |
| `project_id` | integer | Option D | existing project id | Approve all pending in that project |
| `approve_all` | boolean | Optional with C/D | `true` / `false` | Default `true` |

### Request — one transaction

```json
{
  "transaction_id": 101
}
```

### Request — many transactions

```json
{
  "transaction_ids": [101, 102, 103]
}
```

### Request — all in a time

```json
{
  "time_id": 20,
  "approve_all": true
}
```

### Request — all in a project

```json
{
  "project_id": 1,
  "approve_all": true
}
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Bulk approve completed!",
  "approved": [
    {
      "transaction_id": 101,
      "approve_datetime": "2026-08-17 13:30:00"
    },
    {
      "transaction_id": 102,
      "approve_datetime": "2026-08-17 13:30:00"
    }
  ],
  "skipped": [],
  "failed": []
}
```

### Partial success response — `200`

`success` is `false` when any item is in `failed`.

```json
{
  "success": false,
  "message": "Bulk approve completed!",
  "approved": [
    {
      "transaction_id": 101,
      "approve_datetime": "2026-08-17 13:30:00"
    }
  ],
  "skipped": [
    {
      "transaction_id": 102,
      "message": "Already approved"
    }
  ],
  "failed": [
    {
      "transaction_id": 999,
      "message": "Transaction not found or deleted"
    }
  ]
}
```

### Response buckets

| Bucket | When |
|---|---|
| `approved` | Newly approved |
| `skipped` | Already had `approve_datetime` |
| `failed` | Not found / deleted |

### Error responses

**Missing option — `422`**

```json
{
  "success": false,
  "message": "Provide transaction_id, transaction_ids, or time_id/project_id with approve_all!"
}
```

**No transactions — `404`**

```json
{
  "success": false,
  "message": "No transactions found for this time!",
  "approved": [],
  "skipped": [],
  "failed": []
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 5. Get Project Detail

`GET /api/project-detail`

Returns full project info including dates, times, participants, and lecturers.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `project_id` | integer | **Required** | existing project id | Prefer query string |

### Request

```text
GET /api/project-detail?project_id=1
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Project detail retrieved successfully!",
  "project": {
    "project_id": 1,
    "dms_id": "DMS-12345",
    "project_type": "single",
    "project_name": "หลักสูตรความปลอดภัย",
    "project_detail": "รายละเอียดโครงการ",
    "project_seat_assign": false,
    "project_group_assign": false,
    "project_start_register": "2026-08-01 08:00:00",
    "project_end_register": "2026-08-20 17:00:00",
    "project_register_today": false,
    "project_active": true,
    "dates": [
      {
        "date_id": 10,
        "date_title": "25 สิงหาคม 2569",
        "date_detail": null,
        "date_location": "ห้องประชุม A",
        "date_datetime": "2026-08-25",
        "date_active": true,
        "times": [
          {
            "time_id": 20,
            "time_title": "09:00 - 12:00",
            "time_detail": null,
            "time_start": "09:00",
            "time_end": "12:00",
            "time_limit": true,
            "time_max": 30,
            "time_active": true,
            "participants": [
              {
                "attend_id": 101,
                "userid": "650001",
                "name": "สมชาย ใจดี",
                "position": "Nurse",
                "department": "OPD",
                "attend_datetime": "2026-08-25 09:05:00",
                "approve_datetime": null
              }
            ]
          }
        ],
        "lecturers": [
          {
            "lecture_id": 5,
            "userid": "640100",
            "name": "วิทยากร ตัวอย่าง",
            "position": "Instructor",
            "department": "HRD"
          }
        ]
      }
    ]
  }
}
```

### Error responses

**Missing project_id — `422`**

```json
{
  "success": false,
  "message": "Project ID is required!"
}
```

**Project not found — `404`**

```json
{
  "success": false,
  "message": "Project not found!"
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 6. Add Date

`POST /api/date/add`

Adds one date. Optionally create times in the same request.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `project_id` | integer | **Required** | existing project id | — |
| `date_datetime` | date | **Required** | `Y-m-d` | Training date |
| `date_title` | string | Optional | max 255 | Auto Thai date if omitted |
| `date_detail` | string / null | Optional | max 255 | Can omit or `null` |
| `date_location` | string / null | Optional | max 255 | Can omit or `null` |
| `times` | object[] | Optional | array | Date only if omitted |
| `times[].time_start` | time | **Required if times sent** | `H:i` | Start |
| `times[].time_end` | time | **Required if times sent** | `H:i` | End |
| `times[].time_title` | string | Optional | max 255 | Default `"HH:MM - HH:MM"` |
| `times[].time_detail` | string / null | Optional | max 255 | Can omit or `null` |
| `times[].time_limit` | boolean | Optional | `true` / `false` | Default `false` |
| `times[].time_max` | integer | Optional | `>= 0` | Used when limit on |

> Note: to add lecturers after creating a date, use `/api/lecturer/add`.

### Request — full

```json
{
  "project_id": 1,
  "date_datetime": "2026-08-27",
  "date_title": "27 สิงหาคม 2569",
  "date_detail": "วันอบรมรอบเช้า",
  "date_location": "ห้องประชุม A",
  "times": [
    {
      "time_start": "09:00",
      "time_end": "12:00",
      "time_title": "รอบเช้า",
      "time_detail": "ช่วงบรรยาย",
      "time_limit": true,
      "time_max": 30
    }
  ]
}
```

### Request — date only

```json
{
  "project_id": 1,
  "date_datetime": "2026-08-27"
}
```

### Success response — date + times — `200`

```json
{
  "success": true,
  "message": "Date added successfully!",
  "date": {
    "date_id": 11,
    "date_title": "27 สิงหาคม 2569",
    "date_detail": "วันอบรมรอบเช้า",
    "date_location": "ห้องประชุม A",
    "date_datetime": "2026-08-27",
    "times": [
      {
        "time_id": 21,
        "time_title": "รอบเช้า",
        "time_start": "09:00",
        "time_end": "12:00"
      }
    ]
  }
}
```

### Success response — date only — `200`

```json
{
  "success": true,
  "message": "Date added successfully!",
  "date": {
    "date_id": 12,
    "date_title": "28 สิงหาคม 2569",
    "date_detail": null,
    "date_location": null,
    "date_datetime": "2026-08-28",
    "times": []
  }
}
```

### Error responses

**Validation failed — `422`**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "project_id": ["The project id field is required."],
    "date_datetime": ["The date datetime field is required."]
  }
}
```

**Project not found — `404`**

```json
{
  "success": false,
  "message": "Project not found!"
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 7. Edit Date

`POST /api/date/edit`

Updates only the fields you send.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `date_id` | integer | **Required** | existing active date id | — |
| `date_title` | string | Optional | max 255 | Keep old if omitted |
| `date_detail` | string / null | Optional | max 255 | Can set `null` to clear |
| `date_location` | string / null | Optional | max 255 | Can set `null` to clear |
| `date_datetime` | date | Optional | `Y-m-d` | Auto title if title not sent |
| `date_active` | boolean | Optional | `true` / `false` | Enable / disable |

### Request — full

```json
{
  "date_id": 10,
  "date_title": "28 สิงหาคม 2569 (ปรับปรุง)",
  "date_detail": "ย้ายห้อง",
  "date_location": "ห้องประชุม B",
  "date_datetime": "2026-08-28",
  "date_active": true
}
```

### Request — change location only

```json
{
  "date_id": 10,
  "date_location": "ห้องประชุม B"
}
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Date updated successfully!",
  "date": {
    "date_id": 10,
    "date_title": "28 สิงหาคม 2569 (ปรับปรุง)",
    "date_detail": "ย้ายห้อง",
    "date_location": "ห้องประชุม B",
    "date_datetime": "2026-08-28",
    "date_active": true
  }
}
```

### Error responses

**No fields to update — `422`**

```json
{
  "success": false,
  "message": "No fields to update!"
}
```

**Date not found — `404`**

```json
{
  "success": false,
  "message": "Date not found!"
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 8. Remove Date

`POST /api/date/remove`

Soft-deletes date + all times + all participants under it.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `date_id` | integer | **Required** | existing active date id | Soft delete |

### Request

```json
{
  "date_id": 10
}
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Date removed successfully!",
  "date_id": 10
}
```

### Error responses

**Date not found — `404`**

```json
{
  "success": false,
  "message": "Date not found!"
}
```

**Server failure — `500`**

```json
{
  "success": false,
  "message": "Failed to remove date: SQLSTATE[...]"
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 9. Add Time

`POST /api/time/add`

Adds one time slot under an existing date.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `date_id` | integer | **Required** | existing active date id | — |
| `time_start` | time | **Required** | `H:i` | Start |
| `time_end` | time | **Required** | `H:i` | End |
| `time_title` | string | Optional | max 255 | Default `"HH:MM - HH:MM"` |
| `time_detail` | string / null | Optional | max 255 | Can omit or `null` |
| `time_limit` | boolean | Optional | `true` / `false` | Default `false` |
| `time_max` | integer | Optional | `>= 0` | Used when limit on |

### Request — full

```json
{
  "date_id": 10,
  "time_start": "13:00",
  "time_end": "16:00",
  "time_title": "รอบบ่าย",
  "time_detail": "Workshop",
  "time_limit": true,
  "time_max": 25
}
```

### Request — minimal

```json
{
  "date_id": 10,
  "time_start": "13:00",
  "time_end": "16:00"
}
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Time added successfully!",
  "time": {
    "time_id": 22,
    "date_id": 10,
    "time_title": "รอบบ่าย",
    "time_detail": "Workshop",
    "time_start": "13:00",
    "time_end": "16:00",
    "time_limit": true,
    "time_max": 25
  }
}
```

### Error responses

**Date not found — `404`**

```json
{
  "success": false,
  "message": "Date not found!"
}
```

**Validation failed — `422`**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "date_id": ["The date id field is required."],
    "time_start": ["The time start field is required."],
    "time_end": ["The time end field is required."]
  }
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 10. Edit Time

`POST /api/time/edit`

Updates only the fields you send.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `time_id` | integer | **Required** | existing active time id | — |
| `time_title` | string | Optional | max 255 | Keep old if omitted |
| `time_detail` | string / null | Optional | max 255 | Can set `null` to clear |
| `time_start` | time | Optional | `H:i` | Keep old if omitted |
| `time_end` | time | Optional | `H:i` | Keep old if omitted |
| `time_limit` | boolean | Optional | `true` / `false` | If `false`, `time_max` becomes `0` |
| `time_max` | integer | Optional | `>= 0` | Max seats |
| `time_active` | boolean | Optional | `true` / `false` | Enable / disable |

Auto title: if start/end changes without `time_title`, title becomes `"{start} - {end}"`.

### Request — full

```json
{
  "time_id": 20,
  "time_title": "รอบบ่าย (ขยายเวลา)",
  "time_detail": "เพิ่มเวลา workshop",
  "time_start": "13:00",
  "time_end": "17:00",
  "time_limit": true,
  "time_max": 40,
  "time_active": true
}
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Time updated successfully!",
  "time": {
    "time_id": 20,
    "date_id": 10,
    "time_title": "รอบบ่าย (ขยายเวลา)",
    "time_detail": "เพิ่มเวลา workshop",
    "time_start": "13:00",
    "time_end": "17:00",
    "time_limit": true,
    "time_max": 40,
    "time_active": true
  }
}
```

### Error responses

**No fields to update — `422`**

```json
{
  "success": false,
  "message": "No fields to update!"
}
```

**Time not found — `404`**

```json
{
  "success": false,
  "message": "Time not found!"
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 11. Remove Time

`POST /api/time/remove`

Soft-deletes one time and its participants.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `time_id` | integer | **Required** | existing active time id | Soft delete |

### Request

```json
{
  "time_id": 20
}
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Time removed successfully!",
  "time_id": 20
}
```

### Error responses

**Time not found — `404`**

```json
{
  "success": false,
  "message": "Time not found!"
}
```

**Server failure — `500`**

```json
{
  "success": false,
  "message": "Failed to remove time: SQLSTATE[...]"
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 12. Add Participant

`POST /api/participant/add`

Registers one or many employees into a time slot.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `time_id` | integer | **Required** | existing active time id | — |
| `users` | string[] | **Required** | non-empty array | Min 1 item |
| `users[]` | string | **Required** | employee id | e.g. `"650001"` |

### Request

```json
{
  "time_id": 20,
  "users": ["650001", "650002", "650010"]
}
```

### Success response — all added — `200`

```json
{
  "success": true,
  "message": "Participant processing completed!",
  "added": [
    {
      "attend_id": 201,
      "userid": "650001",
      "name": "สมชาย ใจดี"
    },
    {
      "attend_id": 202,
      "userid": "650002",
      "name": "สมหญิง รักดี"
    }
  ],
  "skipped": [],
  "failed": []
}
```

### Partial success response — `200`

```json
{
  "success": false,
  "message": "Participant processing completed!",
  "added": [
    {
      "attend_id": 201,
      "userid": "650001",
      "name": "สมชาย ใจดี"
    }
  ],
  "skipped": [
    {
      "userid": "650002",
      "message": "Already registered for this time slot"
    }
  ],
  "failed": [
    {
      "userid": "650010",
      "message": "User not found"
    },
    {
      "userid": "650011",
      "message": "Time slot is full"
    }
  ]
}
```

### Response buckets

| Bucket | When |
|---|---|
| `added` | Newly registered |
| `skipped` | Already registered in this time |
| `failed` | User not found, or time is full |

### Error responses

**Time not found — `404`**

```json
{
  "success": false,
  "message": "Time not found!"
}
```

**Project not found — `404`**

```json
{
  "success": false,
  "message": "Project not found!"
}
```

**Validation failed — `422`**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "time_id": ["The time id field is required."],
    "users": ["The users field is required."]
  }
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 13. Remove Participant

`POST /api/participant/remove`

Soft-removes one participant. Choose **one** option.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `attend_id` | integer | Option A | existing `hr_attends.id` | Preferred |
| `time_id` | integer | Option B (with `userid`) | existing time id | Must pair with `userid` |
| `userid` | string | Option B (with `time_id`) | employee id | Must pair with `time_id` |

### Request option A

```json
{
  "attend_id": 201
}
```

### Request option B

```json
{
  "time_id": 20,
  "userid": "650001"
}
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Participant removed successfully!",
  "attend_id": 201
}
```

### Error responses

**Not enough info — `422`**

```json
{
  "success": false,
  "message": "Provide attend_id, or time_id + userid!"
}
```

**Not found — `404`**

```json
{
  "success": false,
  "message": "Participant registration not found!"
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 14. Add Lecturer

`POST /api/lecturer/add`

Adds one or many lecturers to a date.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `date_id` | integer | **Required** | existing active date id | — |
| `users` | string[] | **Required** | non-empty array | Min 1 item |
| `users[]` | string | **Required** | employee id | e.g. `"640100"` |

### Request

```json
{
  "date_id": 10,
  "users": ["640100", "640101"]
}
```

### Success response — all added — `200`

```json
{
  "success": true,
  "message": "Lecturer processing completed!",
  "added": [
    {
      "lecture_id": 5,
      "userid": "640100",
      "name": "วิทยากร ตัวอย่าง"
    },
    {
      "lecture_id": 6,
      "userid": "640101",
      "name": "วิทยากร คนที่สอง"
    }
  ],
  "skipped": [],
  "failed": []
}
```

### Partial success response — `200`

```json
{
  "success": false,
  "message": "Lecturer processing completed!",
  "added": [
    {
      "lecture_id": 5,
      "userid": "640100",
      "name": "วิทยากร ตัวอย่าง"
    }
  ],
  "skipped": [
    {
      "userid": "640101",
      "lecture_id": 3,
      "message": "Already added as lecturer for this date"
    }
  ],
  "failed": [
    {
      "userid": "640999",
      "message": "User not found"
    }
  ]
}
```

### Response buckets

| Bucket | When |
|---|---|
| `added` | Newly added |
| `skipped` | Already lecturer on this date |
| `failed` | User not found |

### Error responses

**Date not found — `404`**

```json
{
  "success": false,
  "message": "Date not found!"
}
```

**Project not found — `404`**

```json
{
  "success": false,
  "message": "Project not found!"
}
```

**Validation failed — `422`**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "date_id": ["The date id field is required."],
    "users": ["The users field is required."]
  }
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## 15. Remove Lecturer

`POST /api/lecturer/remove`

Soft-removes one lecturer. Choose **one** option.

### All parameters

| Parameter | Type | Required | Allowed values / format | Default / note |
|---|---|---|---|---|
| `lecture_id` | integer | Option A | existing `hr_lecturers.id` | Preferred |
| `date_id` | integer | Option B (with `userid`) | existing date id | Must pair with `userid` |
| `userid` | string | Option B (with `date_id`) | employee id | Must pair with `date_id` |

### Request option A

```json
{
  "lecture_id": 5
}
```

### Request option B

```json
{
  "date_id": 10,
  "userid": "640100"
}
```

### Success response — `200`

```json
{
  "success": true,
  "message": "Lecturer removed successfully!",
  "lecture_id": 5
}
```

### Error responses

**Not enough info — `422`**

```json
{
  "success": false,
  "message": "Provide lecture_id, or date_id + userid!"
}
```

**Not found — `404`**

```json
{
  "success": false,
  "message": "Lecturer not found!"
}
```

**Invalid token — `401`**

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

---

## Shared error responses

### Invalid token — `401`

```json
{
  "code": 401,
  "status": "error",
  "message": "Authorization token is invalid"
}
```

### Laravel validation fail — `422`

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "The field name field is required."
    ]
  }
}
```

### Model not found (findOrFail) — `404`

```json
{
  "message": "No query results for model [App\\Models\\HrProject] 999"
}
```

### Common custom messages

| Message | Typical HTTP | Endpoint |
|---|---|---|
| `Authorization token is invalid` | `401` | All |
| `Project ID is required!` | `200` / `422` | cancel / get-transaction / project-detail |
| `Project not found!` | `404` | project-detail / date/add / participant/add / lecturer/add |
| `Date not found!` | `404` | date/edit / date/remove / time/add / lecturer/add |
| `Time not found!` | `404` | time/edit / time/remove / participant/add |
| `No fields to update!` | `422` | date/edit / time/edit |
| `Provide attend_id, or time_id + userid!` | `422` | participant/remove |
| `Participant registration not found!` | `404` | participant/remove |
| `Provide transaction_id, transaction_ids, or time_id/project_id with approve_all!` | `422` | approve-transactions |
| `No transactions found for this time!` | `404` | approve-transactions |
| `No transactions found for this project!` | `404` | approve-transactions |
| `Already approved` | in `skipped` | approve-transactions |
| `Provide lecture_id, or date_id + userid!` | `422` | lecturer/remove |
| `Lecturer not found!` | `404` | lecturer/remove |
| `Already added as lecturer for this date` | in `skipped` | lecturer/add |
| `Already registered for this time slot` | in `skipped` | participant/add |
| `User not found` | in `failed` | participant/add / lecturer/add |
| `Time slot is full` | in `failed` | participant/add |

