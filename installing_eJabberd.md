ejabberd can be use with MySQL in native mode instead of the ODBC generic mode. This document describes the necessary steps to get started.

# Erlang MySQL native driver #

You first need to install the native [Erlang MySQL driver](https://support.process-one.net/doc/display/CONTRIBS/Yxa). It is available in [ejabberd-modules repository](https://forge.process-one.net/browse/ejabberd-modules/mysql/trunk). You can retrieve it with the following command:

` svn co https://svn.process-one.net/ejabberd-modules/mysql/trunk mysql `

There is some precompiled Erlang beam files but there's not up-to-date : [mysql\_beam.tar.gz.](https://support.process-one.net/doc/download/attachments/415/mysql_beam.tar.gz)

You need to put the MySQL **_.beam_** files somewhere in your Erlang path (possibly with your ejabberd **_.beam_** files):

  * mysql.beam
  * mysql\_auth.beam
  * mysql\_conn.beam
  * mysql\_recv.beam

# MySQL database creation #
Before starting ejabberd, you need to have install Joomla and create database in MySQL. You probably might already have a MySQL but in case you do not have one instance already running, you can go through the following steps:

  * Download MySQL archive and uncompress it:

` > tar zxvf  mysql-max-4.1.16-pc-linux-gnu-i686-glibc23.tar.gz `

  * Go to the MySQL directory:

` > cd mysql-max-4.1.16-pc-linux-gnu-i686-glibc23`

  * Configure the database:

` > scripts/mysql_install_db `

  * Start MySQL:

` > bin/mysqld_safe & `

> Create a new user 'joomla':

```
> mysql -h localhost -p -u root -S /var/lib/mysql/mysql.sock
Enter password:
Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 2 to server version: 4.1.16-max

Type 'help;' or '\h' for help. Type '\c' to clear the buffer.

mysql> GRANT ALL ON joomla.* TO 'joomla'@'localhost' IDENTIFIED BY 'password';
```

  * Create a new database 'joomla':

```
> mysql -h localhost -p -u joomla -S /var/lib/mysql/mysql.sock
Enter password:
Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 4 to server version: 4.1.16-max

Type 'help;' or '\h' for help. Type '\c' to clear the buffer.

mysql> CREATE DATABASE joomla;
Query OK, 1 row affected (0.00 sec)
```

  * Install Joomla.

  * Download MySQL ejabberd schema:
```
> wget https://git.process-one.net/ejabberd/mainline/blobs/raw/2.1.x/src/odbc/mysql.sql
```
  * Import ejabberd database schema into the joomla database:

```
> mysql -D joomla -h localhost -p -u joomla -S /var/lib/mysql/mysql.sock < mysql.sql
```

  * Check that the database structure has been correctly created:

```
> echo "show tables;" | mysql -D joomla -h localhost -p -u joomla -S /var/lib/mysql/mysql.sock
Tables_in_ejabberd
last
rostergroups
rosterusers
spool
users
vcard
vcard_search
```

# eJabberd Configuration #


  * Get the latest ejabberd version:
```
git clone git://git.process-one.net/ejabberd/mainline.git ejabberd
cd ejabberd
git checkout -b 2.1.x origin/2.1.x
```

  * Go to ejabberd source directory:

```
> cd ejabberd/src
```

  * Compile ejabberd:
```
> ./configure --enable-odbc && make
```

  * Use the example config file as a basis:
```
> cp ejabberd.cfg.example ejabberd.cfg
```

  * Comment the following line in ejabberd.cfg:

```
{auth_method, internal}.
```

  * Add the following lines in ejabberd.cfg:

```
{auth_method, odbc}.
{odbc_server, {mysql, "localhost", "joomla", "joomla", "password"}}.
```
> Note: The MySQL configuration description is of the following form:

```
{mysql, Server, DB, Username, Password}
```

When you have done that user accounts are stored in MySQL. You can define extra informations that you might want to store in MySQL. Change the module used in ejabberd.cfg to change the persistance from the Mnesia database to MySQL:

  * Change mod\_last to mod\_last\_odbc to store the last seen date in MySQL.
  * Change mod\_offline to mod\_offline\_odbc to store offline messages in MySQL.
  * Change mod\_roster to mod\_roster\_odbc to store contact lists in MySQL.
  * Change mod\_vcard to mod\_vcard\_odbc to store user description in MySQL.

## Disabling self registration in eJabberd ##

  * edit the ejabberd.cfg file and comment out {access, register, [{allow, all}]} finally this should look like:

```
  % Every username can be registered via in-band registration:
  %{access, register, [{allow, all}]}.

  % None username can be registered via in-band registration:
  {access, register, [{deny, all}]}.
```

  * Also comment out the mod\_register module in the same file, it should look like this:

```

% Used modules:
  {modules,
   [
    {mod_announce,   [{access, announce}]},
   %% {mod_register,   [{access, register}]},
   ]}.

```

For any more changes or addition please give comments.