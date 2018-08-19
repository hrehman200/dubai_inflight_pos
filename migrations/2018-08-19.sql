ALTER TABLE `discounts` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;

INSERT INTO `discounts` (`id`, `type`, `category`, `percent`, `status`)
VALUES
(NULL, 'Service', 'Groupon 2 Flights', '24.46', '1'),
(NULL, 'Service', 'Groupon 4 Flights', '25.29', '1'),
(NULL, 'Service', 'Groupon 10 Flights', '24.7', '1');

CREATE TABLE `groupon_discount_codes` (
  `id` int(11) NOT NULL,
  `discount_id` int(11) NOT NULL,
  `code` varchar(15) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `groupon_discount_codes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `groupon_discount_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `flight_purchases` ADD `groupon_code` VARCHAR(15) NULL AFTER `price`;