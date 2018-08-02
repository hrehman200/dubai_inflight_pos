CREATE TABLE `customer_yearly_purchases` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `per_minute_cost` double(6,2) NOT NULL,
  `per_month_minutes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `customer_yearly_purchases`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `customer_yearly_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;