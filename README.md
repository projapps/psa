# Projapps SQLite Administrator

This application is built using the new Slim Framework 4 skeleton application. It uses the latest Slim 4 with the PHP-View template renderer. It also uses the Monolog logger.

This application is basically an alternative to [phpSQLiteAdmin](http://phpsqliteadmin.sourceforge.net/) and [phpLiteAdmin](https://www.phpliteadmin.org/) whereby one can administer a SQLite database via a web-based interface.

## Install the Application

Run this command to clone the source codes

    git clone git@github.com:nghianja/psa.git

Install the Slim Framework and its dependencies

	cd psa
	composer install

## Running the Application

To run the application in development, you can run this command 

	composer start

Run this command in the application directory to run the test suite

	composer test

That's it!

## Using the Application

Home Page - localhost:8080

## Updates

Please refer to this [blog post](http://blog.projapps.com/?p=79) for updated information.

## Framework/Libraries used

* [Slim Framework](https://www.slimframework.com/)
* [DataTables](https://datatables.net/) and its [Editor](https://editor.datatables.net/)
* PDO SQLite3 Driver
* [jQuery](http://jquery.com/) and [jQuery User Interface](http://jqueryui.com/)

## Functionality

####Version 1.1
Supports the adding, updating and deleting of table data as well as the creating and dropping of tables. Only adding of column is allowed for altering table schema. What table is displayed for use depends on which user is logged in.

## Usage

Logging into demo table,
1. Click "Login" link.
2. Enter username/password as demo/demo.
3. psa_demo table will be displayed.
4. Click on "Add" button to add a new row.
5. Double-click on a field to edit.
6. Select a row and click "Delete" button to remove the row.

Logging in as administrator,
1. Click "Login" link.
2. Enter username/password as admin/admin. (This account is disabled in my online version.)
3. Admin panel will be displayed.
4. Click on table name in admin panel to edit/drop table.
5. Click on add_new_table in admin panel to create a table.
6. Click on psa_users in tables panel to add/remove a user or edit username, password and the table a user can access.
