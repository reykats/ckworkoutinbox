
# -----------------------------------------------------------------------------------------------------------------------------------------------------
# Seed Data
#

INSERT INTO calendar_entry_repeat_type (id,name) VALUES
(1,'Ever Day'),
(2,'Work Days'),
(3,'Every Week'),
(4,'Every Month'),
(5,'Every Year');

SELECT * FROM calendar_entry_repeat_type;

INSERT INTO calendar_event_type (id,name,description,log_participants) VALUES
(1,'Workout Of The Day','',1);

SELECT * FROM calendar_event_type;

INSERT INTO how_do_you_feel (id,name) VALUES
(1,'Smile'),
(2,'Just Fine'),
(3,'Not So Good');

SELECT * FROM how_do_you_feel;

INSERT INTO emotional_level (id,name) VALUES
(1,'excellent'),
(2,'great'),
(3,'ok'),
(4,'tired'),
(5,'exhausted');

SELECT * FROM emotional_level;

# -------------------------------------------- Test --------------------------------------------------------

INSERT INTO client (id,name,created) VALUES
(1,'NorCal CrossFit',UNIX_TIMESTAMP(NOW()));

SELECT * FROM client;

INSERT INTO location (id,client_id,name,address,phone,email,timezone,created) VALUES
(1,1,'NorCal CrossFit Santa Clara','1731 N. 1st St., San Jose, CA 95112','408-691-0430','Jason@cfnorcal.com','America/Los_Angeles',UNIX_TIMESTAMP(NOW())),
(2,1,'NorCal CrossFit Mountain View','2584 Leghorn St., Mountain View, Ca 94043','408-656-3135','Alex@cfnorcal.com','America/Los_Angeles',UNIX_TIMESTAMP(NOW())),
(3,1,'NorCal CrossFit San Jose','400 Saratoga Ave., San Jose, CA 95129','408-691-0430','Jason@cfnorcal.com','America/Los_Angeles',UNIX_TIMESTAMP(NOW()));

SELECT * FROM location;

INSERT INTO classroom (id,location_id,name) VALUES
(1,3,'Downstairs'),
(2,3,'Upstairs'),
(3,2,'gym'),
(4,1,'large room'),
(5,1,'small room');

SELECT * FROM classroom;

INSERT INTO calendar (id,client_id,location_id,classroom_id,name) VALUES
(1,1,null,null,'NorCal CrossFit'),
(2,1,1,null,'NorCal CrossFit Santa Clara'),
(3,1,1,4,'NorCal CrossFit Santa Clara Large Room'),
(4,1,1,5,'NorCal CrossFit Santa Clara Small Room'),
(5,1,2,null,'NorCal CrossFit Mountain View'),
(6,1,2,3,'NorCal CrossFit Mountain View Gym'),
(7,1,3,null,'NorCal CrossFit San Jose'),
(8,1,3,1,'NorCal CrossFit San Jose Downstairs'),
(9,1,3,2,'NorCal CrossFit San Jose Upstairs');

