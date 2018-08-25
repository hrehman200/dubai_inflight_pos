CREATE TABLE `customer_monthly_liability` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `month` varchar(3) NOT NULL,
  `year` smallint(4) NOT NULL,
  `liability_minutes` int(11) NOT NULL,
  `liability_amount` double(10,2) NOT NULL,
  `pre_2018_minutes` int(11) NOT NULL,
  `pre_2018_amount` double(10,2) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `customer_monthly_liability`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `customer_monthly_liability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `customer_monthly_liability` ADD `pre_2018_minutes_used` INT(11) NOT NULL AFTER `pre_2018_amount`;