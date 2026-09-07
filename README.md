# Stitch & Co — Management System

A web-based management system developed using PHP and MySQL. The project provides a simple and organized interface for managing system data through role-based dashboards.

## 📌 Project Overview

Stitch & Co Management System is a PHP-based web application connected to a MySQL database.

The system includes a login interface and separate dashboards for different user roles. Users can access the features available to their assigned role.

## ✨ Features

- 🔐 User Login System
- 👩‍💼 Admin Dashboard
- 👤 Staff Dashboard
- 🔑 Role-Based Access
- 🗄️ MySQL Database Integration
- 💾 Database Management using phpMyAdmin
- 🎨 Modern and Responsive User Interface
- 🖥️ Local Development using XAMPP
- 🚪 Logout Functionality

## 🛠️ Technologies Used

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP
- phpMyAdmin

## 📁 Project Structure

" ```text
stitch-and-co-management-system/
│
├── index.php
├── admindashboard.php
├── staffdashboard.php
├── logout.php
├── style.css
├── login-bg.jpg
│
├── includes/
│   ├── auth.php
│   └── db-connect.php 
└── database/
    └── SQLQuery2.sql"

## ⚙️ How to Run

### 1. Install XAMPP

Install XAMPP on your computer.

Open the XAMPP Control Panel and start:

- Apache
- MySQL

### 2. Copy the Project

Copy the project folder into the XAMPP `htdocs` directory.

 { ```text
C:\xampp\htdocs\ }

### 3. Create the Database

Open your browser and go to:
'http://localhost/phpmyadmin'

Create the required database in phpMyAdmin.

Then import the SQL file located in:
'database/SQLQuery2.sql'

### 4. Configure Database Connection

Open:
'includes/db-connect.php'

Make sure the database name, username, and password match your local MySQL configuration.

### 5. Run the Project
Start Apache and MySQL in XAMPP.

Then open your browser and go to:
'http://localhost/stitch-and-co-management-system/'

The login page should appear.

## 🗃️ Database

The project uses MySQL for storing and managing application data.

The database can be created and managed through phpMyAdmin.

The SQL database file is included in the database folder.

## 🎯 Project Purpose

This project was developed to demonstrate practical skills in:

- PHP web development
- MySQL database management
- Database connectivity
- User authentication
- Role-based access
- Front-end development
- HTML and CSS styling
- JavaScript
- XAMPP-based local development

## 👩‍💻 Developer

Fabia Arbab

BS Artificial Intelligence Student

GitHub: https://github.com/fabiia-7

LinkedIn: https://www.linkedin.com/in/fabia-arbab-557879434/

⭐ Thank you for visiting this project!

