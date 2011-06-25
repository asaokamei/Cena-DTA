db_driver=mysql
db_user=admin
db_password=admin

[dsn]
host=localhost
dbname=test
;port=3306

[db_options]
MYSQL_ATTR_INIT_COMMAND=set names utf8

; setAttribute( $k, $v )
[db_attributes]
ATTR_ERRMODE=ERRMODE_EXCEPTION

[exec_command]
exec="set names utf8"
