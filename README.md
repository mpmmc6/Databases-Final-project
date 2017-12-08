# Databases-Final-project
By:
Matt McCarthy
Henry Heinze
Mike Kohlberg

Description:

This website is to be used tracking entries and exits for a some kind of secure facility; we wrote ours for a datacenter. We modeled this after the system that is used to track entry and exit to the campus datacenter that Henry goes to for work. For this there is no password, which we decided because all users in the system are already pre-authorized to be there and facilities this would be used in would be manned as well, making password authentication redundant. In a large deployment, the users table would search a domain, but for our purposes, it searches a users table. If the user isn't found in the table, he/she can register themself as a user. The user puts their pawprint, the reason they are at the datacenter (racking servers, replacing hard drives, etc) and the equipment they will be working on (web server, SQL Server, etc) to track changes that could cause later problems. Once in the table, this information is displayed on the home page and the user has the option to sign out or delete their record. On sign out, the app populates the signed out field with the date and time when the user signed out. Once signed out the user can't sign back in on the same record, and would need to create a new record, to keep up security.

Table Definitions:

DCusers:
+-----------+--------------+------+-----+---------+-------+
| Field     | Type         | Null | Key | Default | Extra |
+-----------+--------------+------+-----+---------+-------+
| DCuserID  | varchar(32)  | NO   | PRI | NULL    |       |
| FirstName | varchar(127) | NO   |     | NULL    |       |
| addDate   | datetime     | YES  |     | NULL    |       |
+-----------+--------------+------+-----+---------+-------+

Signing
+-------------------+-------------+------+-----+---------+----------------+
| Field             | Type        | Null | Key | Default | Extra          |
+-------------------+-------------+------+-----+---------+----------------+
| id                | int(11)     | NO   | PRI | NULL    | auto_increment |
| userID            | varchar(32) | NO   |     | NULL    |                |
| reason            | mediumtext  | YES  |     | NULL    |                |
| affectedEquipment | mediumtext  | YES  |     | NULL    |                |
| addDate           | datetime    | NO   |     | NULL    |                |
| outDate           | datetime    | YES  |     | NULL    |                |
+-------------------+-------------+------+-----+---------+----------------+

CRUD:
CREATE: The user creates their record when they sign in with their username, their reason for being there, and the equipment theyre working on
READ: The home page reads and presents the list of records from the database, you can also search by pawprint and that will show only the records for that pawprint.
UPDATE: The sign out button updates the database with the out date for that record.
DELETE: The delete button on the record allows the user to remove that record from the database.

Video Demonstration: https://www.youtube.com/watch?v=EqrnTPbVUCg&feature=youtu.be
