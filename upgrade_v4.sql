-- BloodNet V4 upgrade: events + inspiring stories

CREATE TABLE IF NOT EXISTS community_events (
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

CREATE TABLE IF NOT EXISTS impact_stories (
 id INT AUTO_INCREMENT PRIMARY KEY,
 person_name VARCHAR(120) NOT NULL,
 role_label VARCHAR(120) NOT NULL,
 image_url VARCHAR(700) NOT NULL,
 story_text TEXT NOT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO community_events(title,event_date,location,audience,image_url,description,is_active) VALUES
('University Blood Donation Camp', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'IUBAT Campus Auditorium', 'Students & Faculty', 'https://images.pexels.com/photos/6823564/pexels-photo-6823564.jpeg?auto=compress&cs=tinysrgb&w=1600', 'A full-day campus event with registration, awareness booths and doctor support.', 1),
('Hospital Open Awareness Day', DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'CityCare Medical Center', 'Families & Community', 'https://images.pexels.com/photos/7578804/pexels-photo-7578804.jpeg?auto=compress&cs=tinysrgb&w=1600', 'Meet doctors, learn blood eligibility rules and see how emergency matching works.', 1),
('Rare Blood Registry Drive', DATE_ADD(CURDATE(), INTERVAL 18 DAY), 'Central Blood Bank', 'Public Donors', 'https://images.pexels.com/photos/7088482/pexels-photo-7088482.jpeg?auto=compress&cs=tinysrgb&w=1600', 'A special campaign focused on rare blood groups and long-term donor engagement.', 1);

INSERT INTO impact_stories(person_name,role_label,image_url,story_text,is_active) VALUES
('Dr. Nusrat Jahan', 'Emergency Physician', 'https://images.pexels.com/photos/5215024/pexels-photo-5215024.jpeg?auto=compress&cs=tinysrgb&w=1200', 'When donor alerts reach the right people quickly, families feel immediate relief and our emergency team can focus on treatment.', 1),
('Sabrina Akter', 'Donor', 'https://images.pexels.com/photos/8460157/pexels-photo-8460157.jpeg?auto=compress&cs=tinysrgb&w=1200', 'The dashboard made it easy to accept a request and know exactly where to go. It feels meaningful because the journey is visible.', 1),
('Arif Hossain', 'Patient Family', 'https://images.pexels.com/photos/6129688/pexels-photo-6129688.jpeg?auto=compress&cs=tinysrgb&w=1200', 'Seeing the request status update from matching to completed gave our family confidence during a very stressful time.', 1);
