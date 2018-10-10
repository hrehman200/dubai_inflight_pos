ALTER TABLE `approval_requests` ADD `flight_offer_id` INT(11) NOT NULL DEFAULT '0' AFTER `status`, ADD `customer_id` INT(11) NOT NULL DEFAULT '0' AFTER `flight_offer_id`;
