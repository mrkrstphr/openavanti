# Introduction #

This page outlines information about using the MySQL database engine with OpenAvanti.


# About Foreign Keys #

Foreign Keys must be defined properly per [3.6.6 Using Foreign Keys](http://dev.mysql.com/doc/refman/5.0/en/example-foreign-keys.html) of the MySQL Manual. In short, references defined at the column level are not considered foreign keys. Foreign keys must be defined at the table level.

Example:

```
CREATE TABLE customers(
    customer_id int PRIMARY KEY
) ENGINE=INNODB;

CREATE TABLE orders(
    order_id int PRIMARY KEY,
    customer_id int NOT NULL,
    FOREIGN KEY(customer_id) REFERENCES customers(customer_id)
) ENGINE=INNODB;
```