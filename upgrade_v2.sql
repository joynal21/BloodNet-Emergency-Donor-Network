USE bloodnet;
CREATE TABLE IF NOT EXISTS site_settings (
 id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(100) NOT NULL UNIQUE, setting_value TEXT DEFAULT NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS site_media (
 id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(160) NOT NULL, media_type ENUM('image','video') NOT NULL DEFAULT 'image',
 media_url VARCHAR(700) NOT NULL, caption VARCHAR(255) DEFAULT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS case_history (
 id INT AUTO_INCREMENT PRIMARY KEY, request_id INT NOT NULL, user_id INT DEFAULT NULL, event_name VARCHAR(120) NOT NULL,
 note VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_history_request_v2 FOREIGN KEY (request_id) REFERENCES blood_requests(id) ON DELETE CASCADE,
 CONSTRAINT fk_history_user_v2 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
INSERT INTO site_settings(setting_key,setting_value) VALUES
('hero_title','Blood reaches people faster when the network thinks together.'),
('hero_subtitle','BloodNet connects urgent patients, verified hospitals and ready donors through smart matching, live case tracking and privacy-first coordination.'),
('video_url','https://videos.pexels.com/video-files/13149047/13149047-uhd_2160_3840_30fps.mp4'),
('announcement','Emergency network active — verified donors & hospitals')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
INSERT INTO site_media(title,media_type,media_url,caption,is_active)
SELECT * FROM (SELECT 'Emergency blood collection','image','https://images.pexels.com/photos/37553387/pexels-photo-37553387.jpeg?auto=compress&cs=tinysrgb&w=1600','A modern blood collection environment',1) x
WHERE NOT EXISTS (SELECT 1 FROM site_media WHERE title='Emergency blood collection');
INSERT INTO site_media(title,media_type,media_url,caption,is_active)
SELECT * FROM (SELECT 'Donor in action','image','https://images.pexels.com/photos/12820058/pexels-photo-12820058.jpeg?auto=compress&cs=tinysrgb&w=1600','Every prepared donor can make a difference',1) x
WHERE NOT EXISTS (SELECT 1 FROM site_media WHERE title='Donor in action');
INSERT INTO site_media(title,media_type,media_url,caption,is_active)
SELECT * FROM (SELECT 'A donation journey','image','https://images.pexels.com/photos/4680300/pexels-photo-4680300.jpeg?auto=compress&cs=tinysrgb&w=1600','Track the journey from alert to completed donation',1) x
WHERE NOT EXISTS (SELECT 1 FROM site_media WHERE title='A donation journey');
