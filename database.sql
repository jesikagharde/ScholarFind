-- ============================================================
-- ScholarFind: Scholarship Finder & Eligibility Checker
-- Database Name: scholarship_db
-- ============================================================
CREATE DATABASE IF NOT EXISTS `scholarship_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `scholarship_db`;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `saved_scholarships`;
DROP TABLE IF EXISTS `scholarships`;
DROP TABLE IF EXISTS `student_profiles`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================
-- 1. USERS TABLE
-- ============================================================
CREATE TABLE `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `scholarfind_id` VARCHAR(100) UNIQUE DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'admin') DEFAULT 'student',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- ============================================================
-- 2. STUDENT PROFILES TABLE
-- ============================================================
CREATE TABLE `student_profiles` (
    `profile_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `age` INT DEFAULT NULL,
    `gender` ENUM('male', 'female', 'other', 'all') DEFAULT 'all',
    `state` VARCHAR(100) NOT NULL DEFAULT '',
    `education_level` VARCHAR(100) NOT NULL DEFAULT '',
    `course` VARCHAR(100) DEFAULT '',
    `year` INT DEFAULT 1,
    `percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `family_income` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `category` VARCHAR(50) NOT NULL DEFAULT '',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;
-- ============================================================
-- 3. SCHOLARSHIPS TABLE
-- ============================================================
CREATE TABLE `scholarships` (
    `scholarship_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `provider` VARCHAR(255) NOT NULL,
    `source` VARCHAR(255) DEFAULT 'Official Verified Source',
    `application_portal` VARCHAR(150) DEFAULT 'Official Portal',
    `description` TEXT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `education_level` VARCHAR(100) NOT NULL DEFAULT 'All',
    `course` VARCHAR(255) DEFAULT 'All',
    `minimum_percentage` DECIMAL(5,2) DEFAULT 0.00,
    `maximum_income` DECIMAL(12,2) DEFAULT NULL,
    `gender_eligible` ENUM('all', 'female', 'male', 'other') DEFAULT 'all',
    `state` VARCHAR(100) DEFAULT 'All India',
    `category` VARCHAR(50) DEFAULT 'All',
    `required_documents` TEXT DEFAULT NULL,
    `application_start` DATE DEFAULT NULL,
    `deadline` DATE DEFAULT NULL,
    `application_url` VARCHAR(500) DEFAULT '#',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_filters` (`education_level`, `state`, `category`, `deadline`, `is_active`)
) ENGINE=InnoDB;
-- ============================================================
-- 4. SAVED SCHOLARSHIPS TABLE
-- ============================================================
CREATE TABLE `saved_scholarships` (
    `save_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `scholarship_id` INT NOT NULL,
    `saved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_save` (`user_id`, `scholarship_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships`(`scholarship_id`) ON DELETE CASCADE
) ENGINE=InnoDB;
-- ============================================================
-- 5. APPLICATIONS TABLE
-- ============================================================
CREATE TABLE `applications` (
    `application_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `scholarship_id` INT NOT NULL,
    `status` ENUM('saved', 'applied', 'under_review', 'awarded', 'rejected') DEFAULT 'applied',
    `application_date` DATE NOT NULL,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_application` (`user_id`, `scholarship_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships`(`scholarship_id`) ON DELETE CASCADE
) ENGINE=InnoDB;
-- ============================================================
-- DEFAULT USERS
-- ============================================================
INSERT INTO `users` (`user_id`, `name`, `email`, `scholarfind_id`, `password`, `role`) VALUES
(1, 'System Administrator', 'admin@scholarship.com', 'system.administrator', '$2y$10$wTfZ47iRz9OeVmBw1Lq9seKkJrGj4qF7zJ2dPn6/19.N6U/4z4w3G', 'admin'),
(2, 'Demo Student', 'student@example.com', 'demo.student', '$2y$10$wTfZ47iRz9OeVmBw1Lq9seKkJrGj4qF7zJ2dPn6/19.N6U/4z4w3G', 'student');
-- ============================================================
-- DEFAULT STUDENT PROFILE
-- ============================================================
INSERT INTO `student_profiles` (`profile_id`, `user_id`, `age`, `gender`, `state`, `education_level`, `course`, `year`, `percentage`, `family_income`, `category`) VALUES
(1, 2, 20, 'female', 'Maharashtra', 'Undergraduate', 'BCA', 3, 78.00, 200000.00, 'General / Open');
-- ============================================================
-- SCHOLARSHIPS DATA
-- ============================================================
INSERT INTO `scholarships`
(`scholarship_id`, `title`, `provider`, `source`, `application_portal`, `description`, `amount`, `education_level`, `course`, `minimum_percentage`, `maximum_income`, `gender_eligible`, `state`, `category`, `required_documents`, `application_start`, `deadline`, `application_url`, `is_active`)
VALUES
(1, 'Rajarshi Chhatrapati Shahu Maharaj Shikshan Shulkh Shishyavrutti Yojna (EBC)', 'Directorate of Higher Education, Govt of Maharashtra', 'MahaDBT Official Portal', 'MahaDBT', 'Tuition fee and examination fee reimbursement support for eligible economically backward students pursuing recognized higher education courses in Maharashtra.', 30000.00, 'Undergraduate', 'All Higher Education (BA, B.Com, B.Sc, BCA, B.Tech, etc.)', 50.00, 800000.00, 'all', 'Maharashtra', 'General / Open', 'Maharashtra domicile certificate, family income certificate, qualifying marksheet, college admission proof, Aadhaar card and bank passbook.', '2026-08-01', '2026-11-30', 'https://mahadbt.maharashtra.gov.in', 1),
(2, 'Dr. Panjabrao Deshmukh Vasatigruh Nirvah Bhatta Yojna', 'Higher and Technical Education Department, Govt of Maharashtra', 'MahaDBT Official Portal', 'MahaDBT', 'Financial assistance for eligible students requiring accommodation and maintenance support while pursuing higher education in Maharashtra.', 30000.00, 'Undergraduate', 'Engineering / Medical / Management / Pharmacy / Architecture / BCA', 50.00, 800000.00, 'all', 'Maharashtra', 'All', 'Domicile certificate, income proof, hostel or accommodation proof, academic marksheets, Aadhaar card and bank details.', '2026-08-01', '2026-11-30', 'https://mahadbt.maharashtra.gov.in', 1),
(3, 'Post-Matric Scholarship Scheme for SC Students', 'Social Justice and Special Assistance Department, Govt of Maharashtra', 'MahaDBT Official Portal', 'MahaDBT', 'Scholarship and fee assistance for eligible Scheduled Caste students pursuing post-matric education in Maharashtra.', 50000.00, 'Undergraduate', 'All Degree and Diploma Courses', 40.00, 250000.00, 'all', 'Maharashtra', 'SC', 'SC caste certificate, caste validity certificate where applicable, income certificate, domicile certificate, marksheets and bank passbook.', '2026-08-01', '2026-12-15', 'https://mahadbt.maharashtra.gov.in', 1),
(4, 'Post-Matric Scholarship Scheme for OBC Students', 'VJNT, OBC and SBC Welfare Department, Govt of Maharashtra', 'MahaDBT Official Portal', 'MahaDBT', 'Fee reimbursement and scholarship support for eligible OBC students pursuing approved higher education courses.', 25000.00, 'Undergraduate', 'All UG and PG Courses', 50.00, 150000.00, 'all', 'Maharashtra', 'OBC', 'OBC caste certificate, non-creamy layer certificate where applicable, income certificate, domicile certificate and marksheets.', '2026-08-01', '2026-12-15', 'https://mahadbt.maharashtra.gov.in', 1),
(5, 'Post-Matric Scholarship for VJ/NT and SBC Candidates', 'VJNT, OBC and SBC Welfare Department, Govt of Maharashtra', 'MahaDBT Official Portal', 'MahaDBT', 'Scholarship and education fee assistance for eligible VJ/NT and SBC students pursuing higher education.', 35000.00, 'Undergraduate', 'All Degree, Diploma and Postgraduate Courses', 45.00, 150000.00, 'all', 'Maharashtra', 'VJ/NT', 'Relevant caste certificate, income certificate, domicile certificate, academic marksheets and college bonafide certificate.', '2026-08-01', '2026-12-15', 'https://mahadbt.maharashtra.gov.in', 1),
(6, 'Pandit Deendayal Upadhyay Swayam Yojana', 'Tribal Development Department, Govt of Maharashtra', 'MahaDBT Official Portal', 'MahaDBT', 'Financial assistance for eligible Scheduled Tribe students requiring accommodation support while pursuing higher education.', 60000.00, 'Undergraduate', 'All Higher Education Courses', 50.00, 250000.00, 'all', 'Maharashtra', 'ST', 'ST caste certificate, income proof, hostel non-availability proof where applicable, accommodation proof and bank account details.', '2026-08-01', '2026-11-30', 'https://mahadbt.maharashtra.gov.in', 1),
(7, 'Savitribai Phule Scholarship for SC and VJNT Girls', 'Government of Maharashtra', 'Government of Maharashtra Official', 'MahaDBT', 'Financial incentive for eligible girl students from selected social categories pursuing higher secondary education.', 10000.00, 'Class 12', 'Junior College', 50.00, NULL, 'female', 'Maharashtra', 'SC', 'Relevant caste certificate, Class 10 marksheet, school or college bonafide certificate and bank passbook.', '2026-08-01', '2026-11-15', 'https://mahadbt.maharashtra.gov.in', 1),
(8, 'Tata Trusts Means Grant for Higher Education', 'Tata Trusts', 'Tata Trusts Official', 'Tata Trusts', 'Merit-cum-means financial assistance for eligible students pursuing higher education.', 60000.00, 'Undergraduate', 'All Degree and Professional Programs', 65.00, 450000.00, 'all', 'All India', 'All', 'Academic marksheets, income proof, college fee structure, Aadhaar card and other documents required by the programme.', '2026-07-15', '2026-10-31', 'https://www.tatatrusts.org', 1),
(9, 'Tata Capital Pankh Scholarship Programme', 'Tata Capital Limited', 'Tata Capital Official', 'Tata Capital', 'Educational financial assistance for eligible school, diploma and undergraduate students from financially constrained families.', 35000.00, 'Undergraduate', 'General Graduation / BCA / BBA / Diploma', 60.00, 400000.00, 'all', 'All India', 'All', 'Identity proof, academic marksheet, income proof and college admission or fee receipt.', '2026-07-01', '2026-10-15', 'https://www.tatacapital.com', 1),
(10, 'Tata Housing Scholarship for Meritorious Girl Students', 'Tata Housing', 'Tata Housing Official', 'Tata Housing', 'Financial assistance for eligible female students pursuing selected professional courses.', 60000.00, 'Undergraduate', 'Civil Engineering / Architecture', 70.00, 500000.00, 'female', 'All India', 'All', 'Class 12 marksheet, college admission letter, family income proof, institution bonafide certificate and bank details.', '2026-08-01', '2026-11-30', 'https://www.tatahousing.com', 1),
(11, 'Tata Communications Scholarship for STEM Students', 'Tata Communications', 'Tata Communications Official', 'Tata Communications', 'Financial support for eligible students pursuing science, technology, mathematics and computing programmes.', 50000.00, 'Undergraduate', 'Computer Science / IT / Electronics / AI / BCA / B.Sc', 65.00, 600000.00, 'all', 'All India', 'All', 'Academic marksheets, college enrollment certificate, family income proof, Aadhaar card and supporting documents.', '2026-08-15', '2026-11-20', 'https://www.tatacommunications.com', 1),
(12, 'Reliance Foundation Undergraduate Scholarship', 'Reliance Foundation', 'Reliance Foundation Official', 'Reliance Foundation', 'Merit-based scholarship support for eligible undergraduate students across India.', 200000.00, 'Undergraduate', 'All Undergraduate Disciplines', 60.00, 1500000.00, 'all', 'All India', 'All', 'Class 12 marksheet, income proof where applicable, college bonafide certificate, identity proof and documents required during application.', '2026-08-01', '2026-10-25', 'https://www.reliancefoundation.org', 1),
(13, 'Reliance Foundation Postgraduate Scholarship', 'Reliance Foundation', 'Reliance Foundation Official', 'Reliance Foundation', 'Scholarship support and leadership opportunities for eligible postgraduate students in selected disciplines.', 600000.00, 'Postgraduate', 'M.Tech / M.Sc / MCA / Computer Science / Renewable Energy', 75.00, 1500000.00, 'all', 'All India', 'All', 'Graduation marksheets, degree certificate, relevant entrance score where applicable, income proof and recommendation documents where required.', '2026-08-01', '2026-10-25', 'https://www.reliancefoundation.org', 1),
(14, 'HDFC Bank Parivartans ECSS Programme', 'HDFC Bank', 'HDFC Bank Official', 'HDFC Bank', 'Educational assistance for eligible students facing financial difficulties while continuing their studies.', 75000.00, 'Undergraduate', 'All General and Professional Degree Programs', 55.00, 250000.00, 'all', 'All India', 'All', 'Previous academic marksheet, income or financial difficulty proof, admission proof and bank account details.', '2026-07-01', '2026-10-15', 'https://www.hdfcbank.com', 1),
(15, 'Aditya Birla Capital Scholarship', 'Aditya Birla Capital Foundation', 'Aditya Birla Capital Official', 'Aditya Birla Capital', 'Education grant support for eligible students from financially constrained backgrounds.', 60000.00, 'Undergraduate', 'All Undergraduate Programs', 60.00, 600000.00, 'all', 'All India', 'All', 'Class 12 marksheet, income certificate or supporting financial documents, college bonafide certificate and bank details.', '2026-07-15', '2026-10-31', 'https://www.adityabirlacapital.com', 1),
(16, 'Infosys Foundation STEM Stars Scholarship', 'Infosys Foundation', 'Infosys Foundation Official', 'Infosys Foundation', 'Financial support for eligible female students pursuing selected STEM undergraduate programmes.', 100000.00, 'Undergraduate', 'B.Tech / MBBS / B.Sc Computer Science / Biotech / Mathematics', 75.00, 800000.00, 'female', 'All India', 'All', 'Class 12 marksheet, college admission proof, income certificate, Aadhaar card and bank account details.', '2026-08-01', '2026-11-15', 'https://www.infosys.com/infosys-foundation', 1),
(17, 'ONGC Foundation Scholarship for Meritorious Students', 'ONGC Foundation', 'ONGC Official', 'ONGC Scholarship Portal', 'Scholarship assistance for eligible students from specified categories pursuing selected professional courses.', 48000.00, 'Undergraduate', 'Engineering / MBBS / Geology / MBA', 60.00, 200000.00, 'all', 'All India', 'SC', 'Relevant category certificate, income certificate, Class 12 marksheet, college bonafide certificate and bank account details.', '2026-08-15', '2026-11-30', 'https://www.ongc.co.in', 1),
(18, 'Kotak Kanya Scholarship for Higher Education', 'Kotak Education Foundation', 'Kotak Education Foundation Official', 'Kotak Education Foundation', 'Financial aid for eligible female students pursuing professional undergraduate courses.', 150000.00, 'Undergraduate', 'BCA / B.Tech / MBBS / Architecture / Law', 75.00, 600000.00, 'female', 'All India', 'All', 'Class 12 marksheet, entrance score where applicable, income certificate, college fee structure and Aadhaar details.', '2026-07-01', '2026-09-30', 'https://www.kotakeducation.org', 1),
(19, 'LOréal India For Young Women in Science', 'LOréal India', 'LOréal India Official', 'LOréal India', 'Financial support programme for eligible women pursuing science and related higher education programmes.', 25000.00, 'Undergraduate', 'B.Sc / B.Tech / MBBS / Biotech / Science', 85.00, 600000.00, 'female', 'All India', 'All', 'Academic marksheets, family income proof, college admission letter, identity proof and documents required by the programme.', '2026-07-01', '2026-10-15', 'https://www.loreal.com', 1),
(20, 'Keep India Smiling Foundational Scholarship', 'Colgate-Palmolive India', 'Colgate India Official', 'Colgate India', 'Financial support for deserving students pursuing undergraduate or vocational education.', 30000.00, 'Undergraduate', 'All Graduation and Vocational Degrees', 60.00, 500000.00, 'all', 'All India', 'All', 'Identity proof, academic marksheet, family income certificate, admission proof and supporting documents.', '2026-07-15', '2026-10-31', 'https://www.colgate.com/en-in', 1),
(21, 'SBIF Asha Scholarship Program for College Students', 'SBI Foundation', 'SBI Foundation Official', 'SBI Foundation', 'Academic grant support for eligible students pursuing higher education.', 50000.00, 'Undergraduate', 'All Undergraduate and Postgraduate Degree Courses', 75.00, 300000.00, 'all', 'All India', 'All', 'Academic marksheets, current admission proof, family income certificate and bank account details.', '2026-08-01', '2026-11-15', 'https://www.sbifoundation.in', 1),
(22, 'Santoor Womens Scholarship', 'Wipro Consumer Care and Azim Premji Foundation', 'Santoor Scholarship Official', 'Santoor Scholarship', 'Scholarship assistance for eligible female students pursuing undergraduate higher education.', 24000.00, 'Undergraduate', 'BA / B.Sc / B.Com / BCA / BBA', 60.00, 300000.00, 'female', 'Maharashtra', 'All', 'Class 10 and 12 marksheets, college ID, Aadhaar card and student bank account details.', '2026-07-01', '2026-09-30', 'https://www.santoorscholarship.com', 1),
(23, 'Central Sector Scheme of Scholarships for College and University Students', 'Department of Higher Education, Ministry of Education', 'National Scholarship Portal', 'National Scholarship Portal', 'Central government scholarship support for eligible students pursuing regular higher education courses.', 20000.00, 'Undergraduate', 'All Regular Degree Programs', 80.00, 450000.00, 'all', 'All India', 'All', 'Class 12 marksheet, income certificate where required, Aadhaar linked bank account and college verification documents.', '2026-07-01', '2026-11-30', 'https://scholarships.gov.in', 1),
(24, 'AICTE Pragati Scholarship Scheme for Girl Students', 'All India Council for Technical Education', 'National Scholarship Portal', 'National Scholarship Portal', 'Scholarship support for eligible female students pursuing technical degree or diploma programmes.', 50000.00, 'Undergraduate', 'B.Tech / B.E. / Technical Degree and Diploma', 60.00, 800000.00, 'female', 'All India', 'All', 'Academic marksheets, admission allotment letter, income certificate, Aadhaar card and bank passbook.', '2026-08-01', '2026-11-15', 'https://scholarships.gov.in', 1),
(25, 'AICTE Saksham Scholarship for Specially Abled Students', 'All India Council for Technical Education', 'National Scholarship Portal', 'National Scholarship Portal', 'Scholarship assistance for eligible students with disabilities pursuing technical education.', 50000.00, 'Undergraduate', 'Technical Degree / Diploma', 50.00, 800000.00, 'all', 'All India', 'All', 'Disability certificate where applicable, academic marksheets, income certificate, college bonafide certificate and bank details.', '2026-08-01', '2026-11-15', 'https://scholarships.gov.in', 1),
(26, 'Post-Matric Scholarship Scheme for Minorities', 'Government of India', 'National Scholarship Portal', 'National Scholarship Portal', 'Scholarship assistance for eligible students from notified minority communities pursuing post-matric education.', 30000.00, 'Undergraduate', 'All Higher Education Degrees', 50.00, 200000.00, 'all', 'All India', 'All', 'Community declaration or certificate where applicable, academic marksheet, income certificate and bank account details.', '2026-08-01', '2026-11-30', 'https://scholarships.gov.in', 1),
(27, 'Prime Ministers Scholarship Scheme for CAPFs and Assam Rifles', 'Ministry of Home Affairs', 'National Scholarship Portal', 'National Scholarship Portal', 'Financial assistance for eligible dependent wards under the applicable CAPF and Assam Rifles scheme rules.', 36000.00, 'Undergraduate', 'Engineering / Medical / BCA / MCA / Professional Degrees', 60.00, NULL, 'all', 'All India', 'All', 'Relevant service or dependency certificate, Class 12 scorecard, college bonafide certificate, Aadhaar card and bank details.', '2026-08-01', '2026-11-30', 'https://scholarships.gov.in', 1),
(28, 'INSPIRE Scholarship for Higher Education', 'Department of Science and Technology, Government of India', 'INSPIRE Official Portal', 'INSPIRE', 'Scholarship support for eligible students pursuing basic and natural sciences.', 80000.00, 'Undergraduate', 'B.Sc / Natural Sciences', 85.00, NULL, 'all', 'All India', 'All', 'Class 12 marksheet, required ranking or eligibility proof, Class 10 certificate, college endorsement and bank account details.', '2026-09-01', '2026-12-31', 'https://online-inspire.gov.in', 1),
(29, 'Karnataka Vidyasiri Post-Matric Scholarship', 'Department of Backward Classes Welfare, Government of Karnataka', 'Karnataka Scholarship Portal', 'Karnataka SSP', 'Scholarship and education support for eligible post-matric students in Karnataka.', 25000.00, 'Undergraduate', 'All Degree and Diploma Courses', 50.00, 250000.00, 'all', 'Karnataka', 'OBC', 'Relevant caste and income certificates, domicile proof, academic marksheets, college admission proof and Aadhaar card.', '2026-08-01', '2026-11-30', 'https://ssp.postmatric.karnataka.gov.in', 1),
(30, 'Uttar Pradesh Post Matric Scholarship and Fee Reimbursement Scheme', 'Social Welfare Department, Government of Uttar Pradesh', 'UP Scholarship Official Portal', 'UP Scholarship Portal', 'Scholarship and fee reimbursement assistance for eligible students pursuing higher education in Uttar Pradesh.', 30000.00, 'Undergraduate', 'All UG and PG Degree Programs', 50.00, 200000.00, 'all', 'Uttar Pradesh', 'All', 'UP income certificate, caste certificate where applicable, academic marksheets, college fee receipt and Aadhaar linked bank account.', '2026-07-15', '2026-11-20', 'https://scholarship.up.gov.in', 1),
(31, 'Swami Vivekananda Merit-cum-Means Scholarship', 'Higher Education Department, Government of West Bengal', 'West Bengal Government Official', 'SVMCM', 'Financial support for eligible meritorious students from economically weaker backgrounds pursuing higher education in West Bengal.', 60000.00, 'Undergraduate', 'General Degree / Engineering / Medical / Postgraduate', 60.00, 250000.00, 'all', 'West Bengal', 'All', 'Academic marksheets, family income certificate, West Bengal domicile proof and admission receipt.', '2026-08-01', '2026-12-31', 'https://svmcm.wbhed.gov.in', 1),
(32, 'Tamil Nadu Post Matric Scholarship for BC, MBC and DNC Students', 'Government of Tamil Nadu', 'Tamil Nadu Official Portal', 'Tamil Nadu Scholarship Portal', 'Education support for eligible students from specified backward communities pursuing higher education in Tamil Nadu.', 25000.00, 'Undergraduate', 'All Degree and Professional Programs', 50.00, 250000.00, 'all', 'Tamil Nadu', 'OBC', 'Community certificate, income certificate, Class 12 marksheet, college bonafide certificate and bank details.', '2026-08-01', '2026-11-30', 'https://www.tn.gov.in', 1),
(33, 'Mukhyamantri Medhavi Vidyarthi Yojana', 'Department of Higher Education, Government of Madhya Pradesh', 'Madhya Pradesh Scholarship Portal', 'MP Scholarship Portal', 'Tuition fee assistance for eligible meritorious students pursuing approved higher education courses in Madhya Pradesh.', 150000.00, 'Undergraduate', 'Engineering / Medical / Law / Graduation', 70.00, 600000.00, 'all', 'Madhya Pradesh', 'All', 'MP domicile certificate, Class 12 marksheet, income certificate, entrance examination documents where applicable and Samagra ID.', '2026-08-01', '2026-12-15', 'http://scholarshipportal.mp.nic.in', 1),
(34, 'Chief Ministers Higher Education Scholarship Scheme Rajasthan', 'College Education Department, Government of Rajasthan', 'Rajasthan Government Official', 'Rajasthan Scholarship Portal', 'Financial incentive for eligible meritorious students pursuing higher education in Rajasthan.', 5000.00, 'Undergraduate', 'Regular Graduation', 60.00, 250000.00, 'all', 'Rajasthan', 'General / Open', 'Rajasthan domicile, Jan Aadhaar, Class 12 marksheet, family income certificate and college fee receipt.', '2026-08-01', '2026-11-30', 'https://hte.rajasthan.gov.in', 1),
(35, 'Digital Gujarat Post-Matric Scholarship Scheme', 'Social Justice and Empowerment Department, Government of Gujarat', 'Digital Gujarat Official Portal', 'Digital Gujarat', 'Scholarship support for eligible students pursuing post-matric education in Gujarat.', 25000.00, 'Undergraduate', 'All Degree, Diploma and Technical Courses', 50.00, 250000.00, 'all', 'Gujarat', 'OBC', 'Relevant caste certificate, non-creamy layer certificate where applicable, Gujarat domicile, academic marksheet and college bonafide certificate.', '2026-08-01', '2026-11-30', 'https://www.digitalgujarat.gov.in', 1),
(36, 'AICTE Swanath Scholarship Scheme', 'All India Council for Technical Education', 'National Scholarship Portal', 'National Scholarship Portal', 'Financial assistance for eligible students pursuing technical education under the applicable scheme guidelines.', 50000.00, 'Undergraduate', 'Technical Degree / Engineering / Professional Courses', 0.00, 800000.00, 'all', 'All India', 'All', 'Academic marksheets, admission proof, income certificate where applicable, Aadhaar details, bank account details and scheme-specific eligibility documents.', '2026-06-01', '2026-10-31', 'https://scholarships.gov.in', 1),
(37, 'PM YASASVI Top Class Education Scheme', 'Department of Social Justice and Empowerment, Government of India', 'National Scholarship Portal', 'National Scholarship Portal', 'Central scholarship support for eligible OBC, EBC and DNT students pursuing higher education under the applicable scheme rules.', 100000.00, 'Undergraduate', 'Recognized Higher Education Programs', 0.00, NULL, 'all', 'All India', 'OBC', 'Category certificate, academic records, admission proof, income certificate where applicable, Aadhaar and bank account information.', '2026-06-01', '2026-10-31', 'https://scholarships.gov.in', 1),
(38, 'Top Class Education Scheme for SC Students', 'Ministry of Social Justice and Empowerment, Government of India', 'National Scholarship Portal', 'National Scholarship Portal', 'Financial support for eligible Scheduled Caste students pursuing higher education under applicable scheme guidelines.', 100000.00, 'Undergraduate', 'Recognized Higher Education Programs', 0.00, NULL, 'all', 'All India', 'SC', 'SC caste certificate, academic marksheets, admission proof, income certificate where applicable, Aadhaar details and bank account information.', '2026-06-01', '2026-10-31', 'https://scholarships.gov.in', 1),
(39, 'National Fellowship and Scholarship for Higher Education of ST Students', 'Ministry of Tribal Affairs, Government of India', 'National Scholarship Portal', 'National Scholarship Portal', 'Scholarship support for eligible Scheduled Tribe students pursuing higher education according to official scheme guidelines.', 100000.00, 'Undergraduate', 'Recognized Higher Education Programs', 0.00, NULL, 'all', 'All India', 'ST', 'ST caste certificate, academic records, admission documents, income proof where applicable, Aadhaar details and bank account information.', '2026-06-01', '2026-10-31', 'https://scholarships.gov.in', 1),
(40, 'Ishan Uday Special Scholarship Scheme for North Eastern Region', 'University Grants Commission', 'National Scholarship Portal', 'National Scholarship Portal', 'Scholarship support for eligible students from the North Eastern Region pursuing higher education.', 65000.00, 'Undergraduate', 'Recognized Undergraduate Degree Programs', 0.00, NULL, 'all', 'All India', 'All', 'North Eastern Region domicile proof, academic marksheets, admission proof, Aadhaar details and bank account information.', '2026-06-01', '2026-10-31', 'https://scholarships.gov.in', 1),
(41, 'Prime Minister Scholarship Scheme for Ministry of Railways', 'Ministry of Railways, Government of India', 'National Scholarship Portal', 'National Scholarship Portal', 'Scholarship assistance for eligible wards under the applicable Ministry of Railways scheme guidelines.', 36000.00, 'Undergraduate', 'Recognized Professional and General Degree Programs', 0.00, NULL, 'all', 'All India', 'All', 'Railway employee or service-related eligibility documents, academic marksheets, admission proof, Aadhaar details and bank account information.', '2026-06-01', '2026-10-31', 'https://scholarships.gov.in', 1),
(42, 'Financial Support to Students of North Eastern Region for Higher Professional Courses', 'North Eastern Council', 'National Scholarship Portal', 'National Scholarship Portal', 'Financial assistance for eligible students from the North Eastern Region pursuing approved professional courses.', 30000.00, 'Undergraduate', 'Professional and Technical Degree Programs', 0.00, NULL, 'all', 'All India', 'All', 'North Eastern Region domicile proof, academic marksheets, admission proof, Aadhaar details and bank account information.', '2026-06-01', '2026-10-31', 'https://scholarships.gov.in', 1),
(43, 'AICTE Pragati Scholarship Scheme for Diploma Students', 'All India Council for Technical Education', 'National Scholarship Portal', 'National Scholarship Portal', 'Financial assistance for eligible female students pursuing technical diploma education in AICTE approved institutions.', 50000.00, 'Diploma / Polytechnic', 'Technical Diploma Programs', 50.00, 800000.00, 'female', 'All India', 'All', 'Academic marksheets, admission documents, income certificate, Aadhaar card and bank account details.', '2026-06-01', '2026-10-31', 'https://scholarships.gov.in', 1),
(44, 'National Means-cum-Merit Scholarship', 'Department of School Education and Literacy, Government of India', 'National Scholarship Portal', 'National Scholarship Portal', 'Scholarship support for eligible school students meeting merit and financial criteria.', 12000.00, 'Class 9–10', 'School Education', 55.00, 350000.00, 'all', 'All India', 'All', 'Academic records, income certificate where required, school verification, Aadhaar details and bank account information.', '2026-06-01', '2026-10-31', 'https://scholarships.gov.in', 1),
(45, 'Pre-Matric Scholarship for Students with Disabilities', 'Department of Empowerment of Persons with Disabilities', 'National Scholarship Portal', 'National Scholarship Portal', 'Scholarship support for eligible school students with disabilities under applicable government guidelines.', 15000.00, 'Class 6–8', 'School Education', 0.00, 250000.00, 'all', 'All India', 'All', 'Disability certificate, school bonafide certificate, income certificate where applicable, Aadhaar details and bank account information.', '2026-06-01', '2026-10-31', 'https://scholarships.gov.in', 1);
