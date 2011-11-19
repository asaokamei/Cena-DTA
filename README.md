Cena Data Transfer Agent 
========================

PHP/JavaScript framework for building web applications utilizing HTML5's 
local databases. It's strength is in its ability to synchronize data 
as well as relations between local and master database on a cloud. 

find more details in 
[Cena-DTA's home page.](http://www.workspot.jp/cena/index.php "Cena-DTA")

Requirement
-----------

* server: PHP5.3 and MySQL
* client: WebSqlDB capable browser and jQuery. 

Technical Merit
---------------

Primary key value maybe changed during database synchronization process if 
keys are generated as auto-numbered as in many conventional RDB. If the 
changed key is used to create a relation between records, the relation 
will be broken. 

Cena-DTA can keep track all the primary key changes, and automatically 
changes the corresponding values in other records to keep relationships 
correct. 
