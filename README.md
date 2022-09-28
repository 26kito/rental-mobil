# Api Rental Mobil

## Table of contents
* [General info](#general-info)
* [Packages](#packages)
* [Example](#example)

## General info
Membuat api rental mobil dengan menggunakan framework Laravel
	
## Packages
Package yang digunakan:
* Laravel Passport

## Example
<details>
<summary>Register User</summary>

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
</details>

## Setup
How to run this app on your local?
<ol>
<li>After clone this repo</li>
<li>Run this on your terminal

```
1. composer install
2. cp .env.example .env
3. change the db config based on your own db
4. php artisan key:generate
5. php artisan passport:install
```

</li>
</ol>