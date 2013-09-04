
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

INSERT INTO library_measurement (id,name,description) VALUES
(0,'Time',''),
(1,'Weight',''),
(2,'Distance',''),
(3,'Height',''),
(4,'Calories','');

INSERT INTO library_measurement_system (id,name,description) VALUES
(0,'all',''),
(1,'english',''),
(2,'metric','');

INSERT INTO library_measurement_system_unit (id,library_measurement_id,library_measurement_system_id,name,abbr,description) VALUES
(0,0,0,'Minute','min',''),
(1,0,0,'Second','sec',''),
(2,0,0,'Hour','hour',''),
(3,1,1,'Pound','lb',''),
(4,1,2,'Kilogram','kg',''),
(5,2,1,'Foot','ft',''),
(6,2,1,'Yard','yd',''),
(7,2,1,'Mile','mi',''),
(8,2,2,'Meter','m',''),
(9,2,2,'Kilometer','km',''),
(10,3,1,'Foot','ft',''),
(11,3,1,'Inch','in',''),
(12,3,2,'Centimeter','cm',''),
(13,3,2,'Meter','m','');

INSERT INTO library_workout_recording_type (id,name,description) VALUES
(0,'Record Time',''),
(1,'Record Rounds','');

INSERT INTO library_exercise_level (id,name,description) VALUES
(0,'Beginner','Exercise Using Machine With Fixed Motion Path'),
(1,'Intermediate','Exercise Using Machine Without Fixed Motion Path (i.e. Cables)'),
(2,'Advanced','Exercise Using Free Weight (i.e. Barbell, Dumbell)'),
(3,'Expert','Exercise That Moves Body Through Space (i.e. Squats, Pullup)');

INSERT INTO library_exercise_type (id,name,description) VALUES
(0,'Flexibility','Exercises such as streching, improving the range of motion of muscles and joints'),
(1,'Cardiovascular (Aerobic)','Exercises such as rowing, running, jumpping, focus on increasing cardiovascular endurance'),
(2,'Strengthening (Resistance)','Exercises such as weight training, functional training, sprinting, increase short-term muscle strength');

INSERT INTO library_sport_type (id,name,description) VALUES
(0,'Crossfit','');

INSERT INTO client_user_role (id,name,description) VALUES
(1,'Coach','An employee of the gym'),
(2,'Member','A member of the gym'),
(3,'Guest','A guest or trial member of the gym');

INSERT INTO library_body_region (id,name,description) VALUES
(0,'Full Body',''),
(1,'Upper Body',''),
(2,'Lower Body',''),
(3,'Core',''),
(4,'Arm','');

INSERT INTO library_body_part (id,name,description) VALUES
(0,'Traps',''),
(1,'Shoulder',''),
(2,'Chest',''),
(3,'Biceps',''),
(4,'Forearm',''),
(5,'Abs',''),
(6,'Quads',''),
(7,'Calves',''),
(8,'Triceps',''),
(9,'Lats',''),
(10,'Mid Back',''),
(11,'Lower Back',''),
(12,'Glutes',''),
(13,'Hastrings',''),
(14,'Obliques','');

INSERT INTO library_body_region_body_part (id,library_body_region_id,library_body_part_id) VALUES
(0,0,0),
(1,0,1),
(2,0,2),
(3,0,3),
(4,0,4),
(5,0,5),
(6,0,6),
(7,0,7),
(8,0,8),
(9,0,9),
(10,0,10),
(11,0,11),
(12,0,12),
(13,0,13),
(14,0,14),
(15,1,0),
(16,1,1),
(17,1,2),
(18,1,3),
(19,1,4),
(20,1,5),
(21,1,8),
(22,1,9),
(23,1,10),
(24,1,11),
(25,1,14),
(26,2,6),
(27,2,7),
(28,2,12),
(29,2,13),
(30,3,5),
(31,3,11),
(32,3,12),
(33,3,14),
(34,4,3),
(35,4,4),
(36,4,8);