/* Rebuild membership items in catalog

   Note for future modifications:
       Do not delete a line, change active (last number) to 0
       Changing order of lines has no effect. Change displayorder (next to last number)
       Catalog numbers must be unique for 'membership'.

   See /packages/aftm/db.xml for schema.
 */
DELETE  FROM aftmcatalog;
INSERT INTO aftmcatalog (itemname,catalognumber,itemtype,itemdescription,unitprice,displayorder,active) VALUES
	('membership','member-s1','Student 1-year','Student 1-year - $15.00',15.00,1,1),
	('membership','member-i1','Individual 1-year','Individual 1-year - $20.00',20.00,2,1),
	('membership','member-f1','Family 1-year','Family 1-year - $25.00',25.00,3,1),
	('membership','member-b1','Band or Dance Group 1-year','Band or Dance Group - 1-year $25.00',25.00,4,1),
	('membership','member-b2','Business 1-year','Business - 1-year $50.00',50.00,5,1),
	('membership','member-i5','Individual 5-year','Individual  - 5-year $80.00',80.00,6,1),
	('membership','member-f5','Family 5-year','Family - 5-year - $100.00',100.00,7,1),
	('membership','member-lt','Lifetime membership','Lifetime membership - $300.00',300.00,8,1);


