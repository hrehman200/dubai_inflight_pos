CREATE TABLE `approval_requests` (
  `id` int(11) NOT NULL,
  `made_by` int(11) NOT NULL,
  `approved_by` int(11) NOT NULL,
  `token` varchar(500) NOT NULL,
  `status` tinyint(3) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `approval_requests`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `approval_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;


ALTER TABLE `flight_packages` ADD `type` TINYINT(4) NOT NULL AFTER `status`;
ALTER TABLE `flight_offers` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `user` ADD `email` VARCHAR(255) NOT NULL AFTER `position`;
ALTER TABLE `sales` ADD `approval_request_id` INT(11) NOT NULL AFTER `expiry`;

INSERT INTO `flight_packages` (`id`, `package_name`, `image`, `status`, `type`)
VALUES
(NULL, 'FDR', '', '1', '1'),
(NULL, 'Staff Flying', '', '1', '1'),
(NULL, 'Maintenance', '', '1', '1'),
(NULL, 'Giveaways', '', '1', '1'),
(NULL, 'Marketing', '', '1', '1');

INSERT INTO `flight_offers` (`package_id`, `offer_name`, `code`, `price`, `duration`, `status`)
VALUES
('13', 'FDR 2 Flights', '', '0', '2', '1'),
('13', 'FDR 8 Flights', '', '0', '8', '1'),
('13', 'FDR 10 Flights', '', '0', '10', '1'),
('13', 'FDR 15 Flights', '', '0', '15', '1'),
('13', 'FDR 30 Flights', '', '0', '30', '1'),

('14', 'Staff Flying (Single) 5 Flights', '', '0', '5', '1'),
('14', 'Staff Flying (Single) 10 Flights', '', '0', '10', '1'),
('14', 'Staff Flying (Group) 5 Flights', '', '0', '5', '1'),
('14', 'Staff Flying (Group) 10 Flights', '', '0', '10', '1'),
('14', 'IDP 10 Flights', '', '0', '10', '1'),
('14', 'IDP 30 Flights', '', '0', '30', '1'),
('14', 'IDP 60 Flights', '', '0', '60', '1'),
('14', 'FITP 10 Flights', '', '0', '10', '1'),
('14', 'FITP 30 Flights', '', '0', '30', '1'),
('14', 'FITP 60 Flights', '', '0', '60', '1'),
('14', 'Safety Netting 5 Flights', '', '0', '5', '1'),
('14', 'Safety Netting 10 Flights', '', '0', '10', '1'),

('15', 'Maintenance 2 Flights', '', '0', '2', '1'),
('15', 'Maintenance 10 Flights', '', '0', '10', '1'),
('15', 'Maintenance 30 Flights', '', '0', '30', '1'),
('15', 'Maintenance 60 Flights', '', '0', '60', '1'),

('16', 'Giveaway 2 Flights', '', '0', '2', '1'),
('16', 'Giveaway 10 Flights', '', '0', '10', '1'),
('16', 'Giveaway 30 Flights', '', '0', '30', '1'),
('16', 'Giveaway 60 Flights', '', '0', '60', '1'),

('17', 'Marketing 2 Flights', '', '0', '2', '1'),
('17', 'Marketing 10 Flights', '', '0', '10', '1'),
('17', 'Marketing 30 Flights', '', '0', '30', '1'),
('17', 'Marketing 60 Flights', '', '0', '60', '1');



