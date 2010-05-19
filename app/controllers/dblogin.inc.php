<?

class db_login_info {
	var $db_type = "mysql";    //  database type (DO NOT CHANGE UNLESS YOU KNOW WHAT YOUR DOING)
	var $db_addr = "localhost";  // database ip addr or localhost depending on your config
        var $db_user = "root";   // database username
	var $db_pwd = "";  // database password
	var $db_name = "bhlseriallist";  // database name
}

$db_login_info = new db_login_info;

?>
