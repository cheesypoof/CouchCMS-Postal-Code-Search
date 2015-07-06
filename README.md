# [CouchCMS](http://www.couchcms.com/) Postal Code Search

A postal code search (store locator) implementation.

## Usage
1. Acquire a postal code database for your country that contains latitude and longitude coordinates. You may find such databases at [GeoNames](http://www.geonames.org/).
2. Import the table with `pc`, `latitude`, and `longitude` field names.
3. Go to [line 25](postal-code.php#L25) of `postal-code.php` and replace `postal_codes` with your chosen table name.
4. Update the `validator` parameters in `search.php` to reflect the correct format of your postal code.
