OpenCart module for calculating shipping costs when using Cargus

> Note this repository is well and truly abandoned.

## Getting started

The module requires a custom table to be present in the database, called `(prefix)cargus_zone_city_mapping` - this table holds the city codes and Cargus shipping center codes for all the cities in which Cargus operates in Romania, mapped to OpenCart zone ID values.

The structure of the table is:

	CREATE TABLE `cargus_zone_city_mapping` (
	  `city` varchar(128) NOT NULL,
	  `zone_id` int(11) NOT NULL,
	  `city_id` int(11) NOT NULL,
	  `postal_code` varchar(10) NOT NULL,
	  `cargus_center_id` int(11) NOT NULL DEFAULT '-1',
	  PRIMARY KEY (`city`,`zone_id`)
	)

This data that goes in that table is not that large and should ideally be placed in a static file that can be updated manually (I don't think Cargus updates their city codes that much), but I'm not sure where such a file should be placed within the OpenCart directory structure.

## Limitations

One current limitation is that the destination city name must match a city name from the `cargus_zone_city_mapping` table exactly in order for shipping costs to be calculated.

Another limitation is that I don't think the correct shipping rates are given when calculating just shipping estimates, since OpenCart doesn't require the user to provide a city name and the postal code is optional (and 
not currently searched against by the module).

## License

MIT license - [http://www.opensource.org/licenses/mit-license.php](http://www.opensource.org/licenses/mit-license.php)
