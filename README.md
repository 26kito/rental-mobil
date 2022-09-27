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
  name `*required` <br/>
  email `*required` <br/>
  mobile_phone `*required` <br/>
  address `*required` <br/>
  role_id `*required` <br/>
  password `*required` <br/>
  password_confirmation `*required` <br/>

* **Success Response:**

  * **Code:** 201 Created <br/>
    **Content:** `{ "message": "Successfully register" }`
 
* **Error Response:**

  * **Code:** 400 Bad Request <br/>
    **Content:** `{ "message" : {"email": ["The email has already been taken."]} }`

  OR

  * **Code:** 400 Bad Request <br/>
    **Content:** `{ "message" : {"address": ["The address field is required."]} }`