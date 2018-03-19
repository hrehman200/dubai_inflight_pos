ALTER TABLE `sales` ADD `expiry` DATE NULL AFTER `customer_id`;
ALTER TABLE `flight_credits` ADD `expired_on` DATE NULL AFTER `minutes`;