INSERT INTO `user` (`id`, `created`, `deleted`, `birthdate`, `gender`, `phone`, `password`, `timezone`, `activation_token`, `activation_token_created`, `username`, `first_name`, `last_name`, `email`) VALUES
(1, 1335566805, NULL, 415843200, 'M', '6150123456', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Daniel', 'Chen', 'daniel@mailfininc.com'),
(2, 1335566805, NULL, -307324800, 'M', '6151234567', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Frank', 'Mikulastik', 'frank@mailfininc.com'),
(3, 1335566805, NULL, 660787200, 'F', '6152345678', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Claudia', 'Yu', 'claudia@mailfininc.com'),
(4, 1335566805, NULL, 946857600, 'M', '6153456789', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Aaron', 'Copland', 'aaron@mailfininc.com'),
(5, 1335566805, NULL, 981244800, 'F', '6154567890', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Abigail', 'Washburn', 'abigail@mailfininc.com'),
(6, 1335566805, NULL, 631152000, 'F', '1111111111', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Bonnie', 'Raitt', 'bonnie@mailfininc.com'),
(7, 1335566805, NULL, 665452800, 'M', '2222222222', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Bob', 'Haggart', 'bhaggart@mailfininc.com'),
(8, 1335566805, NULL, 699580800, 'M', '3333333333', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Bob', 'Seger', 'bseger@mailfininc.com'),
(9, 1335566805, NULL, 733881600, 'M', '4444444444', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Bobby', 'Pickett', 'bpickett@mailfininc.com'),
(10, 1335566805, NULL, 768096000, 'F', '5555555555', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Taylor', 'Dayne', 'tdayne@mailfininc.com'),
(11, 1335566805, NULL, 633830400, 'M', '6666666666', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Spike', 'Jones', 'sjones@mailfininc.com'),
(12, 1335566805, NULL, 636249600, 'F', '7777777777', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Sophie B', 'Hawkins', 'sbh@mailfininc.com'),
(13, 1335566805, NULL, 699580800, 'M', '8888888888', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Robert', 'Plant', 'rplant@mailfininc.com'),
(14, 1335566805, NULL, 733622400, 'M', '9999999999', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Robin', 'Thicke', 'rthicke@mailfininc.com'),
(15, 1335566805, NULL, 705196800, 'F', '000000000', MD5('temp!123'), NULL, NULL, NULL, NULL, 'Peter', 'Frampton', 'pframption@mailfininc.com');

INSERT INTO client_user (id,user_id,client_id,client_user_role_id) VALUES
(1,15,1,1),
(2,14,1,2),
(3,13,1,2),
(4,12,1,2),
(5,11,1,2),
(6,10,1,2),
(7,9,1,2),
(8,8,1,2),
(9,7,1,2),
(10,6,1,2),
(11,5,1,2),
(12,4,1,2),
(13,3,1,2),
(14,2,1,2),
(15,1,1,2);

SELECT cal.id, cal.name,
cal.client_id, c.name,
cal.location_id, l.name,
cal.classroom_id, cr.name
FROM calendar cal
LEFT OUTER JOIN client c ON c.id = cal.client_id
LEFT OUTER JOIN location l ON l.id = cal.location_id
LEFT OUTER JOIN classroom cr ON cr.id = cal.classroom_id
ORDER BY cal.client_id, cal.location_id, cal.classroom_id;

INSERT INTO wod (id,client_id,date) VALUES
(1,1,1336003200),
(2,1,1335916800),
(3,1,1335830400);

INSERT INTO calendar_event (calendar_id,calendar_event_type_id,name,start,duration) VALUES 
(2,1,"location event",1336003200 + (13 * 3600),3600),
(1,1,"client event",1336003200 + (9 * 3600),3600);

INSERT INTO calendar_event (calendar_id,calendar_event_type_id,name,start,duration) VALUES 
(4,1,"General CrossFit",1336003200 + (6 * 3600),3600),
(4,1,"General CrossFit",1336003200 + (7 * 3600),3600),
(4,1,"General CrossFit",1336003200 + (8 * 3600),3600),
(4,1,"General CrossFit",1336003200 + (15 * 3600),3600),
(4,1,"General CrossFit",1336003200 + (16 * 3600),3600),
(4,1,"General CrossFit",1336003200 + (17 * 3600),3600),
(4,1,"General CrossFit",1336003200 + (18 * 3600),3600),
(4,1,"General CrossFit",1336003200 + (20 * 3600),3600),
(4,1,"General CrossFit",1336003200 + (21 * 3600),3600),
(4,1,"General CrossFit",1336003200 + (22 * 3600),3600),
(4,1,"General CrossFit",1335916800 + (6 * 3600),3600),
(4,1,"General CrossFit",1335916800 + (7 * 3600),3600),
(4,1,"General CrossFit",1335916800 + (8 * 3600),3600),
(4,1,"General CrossFit",1335916800 + (15 * 3600),3600),
(4,1,"General CrossFit",1335916800 + (16 * 3600),3600),
(4,1,"General CrossFit",1335916800 + (17 * 3600),3600),
(4,1,"General CrossFit",1335916800 + (18 * 3600),3600),
(4,1,"General CrossFit",1335916800 + (20 * 3600),3600),
(4,1,"General CrossFit",1335916800 + (21 * 3600),3600),
(4,1,"General CrossFit",1335916800 + (22 * 3600),3600),
(4,1,"General CrossFit",1335830400 + (6 * 3600),3600),
(4,1,"General CrossFit",1335830400 + (7 * 3600),3600),
(4,1,"General CrossFit",1335830400 + (8 * 3600),3600),
(4,1,"General CrossFit",1335830400 + (15 * 3600),3600),
(4,1,"General CrossFit",1335830400 + (16 * 3600),3600),
(4,1,"General CrossFit",1335830400 + (17 * 3600),3600),
(4,1,"General CrossFit",1335830400 + (18 * 3600),3600),
(4,1,"General CrossFit",1335830400 + (20 * 3600),3600),
(4,1,"General CrossFit",1335830400 + (21 * 3600),3600),
(4,1,"General CrossFit",1335830400 + (22 * 3600),3600);

INSERT INTO calendar_event (calendar_id,calendar_event_type_id,name,start,duration) VALUES 
(6,1,"General CrossFit",1336003200 + (6 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (7 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (8 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (10 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (11 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (12 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (15 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (16 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (17 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (18 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (20 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (21 * 3600),3600),
(6,1,"General CrossFit",1336003200 + (22 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (6 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (7 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (8 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (10 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (11 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (12 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (15 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (16 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (17 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (18 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (20 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (21 * 3600),3600),
(6,1,"General CrossFit",1335916800 + (22 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (6 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (7 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (8 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (10 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (11 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (12 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (15 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (16 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (17 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (18 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (20 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (21 * 3600),3600),
(6,1,"General CrossFit",1335830400 + (22 * 3600),3600);

INSERT INTO calendar_event (calendar_id,calendar_event_type_id,name,start,duration) VALUES 
(8,1,"General CrossFit",1336003200 + (7 * 3600),3600),
(8,1,"General CrossFit",1336003200 + (8 * 3600),3600),
(8,1,"General CrossFit",1336003200 + (15 * 3600),3600),
(8,1,"General CrossFit",1336003200 + (16 * 3600),3600),
(8,1,"General CrossFit",1336003200 + (17 * 3600),3600),
(8,1,"General CrossFit",1336003200 + (18 * 3600),3600),
(8,1,"General CrossFit",1336003200 + (20 * 3600),3600),
(8,1,"General CrossFit",1336003200 + (21 * 3600),3600),
(8,1,"General CrossFit",1335916800 + (7 * 3600),3600),
(8,1,"General CrossFit",1335916800 + (8 * 3600),3600),
(8,1,"General CrossFit",1335916800 + (15 * 3600),3600),
(8,1,"General CrossFit",1335916800 + (16 * 3600),3600),
(8,1,"General CrossFit",1335916800 + (17 * 3600),3600),
(8,1,"General CrossFit",1335916800 + (18 * 3600),3600),
(8,1,"General CrossFit",1335916800 + (20 * 3600),3600),
(8,1,"General CrossFit",1335916800 + (21 * 3600),3600),
(8,1,"General CrossFit",1335830400 + (7 * 3600),3600),
(8,1,"General CrossFit",1335830400 + (8 * 3600),3600),
(8,1,"General CrossFit",1335830400 + (15 * 3600),3600),
(8,1,"General CrossFit",1335830400 + (16 * 3600),3600),
(8,1,"General CrossFit",1335830400 + (17 * 3600),3600),
(8,1,"General CrossFit",1335830400 + (18 * 3600),3600),
(8,1,"General CrossFit",1335830400 + (20 * 3600),3600),
(8,1,"General CrossFit",1335830400 + (21 * 3600),3600);