-- Create Database (only if it doesn't exist)
IF DB_ID('employeemanagementsystem') IS NULL
    CREATE DATABASE employeemanagementsystem;
GO

USE employeemanagementsystem;
GO

-- Drop views first
IF OBJECT_ID('OrderDetails', 'V') IS NOT NULL
    DROP VIEW OrderDetails;
GO

IF OBJECT_ID('EmployeeWorkload', 'V') IS NOT NULL
    DROP VIEW EmployeeWorkload;
GO

-- Drop junction table first because it has foreign keys
IF OBJECT_ID('OrderAssignments', 'U') IS NOT NULL
    DROP TABLE OrderAssignments;
GO

-- Then drop Orders
IF OBJECT_ID('Orders', 'U') IS NOT NULL
    DROP TABLE Orders;
GO

-- Then Users
IF OBJECT_ID('Users', 'U') IS NOT NULL
    DROP TABLE Users;
GO

-- Finally Employee
IF OBJECT_ID('Employee', 'U') IS NOT NULL
    DROP TABLE Employee;
GO


IF OBJECT_ID('OrderDetails', 'V') IS NOT NULL
    DROP VIEW OrderDetails;
GO

IF OBJECT_ID('EmployeeWorkload', 'V') IS NOT NULL
    DROP VIEW EmployeeWorkload;
GO

IF OBJECT_ID('OrderAssignments', 'U') IS NOT NULL
    DROP TABLE OrderAssignments;
GO

IF OBJECT_ID('Orders', 'U') IS NOT NULL
    DROP TABLE Orders;
GO

IF OBJECT_ID('Users', 'U') IS NOT NULL
    DROP TABLE Users;
GO

IF OBJECT_ID('Employee', 'U') IS NOT NULL
    DROP TABLE Employee;
GO

-- EMPLOYEE TABLE
CREATE TABLE Employee (
	EmployeeId      INT PRIMARY KEY IDENTITY(1,1),
	Name            VARCHAR(225),
	Contacts        VARCHAR(225),
	Email           VARCHAR(225),
	Role            VARCHAR(225),
	Address         VARCHAR(225),
	City            VARCHAR(225),
	Designation     VARCHAR(225),
	Salary          INT
);
GO

-- INSERT RECORDS
INSERT INTO Employee (Name, Contacts, Email, Role, Address, City, Designation, Salary)
VALUES 
('Aisha Khan', '0300-1234567', 'aisha@stitchandco.com', 'Production', 'House 12, Block C', 'Karachi', 'Crochet Artisan', 55000),
('Bilal Ahmed', '0301-2345678', 'bilal@stitchandco.com', 'Operations', 'Street 5, DHA Phase 3', 'Lahore', 'Order Manager', 60000),
('Sana Malik', '0302-3456789', 'sana@stitchandco.com', 'Design', 'Flat 4B, Gulberg', 'Sahiwal', 'Designer', 50000),
('Fabia Arbab', '0321-9876757', 'fabia@stitchandco.com', 'Marketing', '57B DHA', 'Islamabad', 'Marketing Executive', 48000),
('Hamza Tariq', '0333-4567890', 'hamza@stitchandco.com', 'Finance', 'House 22, F-8', 'Islamabad', 'Accountant', 55000),
('Zara Sheikh', '0345-5678901', 'zara@stitchandco.com', 'Production', 'Street 9, Model Town', 'Lahore', 'Crochet Artisan', 42000),
('Usman Raza', '0312-6789012', 'usman@stitchandco.com', 'Logistics', 'House 3, Clifton Block 4', 'Karachi', 'Delivery Coordinator', 40000),
('Mahnoor Fatima', '0301-7890123', 'mahnoor@stitchandco.com', 'Design', 'Flat 2A, Bahria Town', 'Rawalpindi', 'Pattern Designer', 47000),
('Ali Raza Khan', '0322-8901234', 'aliraza@stitchandco.com', 'Operations', 'House 15, Johar Town', 'Sahiwal', 'Inventory Manager', 52000);

-- VIEW RECORDS
USE employeemanagementsystem;
GO

SELECT * FROM Employee;

-- ORDER TABLE

IF OBJECT_ID('Orders', 'U') IS NOT NULL
    DROP TABLE Orders;
GO
 
CREATE TABLE Orders (
    OrderId       INT IDENTITY(1001,1) PRIMARY KEY,      -- auto-increment starting at 1001
    CustomerName  VARCHAR(255) NOT NULL,
    Product       VARCHAR(255) NOT NULL,
    OrderDate     DATE NOT NULL,
    Status        VARCHAR(50) NOT NULL
                  CHECK (Status IN ('Pending','Shipped','Delivered')),  -- CHECK: only valid statuses
    Amount        DECIMAL(10,2) NOT NULL CHECK (Amount > 0)            -- CHECK: no zero/negative amount
);
GO
 
INSERT INTO Orders (CustomerName, Product, OrderDate, Status, Amount)
VALUES
('Hina Bashir', 'Amigurumi Bunny', '2026-08-10', 'Pending', 2500.00),
('Omar Farooq', 'Crossbody Bag', '2026-08-09', 'Shipped', 4800.00),
('Areeba Noor', 'Baby Blanket', '2026-08-07', 'Delivered', 6200.00),
('Talha Waseem', 'Wall Hanging', '2026-08-05', 'Delivered', 3100.00),
('Noor ul Ain', 'Baby Booties', '2026-08-03', 'Pending', 1400.00),
('Kashif Iqbal', 'Amigurumi Bear', '2026-08-01', 'Shipped', 2900.00);
GO

/* 
 USERS TABLE (login system)
 Passwords are NEVER stored as plain text — only a hash.
 */
IF OBJECT_ID('Users', 'U') IS NOT NULL
    DROP TABLE Users;
GO
 
CREATE TABLE Users (
    UserId        INT IDENTITY(1,1) PRIMARY KEY,
    Username      VARCHAR(100) UNIQUE NOT NULL,
    PasswordHash  VARCHAR(255) NOT NULL,
    Role          VARCHAR(50) DEFAULT 'admin' CHECK (Role IN ('admin','staff')),
    CreatedAt     DATETIME DEFAULT GETDATE()
);
GO

INSERT INTO Users
(
    Username,
    PasswordHash,
    Role
)
VALUES
('admin', 'hashed_password_123', 'admin'),

('staff1', 'hashed_password_456', 'staff');
GO

-- LINKING JUNCTION TABLE 

IF OBJECT_ID('OrderAssignments', 'U') IS NOT NULL
    DROP TABLE OrderAssignments;
GO
 
CREATE TABLE OrderAssignments (
    AssignmentId  INT IDENTITY(1,1) PRIMARY KEY,
    OrderId       INT NOT NULL,
    EmployeeId    INT NOT NULL,
    TaskRole      VARCHAR(100),                       -- e.g. 'Crocheting', 'Packing', 'Quality Check'
    AssignedDate  DATETIME DEFAULT GETDATE(),          -- date handling: auto-filled at insert time
    CONSTRAINT FK_Assignment_Order
        FOREIGN KEY (OrderId) REFERENCES Orders(OrderId)
        ON DELETE CASCADE,                             -- if an order is deleted, its assignments go too
    CONSTRAINT FK_Assignment_Employee
        FOREIGN KEY (EmployeeId) REFERENCES Employee(EmployeeId)
        ON DELETE CASCADE,                             -- if an employee is deleted, their assignments go too
    CONSTRAINT UQ_Order_Employee_Task
        UNIQUE (OrderId, EmployeeId, TaskRole)          -- prevents duplicate assignment rows
);
GO
 
INSERT INTO OrderAssignments (OrderId, EmployeeId, TaskRole)
VALUES
(1001, 1, 'Crocheting'),             -- Aisha
(1001, 6, 'Quality Check'),          -- Zara
(1002, 6, 'Crocheting'),
(1003, 3, 'Design'),
(1003, 2, 'Order Management'),
(1004, 8, 'Pattern Design'),
(1005, 1, 'Crocheting'),
(1006, 6, 'Crocheting'),
(1006, 7, 'Delivery Coordination');
GO


 

-- JOINS
 
-- INNER JOIN: only orders that have an assigned employee
SELECT o.OrderId, o.CustomerName, o.Product, e.Name AS EmployeeName, oa.TaskRole
FROM Orders o
INNER JOIN OrderAssignments oa ON o.OrderId = oa.OrderId
INNER JOIN Employee e ON oa.EmployeeId = e.EmployeeId
ORDER BY o.OrderId;
GO
 
-- LEFT JOIN: all orders, even ones with no assignment yet (shows NULLs)
SELECT o.OrderId, o.CustomerName, o.Status, e.Name AS AssignedEmployee
FROM Orders o
LEFT JOIN OrderAssignments oa ON o.OrderId = oa.OrderId
LEFT JOIN Employee e ON oa.EmployeeId = e.EmployeeId
ORDER BY o.OrderId;
GO
 
-- RIGHT JOIN: all employees, even ones with no assignments (shows NULL orders)
SELECT e.Name, o.OrderId, o.Product
FROM OrderAssignments oa
RIGHT JOIN Employee e ON oa.EmployeeId = e.EmployeeId
LEFT JOIN Orders o ON oa.OrderId = o.OrderId
ORDER BY e.Name;
GO
 
-- GROUP BY
 
-- Total salary cost per city
SELECT City, COUNT(*) AS EmployeeCount, SUM(Salary) AS TotalSalary
FROM Employee
GROUP BY City
ORDER BY TotalSalary DESC;
GO
 
-- Total order value per status
SELECT Status, COUNT(*) AS OrderCount, SUM(Amount) AS TotalAmount
FROM Orders
GROUP BY Status;
GO
 
-- Number of tasks handled by each employee (uses the junction table)
SELECT e.Name, COUNT(oa.AssignmentId) AS TasksHandled
FROM Employee e
LEFT JOIN OrderAssignments oa ON e.EmployeeId = oa.EmployeeId
GROUP BY e.Name
ORDER BY TasksHandled DESC;
GO
 
/*
-- UNION
-- Combines two result sets into one, removing duplicates.
*/ 
 
-- Single contact list: employee names+city AND customer names (no city, so NULL)
SELECT Name AS ContactName, City, 'Employee' AS ContactType FROM Employee
UNION
SELECT CustomerName, NULL, 'Customer' FROM Orders
ORDER BY ContactType, ContactName;
GO
 
-- ANY

SELECT Name, Designation, Salary
FROM Employee
WHERE Salary > ANY (SELECT Salary FROM Employee WHERE Role = 'Design');
GO
 
-- Employees earning more than ALL Design-department salaries (i.e. the top earner overall vs that group)
SELECT Name, Designation, Salary
FROM Employee
WHERE Salary > ALL (SELECT Salary FROM Employee WHERE Role = 'Design');
GO
 
-- VIEWS

IF OBJECT_ID('OrderDetails', 'V') IS NOT NULL
    DROP VIEW OrderDetails;
GO
 
CREATE VIEW OrderDetails AS
SELECT
    o.OrderId,
    o.CustomerName,
    o.Product,
    o.OrderDate,
    o.Status,
    o.Amount,
    e.Name AS AssignedEmployee,
    oa.TaskRole
FROM Orders o
LEFT JOIN OrderAssignments oa ON o.OrderId = oa.OrderId
LEFT JOIN Employee e ON oa.EmployeeId = e.EmployeeId;
GO
 
-- Now the app/backend can simply run:
SELECT * FROM OrderDetails ORDER BY OrderId;
GO
 
IF OBJECT_ID('EmployeeWorkload', 'V') IS NOT NULL
    DROP VIEW EmployeeWorkload;
GO
 
CREATE VIEW EmployeeWorkload AS
SELECT e.EmployeeId, e.Name, e.City, e.Designation, COUNT(oa.AssignmentId) AS TasksHandled
FROM Employee e
LEFT JOIN OrderAssignments oa ON e.EmployeeId = oa.EmployeeId
GROUP BY e.EmployeeId, e.Name, e.City, e.Designation;
GO
 
SELECT * FROM EmployeeWorkload ORDER BY TasksHandled DESC;
GO
 
-- DATES
 
-- Orders placed in the last 14 days
SELECT OrderId, CustomerName, OrderDate
FROM Orders
WHERE OrderDate >= DATEADD(DAY, -14, GETDATE());
GO
 
-- Orders grouped by month
SELECT FORMAT(OrderDate, 'yyyy-MM') AS OrderMonth, COUNT(*) AS OrdersPlaced, SUM(Amount) AS Revenue
FROM Orders
GROUP BY FORMAT(OrderDate, 'yyyy-MM')
ORDER BY OrderMonth;
GO

-- VERIFY EVERYTHING

SELECT * FROM Employee;
-- All Orders
SELECT * FROM Orders;
GO

-- All Users
SELECT
    UserId,
    Username,
    Role,
    CreatedAt
FROM Users;
GO

-- All Order Assignments
SELECT * FROM OrderAssignments;
GO

-- Order Details View
SELECT * FROM OrderDetails
ORDER BY OrderId;
GO

-- Employee Workload View
SELECT * FROM EmployeeWorkload
ORDER BY TasksHandled DESC;
GO

-- FINAL COUNTS
SELECT 'Employees' AS TableName, COUNT(*) AS TotalRecords
FROM Employee

UNION ALL

SELECT 'Orders', COUNT(*) FROM Orders

UNION ALL

SELECT 'Users', COUNT(*) FROM Users

UNION ALL

SELECT 'OrderAssignments', COUNT(*) FROM OrderAssignments;
GO
