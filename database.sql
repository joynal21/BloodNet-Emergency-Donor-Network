CREATE DATABASE IF NOT EXISTS bloodnet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bloodnet;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS case_history;
DROP TABLE IF EXISTS site_media;
DROP TABLE IF EXISTS site_settings;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS donation_records;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS request_matches;
DROP TABLE IF EXISTS blood_requests;
DROP TABLE IF EXISTS blood_stock;
DROP TABLE IF EXISTS donor_profiles;
DROP TABLE IF EXISTS hospitals;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(160) NOT NULL UNIQUE,
 phone VARCHAR(30) DEFAULT NULL,
 password VARCHAR(255) NOT NULL,
 role ENUM('admin','donor','requester','hospital') NOT NULL DEFAULT 'requester',
 avatar VARCHAR(255) DEFAULT NULL,
 is_verified TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE hospitals (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT DEFAULT NULL,
 name VARCHAR(160) NOT NULL,
 address VARCHAR(255) NOT NULL,
 phone VARCHAR(30) DEFAULT NULL,
 lat DECIMAL(10,7) DEFAULT 23.8103000,
 lng DECIMAL(10,7) DEFAULT 90.4125000,
 verified TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_hospital_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE donor_profiles (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL UNIQUE,
 blood_group VARCHAR(5) NOT NULL,
 city VARCHAR(100) NOT NULL,
 address VARCHAR(255) DEFAULT NULL,
 lat DECIMAL(10,7) DEFAULT 23.8103000,
 lng DECIMAL(10,7) DEFAULT 90.4125000,
 availability ENUM('available','busy','recently_donated','offline') DEFAULT 'available',
 last_donation_date DATE DEFAULT NULL,
 total_donations INT NOT NULL DEFAULT 0,
 response_rate INT NOT NULL DEFAULT 80,
 emergency_opt_in TINYINT(1) NOT NULL DEFAULT 1,
 verified TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_donor_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE blood_requests (
 id INT AUTO_INCREMENT PRIMARY KEY,
 requester_id INT NOT NULL,
 hospital_id INT DEFAULT NULL,
 patient_name VARCHAR(120) NOT NULL,
 blood_group VARCHAR(5) NOT NULL,
 units INT NOT NULL DEFAULT 1,
 priority ENUM('critical','urgent','normal') NOT NULL DEFAULT 'urgent',
 needed_by DATETIME NOT NULL,
 reason VARCHAR(255) DEFAULT NULL,
 address VARCHAR(255) NOT NULL,
 lat DECIMAL(10,7) DEFAULT 23.8103000,
 lng DECIMAL(10,7) DEFAULT 90.4125000,
 status ENUM('submitted','verified','matching','accepted','arrived','collected','completed','cancelled') NOT NULL DEFAULT 'submitted',
 broadcast_radius INT NOT NULL DEFAULT 3,
 notes TEXT DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_request_user FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_request_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE request_matches (
 id INT AUTO_INCREMENT PRIMARY KEY,
 request_id INT NOT NULL,
 donor_id INT NOT NULL,
 distance_km DECIMAL(5,2) NOT NULL DEFAULT 0,
 readiness_score INT NOT NULL DEFAULT 0,
 match_status ENUM('suggested','notified','accepted','declined','expired','completed') NOT NULL DEFAULT 'suggested',
 notified_at DATETIME DEFAULT NULL,
 responded_at DATETIME DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_request_donor(request_id, donor_id),
 CONSTRAINT fk_match_request FOREIGN KEY (request_id) REFERENCES blood_requests(id) ON DELETE CASCADE,
 CONSTRAINT fk_match_donor FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 title VARCHAR(160) NOT NULL,
 message VARCHAR(255) NOT NULL,
 type VARCHAR(30) NOT NULL DEFAULT 'info',
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 link VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE donation_records (
 id INT AUTO_INCREMENT PRIMARY KEY,
 request_id INT NOT NULL,
 donor_id INT NOT NULL,
 hospital_id INT DEFAULT NULL,
 units INT NOT NULL DEFAULT 1,
 donated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 verification_code VARCHAR(20) DEFAULT NULL,
 certificate_no VARCHAR(40) DEFAULT NULL,
 notes VARCHAR(255) DEFAULT NULL,
 CONSTRAINT fk_donation_request FOREIGN KEY (request_id) REFERENCES blood_requests(id) ON DELETE CASCADE,
 CONSTRAINT fk_donation_donor FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_donation_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE blood_stock (
 id INT AUTO_INCREMENT PRIMARY KEY,
 hospital_id INT NOT NULL,
 blood_group VARCHAR(5) NOT NULL,
 units INT NOT NULL DEFAULT 0,
 min_threshold INT NOT NULL DEFAULT 5,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_stock(hospital_id,blood_group),
 CONSTRAINT fk_stock_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT DEFAULT NULL,
 action VARCHAR(160) NOT NULL,
 entity_type VARCHAR(60) DEFAULT NULL,
 entity_id INT DEFAULT NULL,
 details VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


CREATE TABLE site_settings (
 id INT AUTO_INCREMENT PRIMARY KEY,
 setting_key VARCHAR(100) NOT NULL UNIQUE,
 setting_value TEXT DEFAULT NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE site_media (
 id INT AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(160) NOT NULL,
 media_type ENUM('image','video') NOT NULL DEFAULT 'image',
 media_url VARCHAR(700) NOT NULL,
 caption VARCHAR(255) DEFAULT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE case_history (
 id INT AUTO_INCREMENT PRIMARY KEY,
 request_id INT NOT NULL,
 user_id INT DEFAULT NULL,
 event_name VARCHAR(120) NOT NULL,
 note VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_history_request FOREIGN KEY (request_id) REFERENCES blood_requests(id) ON DELETE CASCADE,
 CONSTRAINT fk_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE community_events (
 id INT AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(180) NOT NULL,
 event_date DATE NOT NULL,
 location VARCHAR(255) NOT NULL,
 audience VARCHAR(120) DEFAULT NULL,
 image_url VARCHAR(700) NOT NULL,
 description TEXT DEFAULT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE impact_stories (
 id INT AUTO_INCREMENT PRIMARY KEY,
 person_name VARCHAR(120) NOT NULL,
 role_label VARCHAR(120) NOT NULL,
 image_url VARCHAR(700) NOT NULL,
 story_text TEXT NOT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
INSERT INTO users (id,name,email,phone,password,role,is_verified) VALUES
(1,'System Admin','admin@bloodnet.local','01700000001','$2y$12$.8OEr/NkQ..q/MxRBvCeE.chwxHJ7odns/w6fK7bxCFapJ4Q7z5jW','admin',1),
(2,'Ariana Rahman','ariana@bloodnet.local','01710000002','$2y$12$XnaafWnnd5wDOQfvE4MPPuyAHzBTldf3kxC79LWDEXe86uBQceSS.','donor',1),
(3,'Farhan Karim','farhan@bloodnet.local','01710000003','$2y$12$XnaafWnnd5wDOQfvE4MPPuyAHzBTldf3kxC79LWDEXe86uBQceSS.','donor',1),
(4,'Nabila Islam','nabila@bloodnet.local','01710000004','$2y$12$XnaafWnnd5wDOQfvE4MPPuyAHzBTldf3kxC79LWDEXe86uBQceSS.','donor',1),
(5,'CityCare Hospital','hospital@bloodnet.local','01800000005','$2y$12$eb4wnvO3jXM71npNwhshT.ZYGIPtQG00CpjNR2PN4PX2z.YV58TEm','hospital',1),
(6,'Demo Requester','user@bloodnet.local','01900000006','$2y$12$cnwbfodcejApl2VF4f/LmOMRqIJKRuKh3hptuHgugmfVQ6PoxM6lu','requester',1),
(7,'Rafi Ahmed','rafi@bloodnet.local','01710000007','$2y$12$XnaafWnnd5wDOQfvE4MPPuyAHzBTldf3kxC79LWDEXe86uBQceSS.','donor',1),
(8,'Maliha Noor','maliha@bloodnet.local','01710000008','$2y$12$XnaafWnnd5wDOQfvE4MPPuyAHzBTldf3kxC79LWDEXe86uBQceSS.','donor',1);

INSERT INTO hospitals (id,user_id,name,address,phone,lat,lng,verified) VALUES
(1,5,'CityCare Medical Center','Dhanmondi, Dhaka','01800000005',23.7465000,90.3760000,1),
(2,NULL,'Central Blood Bank','Shahbag, Dhaka','01800000008',23.7389000,90.3950000,1),
(3,NULL,'Northview Hospital','Uttara, Dhaka','01800000009',23.8759000,90.3795000,1);

INSERT INTO donor_profiles (user_id,blood_group,city,address,lat,lng,availability,last_donation_date,total_donations,response_rate,emergency_opt_in,verified) VALUES
(2,'O+','Dhaka','Kalabagan',23.7501000,90.3811000,'available','2026-05-10',8,96,1,1),
(3,'O-','Dhaka','Shahbag',23.7395000,90.3948000,'available','2026-04-18',12,92,1,1),
(4,'A+','Dhaka','Green Road',23.7527000,90.3892000,'available','2026-06-01',5,88,1,1),
(7,'B+','Dhaka','Panthapath',23.7522000,90.3838000,'available','2026-03-12',10,94,1,1),
(8,'AB+','Dhaka','Farmgate',23.7589000,90.3897000,'busy','2026-05-20',6,84,1,1);

INSERT INTO blood_stock (hospital_id,blood_group,units,min_threshold) VALUES
(1,'A+',12,5),(1,'A-',4,5),(1,'B+',8,5),(1,'B-',3,5),(1,'AB+',6,4),(1,'AB-',2,4),(1,'O+',14,6),(1,'O-',3,6),
(2,'A+',9,5),(2,'B+',5,5),(2,'O+',11,6),(2,'O-',2,5);

INSERT INTO blood_requests (id,requester_id,hospital_id,patient_name,blood_group,units,priority,needed_by,reason,address,lat,lng,status,broadcast_radius,notes,created_at) VALUES
(1,6,1,'Samia Hossain','O+',2,'critical',DATE_ADD(NOW(), INTERVAL 4 HOUR),'Emergency surgery','CityCare Medical Center, Dhanmondi',23.7465000,90.3760000,'matching',5,'Demo active emergency request',NOW()),
(2,5,1,'Hasan Mahmud','B+',1,'urgent',DATE_ADD(NOW(), INTERVAL 1 DAY),'Post-operative support','CityCare Medical Center, Dhanmondi',23.7465000,90.3760000,'verified',3,'Hospital verified request',DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3,6,2,'Tania Akter','A+',1,'normal',DATE_ADD(NOW(), INTERVAL 2 DAY),'Planned procedure','Central Blood Bank, Shahbag',23.7389000,90.3950000,'completed',10,'Completed demo case',DATE_SUB(NOW(), INTERVAL 5 DAY));

INSERT INTO request_matches (request_id,donor_id,distance_km,readiness_score,match_status,notified_at) VALUES
(1,2,2.4,94,'notified',NOW()),(1,3,3.1,91,'notified',NOW()),(2,7,2.8,96,'suggested',NULL);

INSERT INTO donation_records (request_id,donor_id,hospital_id,units,donated_at,verification_code,certificate_no,notes) VALUES
(3,4,2,1,DATE_SUB(NOW(), INTERVAL 4 DAY),'QR-482731','BN-2026-00031','Verified donation completed');

INSERT INTO notifications (user_id,title,message,type,link) VALUES
(2,'Critical O+ request nearby','A patient needs 2 units of O+ blood within 4 hours.','emergency','request-view.php?id=1'),
(3,'Emergency donor alert','Your blood type can support a nearby critical request.','emergency','request-view.php?id=1'),
(6,'Matching started','Compatible donors are being ranked and notified for Samia Hossain.','success','request-view.php?id=1');


INSERT INTO site_settings(setting_key,setting_value) VALUES
('hero_title','Blood reaches people faster when the network thinks together.'),
('hero_subtitle','BloodNet connects urgent patients, verified hospitals and ready donors through smart matching, live case tracking and privacy-first coordination.'),
('video_url','https://videos.pexels.com/video-files/13149047/13149047-uhd_2160_3840_30fps.mp4'),
('announcement','Emergency network active — verified donors & hospitals');

INSERT INTO site_media(title,media_type,media_url,caption,is_active) VALUES
('Emergency blood collection','image','https://images.pexels.com/photos/37553387/pexels-photo-37553387.jpeg?auto=compress&cs=tinysrgb&w=1600','A modern blood collection environment',1),
('Donor in action','image','https://images.pexels.com/photos/12820058/pexels-photo-12820058.jpeg?auto=compress&cs=tinysrgb&w=1600','Every prepared donor can make a difference',1),
('A donation journey','image','https://images.pexels.com/photos/4680300/pexels-photo-4680300.jpeg?auto=compress&cs=tinysrgb&w=1600','Track the journey from alert to completed donation',1);


INSERT INTO community_events(title,event_date,location,audience,image_url,description,is_active) VALUES
('University Blood Donation Camp', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'IUBAT Campus Auditorium', 'Students & Faculty', 'https://images.pexels.com/photos/6823564/pexels-photo-6823564.jpeg?auto=compress&cs=tinysrgb&w=1600', 'A full-day campus event with registration, awareness booths and doctor support.', 1),
('Hospital Open Awareness Day', DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'CityCare Medical Center', 'Families & Community', 'https://images.pexels.com/photos/7578804/pexels-photo-7578804.jpeg?auto=compress&cs=tinysrgb&w=1600', 'Meet doctors, learn blood eligibility rules and see how emergency matching works.', 1),
('Rare Blood Registry Drive', DATE_ADD(CURDATE(), INTERVAL 18 DAY), 'Central Blood Bank', 'Public Donors', 'https://images.pexels.com/photos/7088482/pexels-photo-7088482.jpeg?auto=compress&cs=tinysrgb&w=1600', 'A special campaign focused on rare blood groups and long-term donor engagement.', 1);

INSERT INTO impact_stories(person_name,role_label,image_url,story_text,is_active) VALUES
('Dr. Nusrat Jahan', 'Emergency Physician', 'https://images.pexels.com/photos/5215024/pexels-photo-5215024.jpeg?auto=compress&cs=tinysrgb&w=1200', 'When donor alerts reach the right people quickly, families feel immediate relief and our emergency team can focus on treatment.', 1),
('Sabrina Akter', 'Donor', 'https://images.pexels.com/photos/8460157/pexels-photo-8460157.jpeg?auto=compress&cs=tinysrgb&w=1200', 'The dashboard made it easy to accept a request and know exactly where to go. It feels meaningful because the journey is visible.', 1),
('Arif Hossain', 'Patient Family', 'https://images.pexels.com/photos/6129688/pexels-photo-6129688.jpeg?auto=compress&cs=tinysrgb&w=1200', 'Seeing the request status update from matching to completed gave our family confidence during a very stressful time.', 1);

INSERT INTO case_history(request_id,user_id,event_name,note,created_at) VALUES
(1,6,'Request created','Critical O+ request submitted',NOW()),
(1,1,'Matching started','Compatible donors notified inside the current radius',NOW()),
(2,5,'Hospital verification','Request checked and marked verified',DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3,6,'Request created','Planned A+ case opened',DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3,5,'Donation completed','Verified donation and certificate generated',DATE_SUB(NOW(), INTERVAL 4 DAY));
