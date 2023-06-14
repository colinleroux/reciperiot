# RecipeRiot

## Laravel 10 API written for any application FrontEnd that consumes a RESTfull API

The API has endpoints that comply with JSON:API specifications as far as is practicable.

These are broken down into User and Recipe endpoints

## Application setup

If desired the application can allow users to register and use the API/Web frontend without verification if so desired.

Should you desire users not to register and verify their email address then an entry in the .env is required.

### Email verification option :

The default is set to true but email verification can be removed by setting it to false.

In your .env add the entry : 

`EMAIL_VERIFICATION=true` 

or

`EMAIL_VERIFICATION=false`

failure to add an entry will not cause any issues and email verification will be on
allowing users to register and use the application without further verification.

The current installation uses the standard Laravel breeze scaffolding
and spatie to provide roles and permissions for the web frontend allowing users to
perform profile tasks and recipe management if they so wish.

All functionality for users is available via the web frontend and the API.

API user functionality is custom and will not interfere with breeze - 
This is achieved by the methods in app/controller/Api/AuthController.php

The methods here are separate from the frontend so relevant JSON
responses could be implemented.

Apart from the AuthController there is an added function in the User.php model
(A good practice to include the logic for generating the verification and login
URLs within the User model)
```php
public function sendEmailVerificationNotification()
{
$this->notify(new ApiVerifyEmailNotification);
}
```
### Routing - the API

The routes available via API endpoints are outlined in the table below - each route being
stateless requires some additional input usually provided by a view so in order to test
them cater for their requirements by adding them to your requests as an app would.

### User Routes
For testing remember :
Set : Application : Headers - Accept and Content-Type : application/vnd.api+json
If using a client(Postman) use form data and include required key:values or in formatted json 
Authorization : BearerToken include token returned from login route for protected routes.

## User Management Endpoints

The table below outlines the endpoints available : their routes and related controller:methods.

**These endpoints expect certain inputs and authorisation if protected.**




| Purpose                     |                                                                   | Relevant controller:method                        |      | Endpoint                    | Key              | Value         |
|-----------------------------|-------------------------------------------------------------------|---------------------------------------------------|------|-----------------------------|------------------|---------------|
| Login                       |                                                                   | `[AuthController::class, 'login']`                | POST | `/api/login`                | email            | email address |
|                             |                                                                   |                                                   |      |                             | password         | password      |
|                             |                                                                   |                                                   |      |                             |                  |               |
| Register new user           | This will : i) send email with verification link                  | `[AuthController::class, 'register']`             | POST | `/api/register`             | email            | email address |
|                             | ii) register user                                                 |                                                   |      |                             | password         | password      |
|                             | iii) await email verification if set                              |                                                   |      |                             | confirm_password | password      |
|                             |                                                                   |                                                   |      |                             | name             | name          |
|                             |                                                                   |                                                   |      |                             |                  |               |
| Verify Email                | Need to verify registering users address from sent email          | `[AuthController::class, 'verify']`               | GET  | `/email/verify/{id}/{hash}` | id and hash      | in link sent  |
|                             | link is GET therefor user can click link and use browser          |                                                   |      |                             |                  |               |
|                             |                                                                   |                                                   |      |                             |                  |               |
|                             |                                                                   |                                                   |      |                             |                  |               |
| Lost Password – STEP 1 of 2 | Sends email link with token for use with Reset Password           | `AuthController::class, 'sendResetLinkEmail`      | POST | `/api/password/email`       | email            | email address |
|                             |                                                                   |                                                   |      |                             |                  |               |
|                             |                                                                   |                                                   |      |                             |                  |               |
| Reset Password STEP 2 of 2  | Uses link from lost password ,email and new password              |                                                   | POST | `/password/reset/{token}`   | email            | email address |
|                             | to reset user password                                            |                                                   |      |                             | new_password     | password      |
|                             |                                                                   |                                                   |      |                             | confirm_password | password      |
|                             |                                                                   |                                                   |      |                             |                  |               |
| Resend Email Verification   | If reset token has expired or original verification email is lost | `AuthController::class, 'resendVerificationEmail` | POST | email/resend-verification   | email            | email address |
|                             |                                                                   |                                                   |      |                             |                  |               |
|                             |                                                                   |                                                   |      |                             |                  |               |
| Change Password             | User knows existing wants new password                            | `AuthController::class, 'changePassword'`         | POST | `/change-password`          | current_password | password      |
|                             |                                                                   |                                                   |      |                             | new_password     | password      |
|                             |                                                                   |                                                   |      |                             | confirm_password | password      |
|                             |                                                                   |                                                   |      |                             |                  |               |
| Logout                      | User log out – deletes token                                      | `AuthController::class, 'logout'`                 |      | /logout                     | Authorization    | token         |
There are the standard user functions as expected - just for clarity please note.

Login, Register and Logout are all POST and as you would expect
Register triggers a verification email (default expiry 60 min)
