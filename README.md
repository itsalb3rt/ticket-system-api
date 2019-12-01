Ticket system API of the progressive web application to manage tickets, users, multi-user assignment and ticket status tracking. Developed in Vuejs and PHP, using MySQL database.

## Index
- [Features](#features)
- [Docker implementation](#docker)
  - [Back-End](#docker-back-end)
- [Development](#development)
  - [Back-End](#back-end)
  - [Database](#database)
## Features

**Users roles**

The users has roles, users and admins, the main difference is when admin enter in employees area he can desactive and edit users but the normal users cannot desactive others users or edit.

**Ticket Status**

- Open (when ticket is create)
- Close

**Users Status**

Users cannot be deleted
- Active
- Inactive

**Create ticket**

Users can create ticket indicate the subject and a description.

**Tickets time entries**

Users can register the time working on a ticket, after, you can generate a report of hours user working. The normal users (role “user”) only can delete you own entry, the admin role can delete any entry on in any tickets.

**Tickets Notes**

All users can create notes in tickets, this is like a "comments" for more information by the users resolved a tickets.


## Docker

### Docker Back-End

1 – Clone this repository
```bash
$ https://github.com/itsalb3rt/ticket-system-api.git
```
2 – Run
```bash
docker-compose up –build
```
:tada: Now you API is running on http://localhost:5018/

## Development

To play with the code just follow the instructions below

### Back-End

The API is developed in PHP with some Symfony components and third-party libraries for email validation and token generation. You can check the API documentation in the following link [**POSTMANDOCS**](https://documenter.getpostman.com/view/5796243/SWDzeLgP?version=latest)

- [Ligne PHP (Framework)](https://ligne-framework.gitbook.io/ligne-framework-php/)
    - PHP Framework
- [HTTPFoundation](https://symfony.com/doc/current/components/http_foundation.html)
    - The HttpFoundation component defines an object-oriented layer for the HTTP specification.
- [Tokenista](https://packagist.org/packages/ingenerator/tokenista)
    - Generate unique token
- [Email Validator](https://packagist.org/packages/egulias/email-validator)


1 – Clone this repository in your php environment
```bash
$ https://github.com/itsalb3rt/ticket-system-api.git

```
2 – Install all dependencies
```bash
$ composer install
```

### Database

The data base schema is on `etc/db` directory, import the file `ticket_system.sql` on you `MySQL` database.

---

I hope everything goes according to plan, I wish you a happy day, afternoon, night!