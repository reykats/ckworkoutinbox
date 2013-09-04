
mysql --user=root --password=Sesqueue623!

DROP DATABASE workoutdb;
CREATE DATABASE workoutdb;

USE workoutdb;

CREATE USER 'workoutweb'@'localhost' IDENTIFIED BY '1w2e3b45';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE ON workoutdb.* TO 'workoutweb'@'localhost';

#
# Create a backup user for all databases
CREATE USER 'backup_user'@'localhost' IDENTIFIED BY 'backup_123';
GRANT LOCK TABLES, SELECT, RELOAD, SUPER, REPLICATION CLIENT, SHOW VIEW ON *.* TO 'backup_user'@'localhost';
#
# Create a restore user for all databasess
CREATE USER 'restore_user'@'localhost' IDENTIFIED BY 'backup!234';
GRANT ALL PRIVILEGES ON *.* to 'restore_user'@'localhost';

#
# Create a user for maou
CREATE USER 'maou'@'localhost' IDENTIFIED BY 'surfermaou';
GRANT ALL ON workoutdb.* TO 'maou'@'localhost';