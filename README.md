# Api Rental Mobil

## Table of contents
----
* [General info](#general-info)
* [Packages](#packages)
* [Example](#example)

## General info
----
Membuat api rental mobil menggunakan framework Laravel
	
## Packages
----
Package yang digunakan:
* Laravel Passport

## Example
----
*Register User*
----
  Endpoint untuk registrasi user.

* **URL**
  /api/v1/user/register

* **Method:**
  `POST`

* **Request Body**
  name `*required`
  email `*required`
  mobile_phone `*required`
  address `*required`
  role_id `*required`
  password `*required`
  password_confirmation `*required`

* **Success Response:**

  * **Code:** 201 Created
    **Content:** `{ "message": "Successfully register" }`
 
* **Error Response:**

  * **Code:** 400 Bad Request
    **Content:** `{ "message" : {"email": ["The email has already been taken."]} }`

  OR

  * **Code:** 400 Bad Request
    **Content:** `{ "message" : {"address": ["The address field is required."]} }`