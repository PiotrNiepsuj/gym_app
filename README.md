# gym_app

A web application for managing a gym, its clients, memberships, group classes and equipment.

The project was developed as part of a school assignment to practice PHP programming and relational database management.

## Features

- Adding new clients
- Assigning memberships to clients
- Registering clients for group classes
- Displaying the list of clients
- Tracking active memberships
- Viewing the number of gym visits
- Identifying the most active client
- Displaying group classes and their trainers
- Checking available places in group classes
- Displaying gym equipment by room
- Generating a ranking of the most active clients
- Displaying the most expensive membership

## Technologies

- PHP
- MySQL
- HTML
- CSS

## Database

The application uses a relational MySQL database.

The database contains tables responsible for:

- Clients
- Membership types
- Purchased memberships
- Group classes
- Trainers
- Client class registrations
- Gym visits
- Rooms
- Equipment

The application uses SQL JOINs, aggregate functions, GROUP BY, HAVING and prepared statements.

## Installation

1. Install a local web server environment, such as XAMPP.
2. Start Apache and MySQL.
3. Create a database in phpMyAdmin.
4. Import the database structure and sample data.
5. Configure the database connection in `connect_db.php`.
6. Place the project files in the server's web directory.
7. Open the application in your browser.

## Project structure

```text
gym-management/
│
├── index.php
├── connect_db.php
├── style.css
├── schemat.html
└── README.md
```

## Purpose

The main purpose of this project was to gain practical experience in PHP development, SQL queries and working with relational databases.

The application demonstrates basic CRUD operations, database relationships and the implementation of business logic.

## Author

Piotr Niepsuj

Student – IT / Software Development
