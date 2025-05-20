-- Employees table
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `hire_date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`)
);

-- Managers table
CREATE TABLE `managers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
);

-- Exit requests
CREATE TABLE `exit_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `request_date` date NOT NULL,
  `last_working_day` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Interview Scheduled','Completed') NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`employee_id`) REFERENCES employees(id)
);

-- Exit interviews
CREATE TABLE `exit_interviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exit_request_id` int(11) NOT NULL,
  `interview_date` datetime NOT NULL,
  `manager_id` int(11) NOT NULL,
  `feedback` text,
  `conducted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`exit_request_id`) REFERENCES exit_requests(id),
  FOREIGN KEY (`manager_id`) REFERENCES managers(id)
);

-- Asset returns
CREATE TABLE `asset_returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exit_request_id` int(11) NOT NULL,
  `laptop_returned` tinyint(1) NOT NULL DEFAULT 0,
  `id_card_returned` tinyint(1) NOT NULL DEFAULT 0,
  `other_assets` text,
  `return_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`exit_request_id`) REFERENCES exit_requests(id)
);

-- Final settlements
CREATE TABLE `final_settlements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exit_request_id` int(11) NOT NULL,
  `last_salary_date` date NOT NULL,
  `pending_salary_months` int(11) NOT NULL DEFAULT 0,
  `other_dues` decimal(10,2) NOT NULL DEFAULT 0.00,
  `settlement_completed` tinyint(1) NOT NULL DEFAULT 0,
  `settlement_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`exit_request_id`) REFERENCES exit_requests(id)
);